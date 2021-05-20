<?php

namespace App\Http\Controllers;


use App\ResponseService;
use App\XCPTransactions;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\Counterparty\DataSource\XchainOnBcy;
use Illuminate\Http\Request;
use SandraCore\EntityFactory;
use SandraCore\System;

class TokenController extends Controller
{
    public static $maxLimit = 100 ;
    public static $defaultLimit = 100 ;
    /**
     * @var ResponseService
     */
    private $responseService;

    public function __construct()
    {
        $this->responseService = new ResponseService() ;

    }

//    public function getContractInfo($blockchainString,$contractId){
//       $blockchain = BlockchainRouting::getBlockchainFromName($blockchainString);
//
//       if (!$blockchain) {
//           $response = $this->responseService->prepareError(404,"Blockchain not found : $blockchainString");
//           return response()->json($response, 404);
//
//       }
//
//        XchainOnBcy::$dbHost = env('DB_HOST_XCP');
//        XchainOnBcy::$db =  env('DB_DATABASE_XCP');
//        XchainOnBcy::$dbUser = env('DB_USERNAME_XCP');
//        XchainOnBcy::$dbpass = env('DB_PASSWORD_XCP');
//
//       $contract = $blockchain->getContractFactory()->get($contractId,false);
//       $contract->getId();
//       $contract->setDataSource(new XchainOnBcy());
//       $metadata = $contract->metadata->refreshData();
//
//
//       return $metadata->getDisplay();
//
//
//
//
//
//
//
//
//
//    }



}
