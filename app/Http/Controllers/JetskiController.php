<?php

namespace App\Http\Controllers;

use App\Blockchains\Counterparty\XcpAddressFactory;
use App\Blockchains\Counterparty\XcpEventFactory;
use App\Blockchains\Waves\WavesSynchronizationService;
use App\ResponseService;
use App\Services\Factories\jetskiFactory;
use CsCannon\BlockchainRouting;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use SandraCore\EntityFactory;

class TestController extends Controller
{


    public static $maxLimit = 10000000 ;
    public static $defaultLimit = 100 ;


    public function requestRun($instance,$blockchainString,$jwt)
    {

        $sandra  = app('Sandra')->getSandra();
        $responseService = new ResponseService();

        $blockchain = BlockchainRouting::getBlockchainFromName($blockchainString);

        if (!$blockchain) {
            $responseService->prepareError(404, "blockchain not found");
            return $responseService->getFormattedResponse() ;
        }


        $jetski = jetskiFactory::getLast($blockchain);

        if (!$jetski) $responseService->prepareError(404,'jetski not found');





    }

    public function getXCPBlockSync()
    {




    }

    public function sycWaves()
    {

        $sandra = app('Sandra')->getSandra();

        $wavessyc = new WavesSynchronizationService();
        $wavessyc->aspireTransactions();


    }

}
