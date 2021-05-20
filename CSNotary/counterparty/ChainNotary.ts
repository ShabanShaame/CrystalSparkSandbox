import {NotaryManager} from "../NotaryManager";
import {ChainNotaryBase, WalletInterface} from "../ChainNotaryBase";
import {ChainNotaryEvm} from "../evm/ChainNotary";


require('dotenv').config();
const { exec } = require("child_process");

export class ChainNotary extends ChainNotaryBase{


    public async broadcast(){



        const validation = await this.manager.validateInputs(['tx'])

        var txString = this.manager.arguments.tx.toString();


        let me = await exec("php "+__dirname+"/broadcast.php --tx='"+txString+"'" , (error, stdout, stderr) => {

            if (error) {
                console.log(`error: ${error.message}`);

            }
            if (stderr) {
                console.log(`stderr: ${stderr}`);

            }

            // console.log(`stdout: ${stdout}`);
            // process.exit();
            let xcpResponse = JSON.parse(stdout);

            if (xcpResponse.status == 'error') this.manager.returnError(xcpResponse);
            if (xcpResponse.error) this.manager.returnError(xcpResponse);
            this.response = xcpResponse;
            super.broadcast();



        });




    }


     public async send(){

         const validation = await this.manager.validateInputs(['from','contract','to','key','quantity'])

         let query = {from:this.manager.arguments.from,to:this.manager.arguments.to,contract:this.manager.arguments.contract,key:this.manager.arguments.key ,quantity:this.manager.arguments.quantity}

         exec("php "+__dirname+"/send.php --query='"+JSON.stringify(query)+"'" , (error, stdout, stderr) => {
             if (error) {
                 console.log(`error: ${error.message}`);
                 return;
             }
             if (stderr) {
                 console.log(`stderr: ${stderr}`);
                 return;
             }
            // console.log(`stdout: ${stdout}`);
            // process.exit();
             let xcpResponse = JSON.parse(stdout);
             if (xcpResponse.status == 'error') this.manager.returnError(xcpResponse);
             if (xcpResponse.error) this.manager.returnError(xcpResponse);
             this.response = xcpResponse;


             super.send();
         });



     }

     public createWallet() {

         exec("php "+__dirname+"/create_wallet.php" , (error, stdout, stderr) => {
             if (error) {
                 this.manager.returnError(error.message);
                // console.log(`error: ${error.message}`);
                 return;
             }
             if (stderr) {

                 this.manager.returnError(stderr);
                 return;
             }
             //console.log(`stdout: ${stdout}`);
            let reponseObj:WalletInterface = JSON.parse(stdout)
             this.response = reponseObj;
             super.createWallet();

         });


     }


}
