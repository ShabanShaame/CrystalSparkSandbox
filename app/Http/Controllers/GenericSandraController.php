<?php


/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 26.03.19
 * Time: 10:27
 */

namespace App\Http\Controllers;









use App\AuthManager;
use App\ResponseService;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Generic\GenericContractFactory;
use SandraCore\EntityFactory;
use SandraCore\System;

class GenericSandraController extends Controller
{

    protected function getContractFromId($contractId):BlockchainContract{

        $contractFactory = new GenericContractFactory();
        $contract = $contractFactory->get($contractId);

        if (is_null($contract)) $this->returnNotFound($contractFactory);

        return $contract ;


    }

    protected function getSandra():System{

       return   app('Sandra')->getSandra();


    }

    protected function returnNotFound(EntityFactory $factoryNotFound = null){



       abort(404);



    }

    protected function returnError($message){



        abort(500,$message);



    }





}