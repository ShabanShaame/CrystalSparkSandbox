import {ChainNotaryBase, WalletInterface} from "../ChainNotaryBase";
import {ERC20, ERC721, MINTABLE} from "./commonABI";
import Web3 from "web3";
import {ChainParser} from "./ChainParser";

const web3 = require('web3')
require('dotenv').config();
const Tx = require('ethereumjs-tx');


export class ChainNotaryEvm extends ChainNotaryBase {


    public chainId = 1;
    public contractInterface: ContractInterface
    protected OxOnPrivateKey : boolean = false  ;

    // @ts-ignore
    public getWeb3Provider(): Web3 {


        this.manager.returnError({"error": "implementation error"})

    }

    public getWeb3Subprovider() {

        let me = this.getWeb3Provider();
        return me.eth;

    }

    public async send() {
        super.send();
    }

    public  convertPrivateKey(privateKey:string):string {



        if (this.OxOnPrivateKey) return ChainNotaryEvm.removeOx(privateKey);

        return ChainNotaryEvm.addOx(privateKey);



    }

    protected static removeOx(privateKey:string):string{

        if (privateKey.startsWith('0x')) {

            privateKey = privateKey.substr(2);
        }

        return privateKey ;

    }

    protected static addOx(privateKey:string):string{

        if (!privateKey.startsWith('0x'))
            privateKey = '0x'+privateKey;

        return privateKey ;

    }


    public async balanceOf() {

        let subProvider: any = this.getWeb3Subprovider();
        let managerArgs: any = this.manager.arguments;

        // @ts-ignore
        const contract = new subProvider.Contract(ERC20, this.manager.arguments.contract, {gasLimit: 1000000})
        this.response = await contract.methods.balanceOf(this.manager.arguments.to).call().then(function (data) {

                return {address: managerArgs.to, balance: data};

            }
        );

        super.balanceOf();


    }

    public async ownerOf() {

        let subProvider: any = this.getWeb3Subprovider();
        // @ts-ignore
        const contract = subProvider.Contract(ERC721, this.manager.arguments.contract, {gasLimit: 1000000})

        console.log(this.manager.arguments.tokenPath.tokenId);
        console.log(this.manager.arguments.tokenPath);
        contract.methods.ownerOf(this.manager.arguments.tokenPath.tokenId).call().then(console.log);

    }



    public async mintTo() {


        let subProvider: any = this.getWeb3Subprovider();
        const sender = subProvider.accounts.wallet.add(this.convertPrivateKey(this.manager.arguments.key));


        let manager = this.manager ;


        const contract = new subProvider.Contract(MINTABLE, this.manager.arguments.contract, {gasLimit: 1000000})
        contract.methods.mintTo(this.manager.arguments.to).send({ from: sender.address, gas: '3000000' }).then(   (value) => {
            this.response = value;
            }).catch(function(e){

            let response = {error:e.message};

                console.log(e.message) ;
                manager.returnError(response);


        }).then(  () => {


            super.send()
        });


    }


    public contractStandardRouting(contractAddress, web3Internal) {

        if (typeof contractAddress == "undefined") return null ;

        let ABI = ERC20;
        this.contractInterface = ContractInterface.IERC20;

        if (this.manager.arguments.tokenPath && this.manager.arguments.tokenPath.tokenId) {
            ABI = ERC721;
            this.contractInterface = ContractInterface.IERC721;
        }

        return new web3Internal.Contract(ABI, contractAddress, {gasLimit: 1000000})

    }

    public sendMethodRouting(contractObject, receivingAddress) {

        let data = '';
        if (contractObject == null) return data ;


        switch (this.contractInterface) {

            case ContractInterface.IERC20:
                data = contractObject.methods.transfer(receivingAddress, this.manager.arguments.quantity).encodeABI();
                break;


            case ContractInterface.IERC721:
                data = contractObject.methods.transferFrom(this.manager.arguments.from, receivingAddress, this.manager.arguments.tokenPath.tokenId).encodeABI()

                break;

            default :
                this.manager.returnError("Cant define contract interface for send")

        }

        return data;

    }


    public createWallet() {

        let subProvider: any = this.getWeb3Subprovider();

        let account = subProvider.accounts.create('9jfirt43nfdslgjt43ofgj5gunvmdiwere');

        let response: WalletInterface = {address: account.address, privateKey: account.privateKey};
        this.response = response;
        return super.createWallet();
    }

    public chainParseTransactions(){
        let subprovider = this.getWeb3Subprovider()

        // @ts-ignore
        const contract = new  subprovider.Contract(ERC721, this.manager.arguments.contract, {gasLimit: 1000000})
        let chainParser = new ChainParser();
        chainParser.blockInterval = 10 ;
        let bootData = {fromBlock: 27991852,contract:contract};
       // bootData.toBlock = 27991853 ;
        //node CSNotary/NotaryExecutor.js --chain=klaytn --command=parseContractEvents --contract=0xe3656452c8238334efdfc811d6f98e5962fe4461
        chainParser.parseAndSendContractTransaction(this, bootData).then(r  =>console.log("sdf"))
    }

