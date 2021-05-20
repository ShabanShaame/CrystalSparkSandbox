<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 26.03.19
 * Time: 10:27
 */

namespace App\Http\Controllers;


use App\EnvRouting;
use App\LazyHackClass;
use App\ResponseService;
use App\SystemConfig;
use CsCannon ;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Counterparty\DataSource\XchainOnBcy ;
use CsCannon\Blockchains\Ethereum\EthereumAddress;
use CsCannon\Blockchains\Klaytn\KlaytnAddressFactory;
use SandraCore\Concept;
use SandraCore\DatabaseAdapter;


class BalanceController extends Controller
{




    public function getBalance($address){

        if (CsCannon\SandraManager::getSandra()->env == "xulnoc"){return $this->getBalanceXulNoc($address);}


        XchainOnBcy::$dbHost = env('DB_HOST_XCP');
        XchainOnBcy::$db =  env('DB_DATABASE_XCP');
        XchainOnBcy::$dbUser = env('DB_USERNAME_XCP');
        XchainOnBcy::$dbpass = env('DB_PASSWORD_XCP');


        $found = DatabaseAdapter::searchConcept(CsCannon\SandraManager::getSandra(),$address,CsCannon\Blockchains\BlockchainAddressFactory::ADDRESS_SHORTNAME);


        //find blockchain
        $firstFound = $found[0];

        $sandra = CsCannon\SandraManager::getSandra();
        $onBlockchain = $sandra->systemConcept->get( BlockchainContractFactory::ON_BLOCKCHAIN_VERB);

        $addressConcept = $sandra->conceptFactory->getConceptFromId($firstFound);
        $triplets = $addressConcept->getConceptTriplets(1);
        $blockchainsForAddress= $triplets[$onBlockchain] ?? array();
        $addressEntity = null ;


        $balance = new CsCannon\Balance() ;

        foreach ($blockchainsForAddress as $blockchainId){

           $blockchain = BlockchainRouting::getBlockchainFromName($sandra->systemConcept->getSCS($blockchainId));
           if (!$blockchain) continue ;

           $factory = $blockchain->getAddressFactory();
           $addressEntity = $factory->get($address);

                $addressEntity->setDataSource(new CsCannon\Blockchains\DataSource\DatagraphSource());
               $balance = $addressEntity->getBalance();


        }

        //legacy support
        if ($addressConcept && !$addressEntity) {

            LazyHackClass::hackToFixOnBlockchainForKusama($address,$sandra);

            $addressFactory = BlockchainRouting::getAddressFactory($address);
            $addressEntity = $addressFactory->get($address);

        }

        $offset = 0 ;
        $lastQueryTokenCount = $balance->tokenCount ;




        while ($addressEntity->getDataSource() instanceof CsCannon\Blockchains\Ethereum\DataSource\OpenSeaDataSource && $offset <= 4
        && $lastQueryTokenCount > 49
        ){
            $limit = 50;
            $offset++ ;

            $balanceMerge = $addressEntity->getBalance($limit,$offset*$limit);
            $lastQueryTokenCount = $balanceMerge->tokenCount ;

            $balance->merge($balanceMerge);

        }


        //hack to remove non active open sea collections ------<
        $ethereumCollectionToKeep = ContractController::getTrackedContracts();
        foreach ($ethereumCollectionToKeep as $contract){

            $keepList[$contract->getId()] = $contract;
        }
        foreach (isset($balance->contracts['ethereum']) ? $balance->contracts['ethereum'] : array() as $contractKey => $contract){
            if (!isset($keepList[$contractKey])){
               unset( $balance->contracts['ethereum'][$contractKey]);


            }

        }
        //hack to remove non active open sea collections ------>


        $balance = LazyHackClass::hackToIntegrateKlaytnBalance($addressEntity,$balance);

        $tokens = $balance->getTokenBalance();
        $orbs = $balance->getObs();


      //  $sog = new TokenCollection('eSog') ;
        $responseService = new ResponseService();

        $additionalHead = null ;

        return  $responseService->prepare($balance->returnObsByCollections(),$additionalHead)->getFormattedResponse();

    }

