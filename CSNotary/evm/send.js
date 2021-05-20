const Caver = require('caver-js');
const caver = new Caver('https://api.cypress.klaytn.net:8651/');

require('dotenv').config();





const deployedAbi = [

    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "name": "owner",
                "type": "address"
            },
            {
                "indexed": true,
                "name": "spender",
                "type": "address"
            },
            {
                "indexed": false,
                "name": "value",
                "type": "uint256"
            }
        ],
        "name": "Approval",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "name": "from",
                "type": "address"
            },
            {
                "indexed": true,
                "name": "to",
                "type": "address"
            },
            {
                "indexed": false,
                "name": "value",
                "type": "uint256"
            }
        ],
        "name": "Transfer",
        "type": "event"
    }
]

const contractAddress = '0x40dB04e1FB66644db2e079d7B8ECdDb7a32c5Cde';

const argv = require('minimist')(process.argv.slice(2),{

    string: ['receiver','sender'],

    int: ['quantity']// --lang xml


});

let sender = caver.klay.accounts.wallet.add(process.env.CAVER_MAIN_PRIVATE);
const payer = caver.klay.accounts.wallet.add(process.env.CAVER_FEE_PAYER_PRIVATE, process.env.CAVER_FEE_PAYER_PUBLIC);

if (argv.sender)
    sender = caver.klay.accounts.wallet.add(argv.sender);

console.log("XXXXX quantity XXXXXXXX "+ argv.quantity);


const contract = deployedAbi
    && contractAddress
    && new caver.klay.Contract(deployedAbi, contractAddress);


// an arbitrary contract is used
async function run() {
    // make sure `data` starts with 0x
    const { rawTransaction: senderRawTransaction } = await caver.klay.accounts.signTransaction({
        type: 'FEE_DELEGATED_SMART_CONTRACT_EXECUTION',
        from: sender.address,
        to: contractAddress,
        data: contract.methods.transfer(argv.receiver, argv.quantity).encodeABI(),
        gas: '3000000',
        value: 0,
    }, sender.privateKey);

    // signed raw transaction
    console.log("Raw TX:\n", senderRawTransaction);

    // send fee delegated transaction with fee payer information
    caver.klay.sendTransaction({
        senderRawTransaction: senderRawTransaction,
        feePayer: payer.address
    })
        .on('transactionHash', function (hash) {
            console.log(">>> tx_hash for deploy =", hash);
        })
        .on('receipt', function (receipt) {
            console.log(">>> receipt arrived: ", receipt);
        })
        .on('error', function (err) {
            console.error(">>> error: ", err);
        });
}

run();