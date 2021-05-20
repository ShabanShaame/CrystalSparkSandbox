import {NotaryManager} from "./NotaryManager";

export class ChainNotaryBase {


    manager:NotaryManager = null;
    response:any = "unsuported method";
    legacyResponseType:boolean = true;

    constructor(manager :NotaryManager) {
        this.manager = manager ;


    }

    public flatTokenPath(){


    }

    public send(){



        let lastLine = null;

        if (this.legacyResponseType) lastLine = this.response.balance ;

        this.manager.returnResponse(this.response,lastLine);
    }

    public createSend(){

        let lastLine = null;

        if (this.legacyResponseType) lastLine = this.response.balance ;

        this.manager.returnResponse(this.response,lastLine);
    }


    public balanceOf(){
        console.log(this.response);

        let lastLine = null;

        if (this.legacyResponseType) lastLine = this.response.balance ;

        this.manager.returnResponse(this.response,lastLine);
    }

    public chainParseTransactions(){
        console.log(this.response);

        this.manager.returnResponse(this.response,this.response.balance);
    }

    public ownerOf(){



    }

    public getPendingTransactions(){

        this.manager.returnResponse(this.response,this.response.balance);

    }

    public createWallet(){

        this.manager.returnResponse(this.response,false);


    }

    public mintTo(){


        this.manager.returnResponse(this.response,false);


    }

    public broadcast(){




        this.manager.returnResponse(this.response,false);


    }


    public sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

}

export interface WalletInterface {
    address: string;
    privateKey:string;
}