    public function getTokenBalance($address){


        if (CsCannon\SandraManager::getSandra()->env == "xulnoc"){return $this->getBalanceXulNoc($address,true);}

        // $addressFactory = BlockchainRouting::getAddressFactory($address);
        $addressFactory = BlockchainRouting::getAddressFactory($address);

        if (CsCannon\SandraManager::getSandra()->env == "uniquenet"){$addressFactory = new CsCannon\Blockchains\Substrate\Unique\UniqueAddressFactory();}


        //$addressFactory = new CsCannon\Blockchains\Klaytn\KlaytnAddressFactory(); //Forcing klay



        $addressEntity = $addressFactory->get($address);

        $dataSource = $addressEntity->getDataSource();

        if ($addressEntity->getDataSource() instanceof XchainOnBcy){
            /** @var XchainOnBcy $dataSource */

            XchainOnBcy::$dbHost = env('DB_HOST_XCP');
            XchainOnBcy::$db =  env('DB_DATABASE_XCP');
            XchainOnBcy::$dbUser = env('DB_USERNAME_XCP');
            XchainOnBcy::$dbpass = env('DB_PASSWORD_XCP');
            //dd("entered XCHAIN BCY")
        }

        //dd($addressEntity);
        $balance = $addressEntity->getBalance();

        $offset = 0 ;
        $lastQueryTokenCount = $balance->tokenCount ;

        while ($addressEntity->getDataSource() instanceof CsCannon\Blockchains\Ethereum\DataSource\OpenSeaDataSource && $offset <= 4
            && $lastQueryTokenCount > 49
        ){
            $limit = 50;
            $offset++ ;

            $balanceMerge = $addressEntity->getBalance($limit,$offset*$limit);
            $lastQueryTokenCount = $balanceMerge->tokenCount ;

            $balance->merge($balanceMerge);

        }



        $balance = LazyHackClass::hackToIntegrateKlaytnBalance($addressEntity,$balance);


        $tokens = $balance->getTokenBalance();
   //  $sog = new TokenCollection('eSog') ;



        $responseService = new ResponseService();


        $additionalHead = null ;



        return  $responseService->prepare($tokens,$additionalHead)->getFormattedResponse();



    }

    //This should be removed when we put matic in production (Milestone 2)
    public function getBalanceXulNoc($address,$onlyTokens=false){




        $addressFactory = new CsCannon\Blockchains\Ethereum\Sidechains\Matic\MaticAddressFactory(); //Forcing Matic


        $addressEntity = $addressFactory->get($address);

        $dataSource = $addressEntity->getDataSource();

        if ($addressEntity->getDataSource() instanceof XchainOnBcy){
            /** @var XchainOnBcy $dataSource */

            XchainOnBcy::$dbHost = env('DB_HOST_XCP');
            XchainOnBcy::$db =  env('DB_DATABASE_XCP');
            XchainOnBcy::$dbUser = env('DB_USERNAME_XCP');
            XchainOnBcy::$dbpass = env('DB_PASSWORD_XCP');

        }



        //dd($addressEntity);
        $balance = $addressEntity->getBalance();




        //$tokens = $balance->getTokenBalance();
        // $orbs = $balance->getObs();

        //Merged balance test


                $addressFactory2 = new CsCannon\Blockchains\Ethereum\EthereumAddressFactory();

                $addressEntity2 = $addressFactory2->get($address);
                $addressEntity2->setDataSource(new CsCannon\Blockchains\DataSource\DatagraphSource());

                 $balance2 = $addressEntity2->getBalance();
                $balance->merge($balance2);




        $tokens = $balance->getTokenBalance();
        $orbs = $balance->getObs();


        //  $sog = new TokenCollection('eSog') ;
        $responseService = new ResponseService();


        $additionalHead = ['xulNoc'=>"interceped"];

        if ($onlyTokens) {
            $additionalHead = ['onlyToken'=>"yes"];
           return $responseService->prepare($tokens, $additionalHead)->getFormattedResponse();

        }

        return  $responseService->prepare($balance->returnObsByCollections(),$additionalHead)->getFormattedResponse();



    }


    public function ownerOf($contract,$token){





    }


}
