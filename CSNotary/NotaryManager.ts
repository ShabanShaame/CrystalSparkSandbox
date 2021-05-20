


const argv = require('minimist')(process.argv.slice(2),{

    string: ['from','contract','to','key'],

    int: ['quantity']// --lang xml


});



 export class NotaryManager  {
    constructor() {

    }

    status : CSNstatus = CSNstatus.ok
     verbose : boolean = false;
     arguments = null;
     response:CSNResponse = new CSNResponse(CSNstatus.ok,'');

    public returnError(response :Object){

        this.status = CSNstatus.error;

        this.returnResponse(response,false);

    }

     public flatTokenPath(){

         this.arguments.tokenPath = JSON.parse(this.arguments.tokenPath);

     }

    public returnResponse(response :Object,rawLastLine:boolean=true){

        this.response.setMessage(response);
        this.response.status = this.status ;
        console.log(JSON.stringify(this.response));

        if (rawLastLine)
            console.log(rawLastLine);

        process.exit();

    }

    public validateInputs(required : Array<string>) {


         let error:Array<string> =  [] ;
         let receivedArgs = this.arguments;




        required.forEach(function (argument) {


             if (!receivedArgs[argument]) error.push(argument) ;

         })

        if (error.length > 0) {
            let fullError = {message:"missing arguments",list:error}
            this.returnError(fullError);
        }

    }

    public parseIntent(){


         const argv = require('minimist')(process.argv.slice(2),{

             string:  ["command","chain",'from','contract','to','key','query','legacy','feePayerPrivate','feePayerPublic','tx']


         });
         this.arguments = argv;
        if (this.arguments.tokenPath) this.flatTokenPath();

        if (argv.chain == 'bitcoin') argv.chain = 'counterparty' // hack to push counterpaty
        if (argv.chain == 'goerli') {argv.chain = 'ethereum' ; this.arguments.network = 'goerli'}
        if (argv.chain == 'bitcoin') argv.chain = 'counterparty' // hack to push counterpaty





        import("./"+argv.chain+"/ChainNotary")
            .then((notary) => {


                let my = new notary.ChainNotary(this);

                this.response.meta.chain = this.arguments.network ;


                if (this.arguments.legacy == 'false'){

                    my.legacyResponseType = false ;
                }

                try {

                    switch (this.arguments.command) {


                        case 'send' :
                            my.send();
                            break;
                        case 'balanceOf' :
                            my.balanceOf();
                            break;
                        case 'ownerOf' :
                            my.ownerOf();
                            break;
                        case 'createWallet' :
                            my.createWallet();
                            break;
                        case 'parseContractEvents' :
                            my.chainParseTransactions();
                            break;
                        case 'mintTo' :
                            my.mintTo();
                            break;
                        case 'getPending' :
                            my.getPendingTransactions();
                            break;
                        case 'createSend' :
                            my.createSend();
                            break;
                        case 'broadcast' :
                            my.broadcast();
                            break;
                        case 'getContractData' :
                            my.getContractData();
                            break;

                        default :
                            this.returnError({message: "Command not found " + this.arguments.command + " "})

                    }
                }catch (e){
                    console.log("EEERRROOROOROR")
                    this.returnError({message: "error" + e + " "})
                }



            })
            .catch((err) => {
                console.log(err);
                this.log("./"+argv.chain+"/ChainNotary");
                this.returnError({message:"CSNotary unsupported "+argv.chain+" chain"})
            });

         this.log(argv.command);
         this.log(argv.chain);

     }

     public log(log){

        if (this.verbose) {
            console.log(log)
        }

     }

}

class CSNResponse {

    meta : any = {version:0.1,chain:'unknown'}
    status : CSNstatus = CSNstatus.ok
    data : Object = null ;


    constructor(status:CSNstatus,message) {


        this.data = message;
        this.status = status ;
    }

    public  return(){

        let i = 3 ;
    }

    public  setChain(chain:string){

       this.meta.chain = chain;
    }

    public  setMessage(message:any){

        this.data = message;
    }


}


 enum CSNstatus {
   "ok" ="ok",
     "error" ="error"
}



