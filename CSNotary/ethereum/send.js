//const HDWalletProvider = require("truffle-hdwallet-provider")
const web3 = require('web3')
require('dotenv').config();
const Tx = require('ethereumjs-tx');




const argv = require('minimist')(process.argv.slice(2),{

    string: ['from','contract','to','key'],

    int: ['quantity']// --lang xml


});




const NFT_ABI = [{
    "constant": false,
    "inputs": [
        {
            "name": "_to",
            "type": "address"
        }
    ],
    "name": "mintTo",
    "outputs": [],
    "payable": false,
    "stateMutability": "nonpayable",
    "type": "function"
},

    {
        "constant": false,
        "inputs": [
            {
                "name": "_to",
                "type": "address"
            },
            {
                "name": "_value",
                "type": "uint256"
            }
        ],
        "name": "transfer",
        "outputs": [
            {
                "name": "",
                "type": "bool"
            }
        ],
        "payable": false,
        "stateMutability": "nonpayable",
        "type": "function"
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



async function main() {
    //const provider = new HDWalletProvider("dfe", `https://mainnet.infura.io/v3//a6e34ed067c74f25ba705456d73a471e`)

    const web3Instance = new web3(
        new web3.providers.HttpProvider("https://mainnet.infura.io/v3/a6e34ed067c74f25ba705456d73a471e")
    )



    const NFT_CONTRACT_ADDRESS = argv.contract
    const RECEIVER_ADDRESS= argv.to

    const privateKey = argv.key;
    const account = web3Instance.eth.accounts.privateKeyToAccount('0x' + privateKey);
    const sender = web3Instance.eth.accounts.wallet.add(account);
    web3Instance.eth.defaultAccount = account.address;



    try {
        const nftContract = new web3Instance.eth.Contract(NFT_ABI, NFT_CONTRACT_ADDRESS, {gasLimit: "1000000"});
    }catch (e) {
        console.log(e);

    }
    console.log(argv);
    //process.exit();

    //process.exit();



        const nftContract = new web3Instance.eth.Contract(NFT_ABI, NFT_CONTRACT_ADDRESS, { gasLimit: 1000000 })
    /*

        const { rawTransactionX: senderRawTransaction } = await web3Instance.eth.accounts.signTransaction({

            from: sender.address,
            to: argv.contract,
            data: nftContract.methods.transfer(argv.to, 1).encodeABI(),
            gas: 1000000,
            gasLimit: "53000",
            value: 0,
        }, sender.privateKey);
        console.log(argv.contract);
        console.log("sign raw");
        process.exit();


*/
    let count = await web3Instance.eth.getTransactionCount(sender.address);
    console.log("hello count hex"+count)
    let gasPriceGwei = 15;
    let gasLimit = 71000;
    const rawTransaction = {
        "from": sender.address,

        "gasPrice": web3.utils.toHex(gasPriceGwei * 1e9),
        "gasLimit": web3.utils.toHex(gasLimit),
        "to": argv.contract,
        "value": "0x0",
        "data": nftContract.methods.transfer(argv.to, 1).encodeABI(),
        'nonce': web3.utils.toHex(count)

    };

    console.log("sender" +sender.address)


        var tx = new Tx(rawTransaction);
        var privKey =  Buffer.from('57F88E74039A8F6478AFC997B511210E19900D2036B1180203D50543E517AA37', 'hex');

        tx.sign(privKey);
        let serializedTx = tx.serialize();
    process.exit();
        web3Instance.eth.sendSignedTransaction('0x' + serializedTx.toString('hex')).on('transactionHash', function (hash) {
            console.log(">>> tx_hash for deploy =", hash);

        }).on('receipt', function (receipt) {
            console.log(">>> receipt arrived: ", receipt);
        })
            .on('error', function (err) {
                console.error(">>> error: ", err);
            });


        return ;


    web3Instance.eth.sendTransaction({
            senderRawTransaction: senderRawTransaction,
            gas:100000


        })
            .on('transactionHash', function (hash) {
                console.log(">>> tx_hash for deploy =", hash);
                process.exit();
            })
            .on('receipt', function (receipt) {
                console.log(">>> receipt arrived: ", receipt);
            })
            .on('error', function (err) {
                console.error(">>> error: ", err);
            });



return ;

        const result = await nftContract.methods.transfer(RECEIVER_ADDRESS,1).send({ from: argv.from }).on('transactionHash', function (hash) {
            console.log(">>> tx_hash for deploy =", hash);

        })
            .on('receipt', function (receipt) {
                console.log(">>> receipt arrived: ", receipt);


            })
            .on('error', function (err) {
                console.error(">>> error: ", err);
            });



}

main()
