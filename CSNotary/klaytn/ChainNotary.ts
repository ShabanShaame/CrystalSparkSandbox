import {NotaryManager} from "../NotaryManager";
import {ChainNotaryBase, WalletInterface} from "../ChainNotaryBase";
import {ChainNotaryEvm} from "../evm/ChainNotary";
import {ERC20} from "../evm/commonABI";

const Caver = require('caver-js');
require('dotenv').config({ path: '../../../' });
const Tx = require('ethereumjs-tx').Transaction;


export class ChainNotary extends ChainNotaryEvm{

    protected OxOnPrivateKey : boolean = true  ;


    public getWeb3Provider(){


        const web3Instance = new Caver(
            new Caver.providers.HttpProvider("https://cypress.en.klayfee.com/")
        )

        return web3Instance ;

    }

    public getWeb3Subprovider() {
        let web3 =  this.getWeb3Provider();
        return web3.klay
    }


    public async send(){

        let subProvider = this.getWeb3Subprovider();

        const Caver = require('caver-js');
        const caver = new Caver('https://cypress.en.klayfee.com');

        require('dotenv').config();

        const sender = caver.klay.accounts.wallet.add(this.convertPrivateKey(this.manager.arguments.key));
        const payer = caver.klay.accounts.wallet.add(this.convertPrivateKey(this.manager.arguments.feePayerPrivate),this.manager.arguments.feePayerPublic);

        const CONTRACT_ADDRESS = this.manager.arguments.contract
        const RECEIVER_ADDRESS= this.manager.arguments.to ;

        let subProvider2 = caver.klay ;

        /*
        Why this is differnt
        console.log(caver.klay)
        console.log(subProvider)

         */

        const contract = this.contractStandardRouting(CONTRACT_ADDRESS,subProvider)


            // make sure `data` starts with 0x
            const { rawTransaction: senderRawTransaction } = await subProvider.accounts.signTransaction({
                type: 'FEE_DELEGATED_SMART_CONTRACT_EXECUTION',
                from: sender.address,
                to: CONTRACT_ADDRESS,
                data: this.sendMethodRouting(contract,RECEIVER_ADDRESS),
                gas: '3000000',
                value: 0,
            }, sender.privateKey);

            // signed raw transaction
            console.log("Raw TX:\n", senderRawTransaction);
        let response: any = {};


            // send fee delegated transaction with fee payer information
        //For an unknow reason subprovider.sendTransaction return an error we have to reinitial caver.klay to make it work
        caver.klay.sendTransaction({
                senderRawTransaction: senderRawTransaction,
                feePayer: payer.address
            })
                .on('transactionHash', function (hash) {
                    console.log(">>> tx_hash for deploy =", hash);

                    response.txHash = hash ;


                })
                .on('receipt', function (receipt) {
                    console.log(">>> receipt arrived: ", receipt);
                    response.receipt = receipt ;
                })
                .on('error', function (err) {
                    console.error(">>> error: ", err);
                }).then( (value) => {
            this.response = response;
            super.send();
        });


        //await super.send();
        /*


         let web3Instance = this.getWeb3Provider();
         let subProvider = this.getWeb3Subprovider();

         const validation = await this.manager.validateInputs(['from','contract','to','key','quantity'])

         const CONTRACT_ADDRESS = "0x40dB04e1FB66644db2e079d7B8ECdDb7a32c5Cde"
        const RECEIVER_ADDRESS= "0x1d45587d4653ac12c813a60a15e929985be858e7"

        const privateKey = this.manager.arguments.key;
        const account = subProvider.accounts.privateKeyToAccount("0x9ce26eed938817e9c26840c5370aacc4f17ddabe7b1260294d59c4cf7fc86e77");
        const sender = subProvider.accounts.wallet.add(account);
        subProvider.defaultAccount = account.address;

        const caver = new Caver('https://api.cypress.klaytn.net:8651/');

        const payer = caver.klay.accounts.wallet.add('0x93d501286bc90f2271340a97432801e4406a9b8a7f4ab006f97cd426796668d8');

        console.log(payer.address);
        console.log(sender.address);



        const contract = this.contractStandardRouting(CONTRACT_ADDRESS,subProvider)

         let count = await subProvider.getTransactionCount(sender.address);

        const { rawTransaction: senderRawTransaction } = await subProvider.accounts.signTransaction({
            type: 'FEE_DELEGATED_SMART_CONTRACT_EXECUTION',
            from: sender.address,
            to: CONTRACT_ADDRESS,
            data: this.sendMethodRouting(contract,RECEIVER_ADDRESS),
            gas: '3000000',
            value: 0,
        }, sender.privateKey);
        console.log("yyyaaaa")
        console.log(this.sendMethodRouting(contract,RECEIVER_ADDRESS))
        console.log("yyyaaaa")

        // signed raw transaction
        console.log("Raw TX:\n", senderRawTransaction);

        // send fee delegated transaction with fee payer information
       subProvider.sendTransaction({
            senderRawTransaction: senderRawTransaction,
            feePayer:payer
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


        process.exit()

        let gasPriceGwei = 60;
        let gasLimit = 200000;
        const rawTransaction = {
            "from": sender.address,
            "chainId":'1000',
            "gasPrice": web3Instance.utils.toHex(gasPriceGwei * 1e9),
            "gasLimit": web3Instance.utils.toHex(gasLimit),
            "to": CONTRACT_ADDRESS,
            "value": "0x0",
            "data": this.sendMethodRouting(contract,RECEIVER_ADDRESS),
            'nonce': web3Instance.utils.toHex(count)

        };

        console.log("sender" +sender.address)



        let tx = new Tx(rawTransaction);
        let privKey =  Buffer.from(this.manager.arguments.key, 'hex');

        tx.sign(privKey);
        let serializedTx = tx.serialize();

        subProvider.sendSignedTransaction('0x' + serializedTx.toString('hex')).on('transactionHash', function (hash) {
            console.log(">>> tx_hash for deploy =", hash);

        }).on('receipt', function (receipt) {
            console.log(">>> receipt arrived: ", receipt);
        })
            .on('error', function (err) {
                console.error(">>> error: ", err);
            });*/

    }




}
