<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 07.01.20
 * Time: 10:15
 */
namespace App\Services;

use App\BlockchainSyncProcess;
use App\ProcessFactory;
use CsCannon\Blockchains\BlockchainContractFactory;

class KlaytnService
{

    public function newWallet(){

        $cmd = "node public/caver/newWallet.js";
        return exec($cmd);


    }
    public function getPublicKey($key){

        $cmd = "node  public/caver/getWalletKey.js --key=$key";
        return exec($cmd);


    }

    public function sendUser($pubkey,$amount){


        $cmd = "node  public/caver/contractSend.js --receiver=$pubkey --quantity=$amount";
        return exec($cmd);
    }

    public function userSpend($pubkey,$amount,$senderKey){


        $cmd = "node  public/caver/contractSend.js --receiver=$pubkey --quantity=$amount --sender=$senderKey";
        return exec($cmd);
    }





}