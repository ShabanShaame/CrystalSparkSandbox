import {NotaryManager} from "../NotaryManager";
import {ChainNotaryBase} from "../ChainNotaryBase";
import {ChainNotaryEvm} from "../evm/ChainNotary";
import {ERC20} from "../evm/commonABI";

const web3 = require('web3')
require('dotenv').config();
const Tx = require('ethereumjs-tx');


export class ChainNotary extends ChainNotaryEvm{


    public getWeb3Provider(){


        const web3Instance = new web3(
            new web3.providers.HttpProvider("https://testnetv3.matic.network")
        )

        return web3Instance ;

    }

    public getWeb3Subprovider() {
        let web3 =  this.getWeb3Provider();
        return web3.eth
    }


    public async send(){

         let web3Instance = this.getWeb3Provider();

         const validation = await this.manager.validateInputs(['from','contract','to','key','quantity'])

         const CONTRACT_ADDRESS = this.manager.arguments.contract
        const RECEIVER_ADDRESS= this.manager.arguments.to

        const privateKey = this.manager.arguments.key;
        const account = web3Instance.eth.accounts.privateKeyToAccount('0x' + privateKey);
        const sender = web3Instance.eth.accounts.wallet.add(account);
        web3Instance.eth.defaultAccount = account.address;



        const contract = this.contractStandardRouting(CONTRACT_ADDRESS,web3Instance.eth)

         let count = await web3Instance.eth.getTransactionCount(sender.address);


        let gasPriceGwei = 60;
        let gasLimit = 200000;
        const rawTransaction = {
            "from": sender.address,

            "gasPrice": web3.utils.toHex(gasPriceGwei * 1e9),
            "gasLimit": web3.utils.toHex(gasLimit),
            "to": CONTRACT_ADDRESS,
            "value": "0x0",
            "data": this.sendMethodRouting(contract,RECEIVER_ADDRESS),
            'nonce': web3.utils.toHex(count)

        };

        console.log("sender" +sender.address)



        let tx = new Tx(rawTransaction);
        let privKey =  Buffer.from(this.manager.arguments.key, 'hex');

        tx.sign(privKey);
        let serializedTx = tx.serialize();

        web3Instance.eth.sendSignedTransaction('0x' + serializedTx.toString('hex')).on('transactionHash', function (hash) {
            console.log(">>> tx_hash for deploy =", hash);

        }).on('receipt', function (receipt) {
            console.log(">>> receipt arrived: ", receipt);
        })
            .on('error', function (err) {
                console.error(">>> error: ", err);
            });
    }





}
