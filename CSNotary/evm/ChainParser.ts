import {NotaryManager} from "../NotaryManager";
import {ChainNotaryEvm} from "./ChainNotary";

//this is to count how many transaction are sent back from the blockchain node
let countNodeResponses:number  ;

export class ChainParser {

    transactions:TransactionList;
    subProvider ;
    contract  ;
    contractAddress : string ;
    blockInterval:number = 5000;
    actualInterval:number = 5000 ;
    countLastEvents:number = 0 ;
    retreiveBlockTimestamp:boolean = true; //a true create a significant amount of call to the node. To use when not rate limited


   public async parseAndSendContractTransaction(notary:ChainNotaryEvm,bootData : BootContractInterface){

       let toBlock = bootData.fromBlock + this.blockInterval ;
       this.contract = bootData.contract
       this.subProvider =  notary.getWeb3Subprovider();

       let latest = await notary.getWeb3Subprovider().getBlockNumber();

        if (bootData.toBlock) latest = bootData.toBlock ;
        if (toBlock > bootData.toBlock ) toBlock = bootData.toBlock ;


       let currentBlock = bootData.fromBlock ;
       var iteration = 0 ;
       countNodeResponses = 0 ;

       while (currentBlock < latest) {
           iteration++;


           await notary.sleep(200);

           if (iteration > 40000){
               console.log("Should break");
               break ;
           }
           ;
           console.log("iteration"+iteration);
           console.log("Number of last events"+countNodeResponses);


           await this.parse({fromBlock: currentBlock, toBlock: toBlock},notary).then(value => {


               if(countNodeResponses < 10000)
                   this.actualInterval = this.actualInterval *2

               if(countNodeResponses > 50000){
                   this.actualInterval = this.actualInterval /2
                   //we had too many events let's cool it down
                   console.log("Cooling block interval to "+this.actualInterval);

               }


               currentBlock = toBlock ;
               toBlock = toBlock + this.actualInterval ;
               toBlock = Math.floor(toBlock);
               console.log("intervalling"+this.actualInterval);


           }).catch((error) => {
               this.actualInterval = this.actualInterval /6

               toBlock = currentBlock - this.actualInterval ;
               toBlock = Math.floor(toBlock);
               console.log(error)
               console.log("Promise reject slashing interval to "+this.actualInterval);


           });


       }
    }

    private async parse(data:ParserData,notary:ChainNotaryEvm) {


        console.log("starting transaction");
        console.log(data);
        let blockTimestampCall = this.retreiveBlockTimestamp;

        var transactions = this.transactions ;


        async function dispatchTransactions(transactions: TransactionList) {

            const axios = require('axios').default;

            return await axios.post('http://localhost:8888/crystalcontrolcenter/public/api/v1/events/klaytn/import', transactions.list)
                //const response = await axios.post('http://baster.bitcrystals.com/api/v1/events/import', transactions.list)
                .then(function (response) {
                    console.log(response.data);
                    console.log("dataPushed")
                })
                .catch(function (error) {
                    console.log(error.response.data.message);
                    console.log("stopping all");
                    process.exit()
                })


        }

        var parse =  await this.contract.getPastEvents('Transfer', {
            fromBlock: data.fromBlock,
            toBlock: data.toBlock
        }, async function (error, events) {




        }).then(async r =>{


            async function process(){
                var countEvents = r.length;
                countNodeResponses = countEvents ;
                var processed = 0 ;


                transactions = new TransactionList();

                console.log("countEvent " + countEvents);

                for (const event of r) {

                    const  getTimestamp = async () => {

                        //  console.log('Taking a break...');

                        // console.log('Two seconds later, showing sleep in a loop...');
                        // console.log("Ouh oh we should sleep")
                        // await sleep(1);
                        //if (processed % 10 == 0)
                        //console.log("enoughSleep"+processed)
                        if (!blockTimestampCall)
                        return 1 ;

                        return await  getBlocktime(notary.getWeb3Subprovider(),event.blockNumber);

                    }
// or

                    await getTimestamp().catch((error) => {

                        console.log("Timestamp rejected");
                        throw (error);

                    }).then(async value => {

                        processed++;
                        if (event.returnValues == true) return //what is this
                        //console.log("processed "+processed);
                        var token = [];
                        token['tokenId'] = event.returnValues.tokenId ;
                        console.log(event.returnValues);

                        transactions.addTransaction({
                            tokenId: event.returnValues.tokenId,
                            source: event.returnValues.from,
                            contract: event.address,
                            destination: event.returnValues.to,
                            txHash: event.transactionHash,
                            transactionIndex: event.transactionIndex,
                            blockIndex:event.blockNumber,
                            blockTime:value

                        })

                        if (countEvents == processed){
                            const done = await dispatchTransactions(transactions);
                        }

                    });
                }

            }

            var processResult = await process().catch((error) => {
                //this.actualInterval = this.actualInterval /10
                console.log("error while processing");
                throw (error)


            })

                .then(value => {
                    console.log("should be all finished"+value);
                });



        }).catch((error) => {
            console.log("too much");
            throw error;

        });


        async function getBlocktime(web3Provider, blockId:number){


            var timestamp =  web3Provider.getBlock(blockId, function (error, block) {



                if (!error){


                    timestamp = block.timestamp;

                }

            }).catch((error) => {
                console.log("time is to fast");
                throw error;
            }).then(r =>{


                return timestamp ;
            });

            return await timestamp ;





        }


    }







}

interface BootContractInterface {

    fromBlock:number,
    toBlock?:number,
    contract:any,
}

var http = require('http');
var https = require('https');

class TransactionList {
    constructor() {
        this.list = [];
    }
    list : Transaction[];
    addTransaction(transaction : Transaction){

        // if (!transaction.tokenId || !transaction.source ||!transaction.destination){ return ;}
        this.list.push(transaction);


    }}

interface Transaction{
    // token:Array<any>;
    tokenId:string;
    source:string;
    destination:string;
    contract:string;
    txHash:string;
    transactionIndex:number
    blockIndex:number
    blockTime:number

}

interface ParserData{
    fromBlock?:number;
    toBlock?:number;


}

class options{
    retreiveBlockTimestamp:boolean=false ;


}

