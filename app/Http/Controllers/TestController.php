<?php

namespace App\Http\Controllers;

use App\Blockchains\Counterparty\XcpAddressFactory;
use App\Blockchains\Counterparty\XcpEventFactory;
use App\Blockchains\Waves\WavesSynchronizationService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TestController extends Controller
{


    public static $maxLimit = 10000000 ;
    public static $defaultLimit = 100 ;


    public function getAddress()
    {

        $limit = request()->limit ;
        $offset = request()->offset ;

        if (!is_numeric($limit)){
            $limit = self::$defaultLimit ;
        }

        if ($limit > self::$maxLimit){
            $limit = self::$maxLimit ;
        }

        if (!is_numeric($offset)){
            $offset = 0 ;
        }


        $addressFactory = new XcpEventFactory();
        $addressFactory->populateLocal($limit);


      $count = count($addressFactory->entityArray);


        return $count;


    }

    public function getXCPBlockSync()
    {

        $sandra = app('Sandra')->getSandra();

        $synchroFactory = new \SandraCore\EntityFactory('trackStatus', 'systemTrackStatusFile', $sandra);
        $synchroFactory->populateLocal();
        $memo = $synchroFactory->getOrCreateFromRef('id', 'xcpSynchroniser');
        $synched = $memo->getOrInitReference("syncBlockDesc", '577024');

        return $synched->refValue ;


    }

    public function sycWaves()
    {

        $sandra = app('Sandra')->getSandra();

        $wavessyc = new WavesSynchronizationService();
        $wavessyc->aspireTransactions();


    }

}
