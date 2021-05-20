<?php

namespace App\Http\Controllers\CSNotary;
use App\Http\Controllers\Controller;
use App\Services\CSNotaryInterface;
use App\Services\UserService;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC20;
use CsCannon\Blockchains\Interfaces\UnknownStandard;
use CsCannon\Blockchains\SignableBlockchainAddress;
use Illuminate\Http\Request ;

/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 28.10.20
 * Time: 12:48
 */



class CSNotaryController extends Controller
{


    public function helloNotary(){

        return ["CSNotary"=>"hello Notary"];

    }

    public function createWallet(Request $request){



       $chain = $this->validateBlockchainString($request);

        $notaryInterface = new CSNotaryInterface($chain,
            __DIR__."../../../../../CSNotary");

        return $notaryInterface->rawCreateWallet();

    }

    public function createSend(Request $request){


        $chain = $this->validateBlockchainString($request);



        $from = $chain->getAddressFactory()::getAddress($this->requireParam($request,'source'));
        $to = $chain->getAddressFactory()::getAddress($this->requireParam($request,'destination'));
        if (isset ($request->contract))
        $contract = $chain->getContractFactory()::getContract($request->contract,false);
        else
            $contract = null ;

        $quantity = $this->requireParam($request,'quantity');

        //dd($contract);

        $notaryInterface = new CSNotaryInterface($chain,
            __DIR__."../../../../../CSNotary");


        $request->key ? $privateKey = $request->key : $privateKey = false ;


        if ($privateKey){
            $signableAddress = new SignableBlockchainAddress($from,$privateKey);
            return $this->csNotaryParseResponse($notaryInterface->send($signableAddress,$to,$contract,
                UnknownStandard::init($this->hasJsonParam($request,'token')),$quantity,$request->fee ?? null));
        }


        return $notaryInterface->createSend($from,$to,$contract,
            UnknownStandard::init($this->hasJsonParam($request,'token')),$quantity,$request->fee ?? null);



    }

    public function broadcast(Request $request){

        $chain = $this->validateBlockchainString($request);

        //dd("here");


        $tx = $this->requireParam($request,'tx');


        $notaryInterface = new CSNotaryInterface($chain,
            __DIR__."../../../../../CSNotary");

        return $notaryInterface->broadcast($tx);


    }

    public function getContractData(Request $request,$contractId){

        $chain = $this->validateBlockchainString($request);




        $notaryInterface = new CSNotaryInterface($chain,
            __DIR__."../../../../../CSNotary");

        $contract = $chain->getContractFactory()::getContract($contractId,false);
        return $notaryInterface->getContractData($contract);


    }

    public function csNotaryParseResponse($response){


        if (is_object($response )){
            $response = json_encode($response);
        }
        return $response ;


    }


    public function csNotaryReject($message,$status = 404){


       // die("reject");
        response()->json(['status'=>'error','message'=>$message],$status)->send();
        die();
       // return ['status'=>'error','message'=>$message];


    }

    public function requireParam(Request $request, $param){

        if (!$request->has($param)) {
            $this->csNotaryReject("$param is required");
        }

        return $request->$param ;

    }




    public function hasJsonParam(Request $request, $param){


        if (!$request->has($param)) {
            return null ;
        }
        try {

          $jsonArray =  json_decode($request->$param);
        }catch (\Exception $exception){
            return null ;

        }

        return $jsonArray ;

    }


    private function validateBlockchainString(Request $request):Blockchain{

        $chainString = $this->requireParam($request,'chain');
        if ($chainString == 'bitcoin') $chainString = 'counterparty'; //hack to force bitcoin into counterparty
        $chain = BlockchainRouting::getBlockchainFromName($chainString);
        if (!$chain) $this->csNotaryReject("blockchain not supported");
        //dd($chain::NAME);

        return $chain ;

}



}
