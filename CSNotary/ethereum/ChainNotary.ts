import {ChainNotaryEvm} from "../evm/ChainNotary";

const web3 = require('web3')
require('dotenv').config();
const Tx = require('ethereumjs-tx').Transaction;


export class ChainNotary extends ChainNotaryEvm{


    public getWeb3Provider(){

        let url = '';

        switch (this.manager.arguments.network) {

            case "rinkeby":
                url = 'https://rinkeby.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                break;
            case "ropsten":
                url = 'https://ropsten.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                break;
            case "goerli":
                url = 'https://goerli.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                this.chainId = 5;
                break;
            default :
                url = 'https://mainnet.infura.io/v3/a6e34ed067c74f25ba705456d73a471e';
                break;
        }



        const web3Instance = new web3(
            new web3.providers.HttpProvider(url)
        )

        return web3Instance ;

    }

    public getWeb3Subprovider() {

       let web3 =  this.getWeb3Provider();
        return web3.eth

    }

    public async getPendingTransactions() {



        let subProvider: any = this.getWeb3Subprovider();
        // @ts-ignore

        subProvider.getTransactionReceipt('0xd2f38cf875e92a5bc836093e7770ff3ee059505d37e13449261c8c98edc7c1b0').then(console.log);

        return ;



        var options = {
            fromBlock: "pending",
            toBlock: "latest",
            address: this.manager.arguments.address,
        }


        let web3 = this.getWeb3Provider();
        console.log(web3);
        web3.eth.filter

        web3.eth.filter(options, (error, result) => {
            if (!error)
                console.log(result);
        });


    }


    public async send() {

        try {

            let web3Instance = this.getWeb3Provider();

            const validation = await this.manager.validateInputs(['from', 'contract', 'to', 'key'])

            const CONTRACT_ADDRESS = this.manager.arguments.contract
            const RECEIVER_ADDRESS = this.manager.arguments.to

            const privateKey = this.manager.arguments.key;
            const account = web3Instance.eth.accounts.privateKeyToAccount(ChainNotaryEvm.addOx(privateKey));
            const sender = web3Instance.eth.accounts.wallet.add(account);
            web3Instance.eth.defaultAccount = account.address;

            let gasPriceGwei = await web3Instance.eth.getGasPrice();


            const contract = this.contractStandardRouting(CONTRACT_ADDRESS, web3Instance.eth);

            let count = await web3Instance.eth.getTransactionCount(sender.address, 'pending');

            //console.log(this.getWeb3Provider());


            //let gasPriceGwei = 15;

            let gasLimit = 200000;
            // let gasLimit = 71000;
            const rawTransaction = {
                "from": sender.address,

                "gasPrice": web3.utils.toHex(gasPriceGwei),
                //"gasPrice": web3.utils.toHex(gasPriceGwei * 1e9),
                "gasLimit": web3.utils.toHex(gasLimit),
                "to": CONTRACT_ADDRESS,
                "value": "0x0",
                "data": this.sendMethodRouting(contract, RECEIVER_ADDRESS),
                'nonce': web3.utils.toHex(count),
                'chainId': web3.utils.toHex(this.chainId)

            };


            let tx = new Tx(rawTransaction, {'chain': this.manager.arguments.network});
            let privKey = Buffer.from(ChainNotaryEvm.removeOx(this.manager.arguments.key), 'hex');
            let response: any = {};


            tx.sign(privKey);
            let serializedTx = tx.serialize();

            // console.log(serializedTx.toString('hex'));
            try {
                response.txHash = await this.waitForHash('0x' + serializedTx.toString('hex'));
            } catch (err) {

                response.error = err.message;
                this.response = response;
                this.manager.returnError(response);
                return

            }

            this.response = response;
            super.send();

        }catch (e){
            console.log(e.message)
            this.manager.returnError(e.message);

        }
    }







}

/*
 * web3Instance.eth.sendSignedTransaction('0x' + serializedTx.toString('hex'))
 .on('transactionHash', function (hash) {
                response.txHash = hash ;

        })
 .on('receipt', function (receipt) {

                response.receipt = receipt ;
            })
 .on('error', function (err) {


            }).then( (value) => {
            this.response = response;
            super.send();
        });
 */