    public async getContractData(){
        let subprovider = this.getWeb3Subprovider()

        const validation = await this.manager.validateInputs(['contract'])

        let validERC20 = true ;
        let decimals = 0 ;
        let contract = null ;
        let symbol = null ;
        let name = null ;


        try {
             // @ts-ignore
            contract = new  subprovider.Contract(ERC20, this.manager.arguments.contract, {gasLimit: 1000000})
            decimals = await contract.methods.decimals().call().then(function (data) {return data})
            symbol = await contract.methods.symbol().call().then(function (data) {return data})
            name = await contract.methods.name().call().then(function (data) {return data})
            if ( decimals == undefined){
                this.response = 'not ERC-20 compatible';
                this.manager.returnError(this.response);
                return ;


            }
        }
        catch(err) {

            validERC20 = false ;


            let error = 'not ERC-20 compatible';
            this.manager.returnError(error);
            return ;

        }



        let response = {decimals:decimals,symbol:symbol,name:name,interfaces:['ERC20']};

        this.response = response
        super.send();






    }

    public async broadcast(){

        let response: any = {};

        var txString = this.manager.arguments.tx.toString();

        //console.log("broadcasting "+ web3.utils.toAscii(this.manager.arguments.tx));



        try {
            response.txHash = await this.waitForHash(txString);
        }
        catch(err) {

            response.error = err.message;
            this.response = response ;
            this.manager.returnError(response);
            return

        }



        this.response = response ;
        super.send();



    }


    public async createSend(){

        let web3Instance = this.getWeb3Provider();
        let subProvider = this.getWeb3Subprovider();

        const validation = await this.manager.validateInputs(['from','to'])

        const CONTRACT_ADDRESS = this.manager.arguments.contract
        const RECEIVER_ADDRESS= this.manager.arguments.to
        const SENDER_ADDRESS= this.manager.arguments.from

        let gasPriceGwei = null ;

        if (this.manager.arguments.fee){ gasPriceGwei =  web3.utils.toHex(this.manager.arguments.fee * 1e9) ;}
        else gasPriceGwei = await subProvider.getGasPrice();



        const contract = this.contractStandardRouting(CONTRACT_ADDRESS,subProvider);
        let quantity = "0x0";

        if (!contract) quantity = web3.utils.toHex(this.manager.arguments.quantity);

        //let gaslimit = this.estimateGas(contract,RECEIVER_ADDRESS);
        //console.log(gaslimit);

        let count = await subProvider.getTransactionCount(SENDER_ADDRESS,'pending');




        let gasLimit = 200000;
        // let gasLimit = 71000;
        const rawTransaction = {
            "from": SENDER_ADDRESS,
            "gasPrice": web3.utils.toHex(gasPriceGwei),
            //"gasPrice": web3.utils.toHex(gasPriceGwei * 1e9),
            "gasLimit": web3.utils.toHex(gasLimit),
            "to": CONTRACT_ADDRESS ?? RECEIVER_ADDRESS, // if no contract set then send main chain currency
            "value": quantity,
            "data":  this.sendMethodRouting(contract,RECEIVER_ADDRESS),
            'nonce': web3.utils.toHex(count),
            'chainId': web3.utils.toHex(this.chainId)

        };

        try {
            gasLimit = await subProvider.estimateGas(rawTransaction);
        }catch (e) {
            this.manager.returnError(e.message);
            return ;
        }

        rawTransaction.gasLimit =  web3.utils.toHex(gasLimit)


        this.response = rawTransaction;
        super.createSend()


    }

    public estimateGas(contractObject,receivingAddress){

        let gasRequirement = null ;

        switch (this.contractInterface) {

            case ContractInterface.IERC20:
                gasRequirement = contractObject.methods.transfer.estimateGas(receivingAddress, this.manager.arguments.quantity)
                break;


            case ContractInterface.IERC721:
                gasRequirement = contractObject.methods.transferFrom.estimateGas(this.manager.arguments.from, receivingAddress, this.manager.arguments.tokenPath.tokenId)

                break;

        }
    }

    public  waitForHash(signedTx) {
        return new Promise((resolve, reject) => {
            let web3Instance = this.getWeb3Subprovider();
            web3Instance.sendSignedTransaction(signedTx)
                .once('transactionHash', (hash) => {

                    resolve(hash)

                }).on('error', function (err) {
                reject(err)

            });
        })
    }


    public  async callContractMethod(contract) {

        return new Promise((resolve, reject) => {
            let web3Instance = this.getWeb3Subprovider();
            let decimals =  contract.methods.decimals().call().then(function (data) {resolve(data)},(e)=>{reject(e)}).catch(function(error){
            });


        })
    }

}

enum ContractInterface {
    IERC20 = 'IERC20',
    IERC721 = "IERC721"
}


