<?php

namespace App\Http\Controllers;

use App\AssetCollectionFactory;

use App\ResponseService;
use App\Services\AppService;
use App\Services\PaginateDatabase;
use App\Services\UserFactory;
use App\Services\UserService;
use App\SystemConfig;
use CsCannon\AssetCollection;
use CsCannon\AssetFactory;
use CsCannon\AssetSolvers\PathPredictableSolver;
use CsCannon\BlockchainRouting;


use App\XCPTransactions;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainAddressFactory;
use CsCannon\Blockchains\BlockchainBlock;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\BlockchainEvent;
use CsCannon\Blockchains\BlockchainEventFactory;
use CsCannon\Blockchains\BlockchainImporter;
use CsCannon\Blockchains\BlockchainToken;
use CsCannon\Blockchains\Counterparty\XcpEventFactory;
use CsCannon\Blockchains\Ethereum\EthereumAddress;
use CsCannon\Blockchains\Ethereum\EthereumBlockchain;
use CsCannon\Blockchains\Ethereum\EthereumEventFactory;
use CsCannon\Blockchains\Ethereum\EthereumImporter;
use CsCannon\Blockchains\Ethereum\RopstenEventFactory;
use CsCannon\Blockchains\Ethereum\Sidechains\Matic\MaticBlockchain;
use CsCannon\Blockchains\Ethereum\Sidechains\Matic\MaticEventFactory;
use CsCannon\Blockchains\FirstOasis\FirstOasisEventFactory;
use CsCannon\Blockchains\Generic\GenericAddressFactory;
use CsCannon\Blockchains\Generic\GenericContractFactory;
use CsCannon\Blockchains\Generic\GenericEventFactory;
use CsCannon\Blockchains\Klaytn\KlaytnAddressFactory;
use CsCannon\Blockchains\Klaytn\KlaytnBlockchain;
use CsCannon\Blockchains\Klaytn\KlaytnContractFactory;
use CsCannon\Blockchains\Klaytn\KlaytnEventFactory;
use CsCannon\Blockchains\Substrate\Kusama\KusamaEventFactory;
use CsCannon\Blockchains\Substrate\Kusama\WestendEventFactory;
use CsCannon\Blockchains\Substrate\Unique\UniqueAddressFactory;
use CsCannon\Blockchains\Substrate\Unique\UniqueBlockchain;
use CsCannon\Blockchains\Substrate\Unique\UniqueEventFactory;
use CsCannon\SandraManager;
use CsCannon\Tools\BalanceBuilder;
use Ethereum\Sandra\EthereumContractFactory;
use Illuminate\Http\Request;
use Matrix\Exception;
use SandraCore\CommonFunctions;
use SandraCore\Concept;
use SandraCore\ConceptManager;
use SandraCore\DatabaseAdapter;
use SandraCore\Entity;
use SandraCore\EntityFactory;
use SandraCore\ForeignEntity;
use SandraCore\ForeignEntityAdapter;
use SandraCore\System;
use Symfony\Component\VarDumper\Cloner\Data;

class TransactionsController extends Controller
{
    public static $maxLimit = 500;
    public static $defaultLimit = 500;
    private $eventList;


    public function syncLocal()
    {

        $sandra = app('Sandra')->getSandra();

        $localImporter = new XCPTransactions();
        $localImporter->localImporterWithAbstract($sandra);


    }

    public function getEvents($address = null, AssetCollection $collection = null,$withOrbs=true)
    {

        $responseService = new ResponseService();


        $sandra = app('Sandra')->getSandra();
        $filter = array();




        $limit = request()->limit;
        $offset = request()->offset;

        $databasePagination = new PaginateDatabase($limit,$offset);

        $config = new SystemConfig();
        $activeBlockchains = $config->getActiveBlockchains();



        //do we have a collection filter ?
        if (request()->collection or $collection) {


            if (request()->collection) {
                $collectionFactory = new \CsCannon\AssetCollectionFactory($sandra);
                $collectionFactory->populateLocal();
                $collectionEntity = $collectionFactory->get(request()->collection);


            }

            if ($collection instanceof AssetCollection) {
                $collectionEntity = $collection;
            }

            if (!$collectionEntity) {
                $responseService->prepareError(404, 'collection not found');
                return $responseService->getFormattedResponse();
            }

            $blockchainContractFactory = new GenericContractFactory();
            $blockchainContractFactory->setFilter(BlockchainContractFactory::JOIN_COLLECTION, $collectionEntity);
            $contracts = $blockchainContractFactory->populateLocal();
            // dd($collectionEntity->dumpMeta());

            $filter = [BlockchainEventFactory::EVENT_CONTRACT => $contracts];

        }

        if(request()->contracts){
            $contractArray = explode(',',request()->contracts);
            $filter = [BlockchainEventFactory::EVENT_CONTRACT => $contractArray];

        }

        if(request()->token){
            $tokenArray  = explode(',',request()->contracts);


        }



        $addressArray = null;

        $allTransactionsArray = array();
        $rawAddress = '';

        if (isset($address)) {
            $addressArray = explode(',', $address);
        }


        if (is_array($addressArray)) {


                /** @var $sandra System */

                //first we find the address on our datagraph
                $addressConceptsList = DatabaseAdapter::searchConcept($sandra,$addressArray,$sandra->systemConcept->get('address'));

                $conceptManager = new ConceptManager($sandra);
                if ($addressConceptsList) {
                    $conceptManager->getConceptsFromArray($addressConceptsList);
                    $isaUnid = $sandra->systemConcept->get('is_a');

                    //get all active blockchains
                    foreach ($activeBlockchains as $blockchain) {

                        $blockchain = BlockchainRouting::getBlockchainFromName($blockchain->get('blockchain'));

                        $addressFactory = $blockchain->getAddressFactory();
                        $blockchainIsaAddressIndex[$addressFactory->entityIsa] = $blockchain;

                    }
                }

            $blockchainToQuery = array();

                //we find the right factory for the concepts

                foreach ($conceptManager->getTriplets() as $subject => $verbArray){ //foreach address =>subject
                    //echo "$subject = subject".PHP_EOL;
                    foreach ($verbArray[$isaUnid] ?? array() as $isaVerb => $target){ // For each verb isa

                        //now load the correct address factory
                        if (isset($blockchainIsaAddressIndex[$sandra->systemConcept->getSCS($target)])){
                            $blockchainToQuery[$sandra->systemConcept->getSCS($target)] =
                                $blockchainIsaAddressIndex[$sandra->systemConcept->getSCS($target)];
                        }
                }}



                //now we make a request to query each matching blockchains
                foreach ($blockchainToQuery as $blockchain) {

                    $addressFactory = $blockchain->getAddressFactory();
                    $addressFactory->conceptArray = $addressConceptsList; //we load the factory only with known concepts
                    $addressFactory->populateLocal();
                    $eventFactory = $blockchain->getEventFactory();
                    foreach ($addressFactory->getEntities() as $addressEntity) { //for each address found here
                        $eventFactorySend = new $eventFactory ;
                        $eventFactoryReceive = new $eventFactory ;


                        if ($eventFactory instanceof KusamaEventFactory or $eventFactory instanceof WestendEventFactory  ){
                            try {

                                $this->buildBalances($eventFactory);
                            }catch (\Exception $e){

                            }
                        }

                        //find the correct entity


                        $allTransactionsArrayFrom = $this->getEventsSegment($eventFactorySend, $databasePagination->limit, $databasePagination->offset, $addressEntity,
                            array(BlockchainEventFactory::EVENT_SOURCE_ADDRESS => $addressEntity),$withOrbs);
                        $allTransactionsArrayTo= $this->getEventsSegment($eventFactoryReceive, $databasePagination->limit, $databasePagination->offset, $addressEntity,
                            array(BlockchainEventFactory::EVENT_DESTINATION_VERB => $addressEntity),$withOrbs);

                        $allTransactionsArray = array_merge($allTransactionsArrayTo, $allTransactionsArrayFrom);

                }
            }


        } else {

            //all events requested

            foreach ($activeBlockchains as $blockchain){

                $blockchain = BlockchainRouting::getBlockchainFromName($blockchain->get('blockchain'));

                $blockchainEventFactory = $blockchain->getEventFactory();

                if ($blockchainEventFactory instanceof KusamaEventFactory or $blockchainEventFactory instanceof WestendEventFactory){
                    try {
                        set_time_limit ( -1 );
                        $this->buildBalances($blockchainEventFactory);
                    }catch (\Exception $e){
                      //  dd($e);
                    }
                }


                $allTransactionsArray += $this->getEventsSegment($blockchainEventFactory, $databasePagination->limit, $databasePagination->offset, $address, $filter,$withOrbs);
            }

        }

        usort($allTransactionsArray, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        $allTransactionsArray = array_reverse($allTransactionsArray);



        $additionalHead = null;

        //If we have a single collection and the asset are not in the datagraph we build a list of orb based on last transactions
        if (isset($collectionEntity)) {
            $solverArray = $collectionEntity->getSolvers();
            $specifiers = array();
            foreach ($solverArray ? $solverArray : array() as $solver) {
                if ($solver instanceof PathPredictableSolver) {
                    $count = 0;
                    foreach ($this->eventList ? $this->eventList : array() as $event) {
                        /** @var BlockchainEvent $event */
                        $count++;
                        if ($count >= 20) break;

                        $specifiers[] = $event->getSpecifier();

                    }
                    //we save the specifiers into collection sample
                    if (!empty($specifiers)) {
                        $collectionEntity->storeSample($specifiers);
                    }
                }
            }
        }
        return $responseService->prepare($allTransactionsArray, $additionalHead)->getFormattedResponse();

    }

    public function getEventsRaw($address = null, AssetCollection $collection = null)
    {

    return $this->getEvents($address,$collection,false);



    }

    private function getEventsSegment(BlockchainEventFactory $eventFactory, $limit, $offset, $addressEntity = null, $filters = array(),$withOrbs=true)
    {


        $returnArray = array();
        $blockchainEnventFactory = $eventFactory;



        foreach ($filters as $key => $value) {

            if (!$value instanceof Entity and !is_array($value)) return array(); // the address is not in the datagraph

            try {
                $blockchainEnventFactory->setFilter($key, $value);

            }catch (\Exception $e){

                return array() ;
            }
        }
        //$blockchainEnventFactory->setFilter(BlockchainEvent::BLOCKCHAIN_EVENT_TYPE_VERB)


        $this->eventList = $blockchainEnventFactory->populateLocal($limit, $offset);




        // we keep received specifiers
        foreach ($this->eventList ? $this->eventList : array() as $event) {
            /** @var BlockchainEvent $event */
            $event->getBlockchainContract();


        }


        $returnArray = $blockchainEnventFactory->display($withOrbs)->return();


        //if we want user data
        if ($blockchainEnventFactory->addressFactory) {



            $userFactory = UserFactory::getUserFactoryFromAddressFactory($blockchainEnventFactory->addressFactory);
            $map = $userFactory->getAddressMap($blockchainEnventFactory->addressFactory);



            $appS = new AppService();
            $app = $appS->getOrCreateApp('spellsofgenesis');
            $userService = new UserService();

            //now we add username to each sender
            foreach ($returnArray as $key => $value) {

                $sourceAddress = $value['source'];

                if (isset ($map[$sourceAddress])) {

                    $user = $map[$sourceAddress];

                    $appData = $userService->getAppData(end($user), $app);
                    if ($appData)
                        $returnArray[$key]['source_user']['spellsofgenesis'] = $appData->getDisplayRef();
                }

                $destinationAddress = $value['destination'];

                if (isset ($map[$destinationAddress])) {

                    $user = $map[$destinationAddress];
                    $appData = $userService->getAppData(end($user), $app);
                    if ($appData)
                        $returnArray[$key]['destination_user']['spellsofgenesis'] = $appData->getDisplayRef();
                }

            }
        }

        return $returnArray;


    }

    public function getEventsByCollection(AssetCollection $collection, System $sandra, $countOnly = false)
    {

        $eventFactory = new BlockchainEventFactory();


        $blockchainContractFactory = new GenericContractFactory();
        $blockchainContractFactory->setFilter(BlockchainContractFactory::JOIN_COLLECTION, $collection);
        $contracts = $blockchainContractFactory->populateLocal() ?? array();
        $result = array();


        // $eventFactory->setFilter(BlockchainEventFactory::EVENT_CONTRACT,$contracts);


        foreach ($contracts as $contract) {
            $eventFactory = new BlockchainEventFactory();
            $eventFactory->setFilter(BlockchainEventFactory::EVENT_CONTRACT, $contract);
            $result['contract'][$contract->getId()]['count'] = $eventFactory->countEntitiesOnRequest();

        }


        return $result;

        $events = $eventFactory->populateLocal();

        return $result;


    }

    //resort the response if we have multiple transactions
    public function resortArray(array $arrayOfTransactionArrays)
    {

        foreach ($arrayOfTransactionArrays as $transactionArray) {

            usort($transactionArray, function ($a, $b) {
                return $a['timestamp'] <=> $b['timestamp'];
            });

        }

    }


    public function syncOpensea($address = null)
    {


        $myImporter = new EthereumImporter(SandraManager::getSandra());

        $myImporter->getEvents($address, null, 100, 0, $address);
        return;


    }

    public function import(Request $request,$blockchain = null)
    {

        if (!$blockchain) $blockchain = new EthereumBlockchain();

        ini_set('memory_limit', '4G');

        //sleep(5);
        //echo"OK slept in PHP";
        //return ;
        $myImporter = new EthereumImporter(SandraManager::getSandra());
        $entityArray = array();

        $foreignEntityEventsFactory = new ForeignEntityAdapter(null, null, SandraManager::getSandra());
        //print_r($request->all());


        foreach ($request->all() as $key => $value) {

            $txHash = $value[Blockchain::$txidConceptName];

            if (!$value['source'] or !$value['destination'] or !isset ($value['tokenId'])) continue  ;

            $trackedArray[BlockchainImporter::TRACKER_ADDRESSES] = array();
            $trackedArray[BlockchainImporter::TRACKER_ADDRESSES][] = strtolower($value['source']);
            $trackedArray[BlockchainImporter::TRACKER_ADDRESSES][] = strtolower($value['destination']);
            $trackedArray[BlockchainImporter::TRACKER_CONTRACTIDS][] = strtolower($value['contract']);
            $trackedArray[BlockchainEventFactory::EVENT_CONTRACT] = strtolower($value['contract']);
            $trackedArray[BlockchainEventFactory::EVENT_BLOCK_TIME] = $value['blockTime'];

            $trackedArray[BlockchainEventFactory::EVENT_DESTINATION_SIMPLE_VERB] = strtolower($value['destination']);


            $value[BlockchainContractFactory::CONTRACT_STANDARD]['tokenId'] = $value['tokenId'];

            //$trackedArray[BlockchainImporter::TRACKER_BLOCKID][] =  $value['blockIndex'] ;

            $entityArray[] = $entity = new ForeignEntity($txHash, $value + $trackedArray,
                $foreignEntityEventsFactory, $txHash, SandraManager::getSandra());

        }

        $totalResponses['AAAARCHTUNG'] = count($entityArray);

        $foreignEntityEventsFactory->addNewEtities($entityArray, array());


        $structure = $foreignEntityEventsFactory->return2dArray();
        $totalResponses['structure'] = reset($structure);

        print_r($totalResponses);


        $blockFactory = $myImporter->getPopulatedBlockFactory($foreignEntityEventsFactory);
        // die();

        $addressFactory = $myImporter->getPopulatedAddressFactory($foreignEntityEventsFactory);
        $contractFactory = $myImporter->getPopulatedContractFactory($foreignEntityEventsFactory);


        $myImporter->saveEvents($foreignEntityEventsFactory, new $blockchain, $contractFactory,
            $addressFactory, $blockFactory);

        print_r($myImporter->responseArray);


        return 'hello';

    }


    private function buildBalances(BlockchainEventFactory $eventFactory){


        BalanceBuilder::flagAllForValidation(new $eventFactory);
        BalanceBuilder::buildBalance(new $eventFactory);

    }

    public function importEventsByBlockchain(Request $request,$chainName)
    {

        $blockchain = BlockchainRouting::getBlockchainFromName($chainName);

        $this->import($request,$blockchain);






    }

    public function injectEventsFromBlockchain(Blockchain $blockchain,PaginateDatabase $pagination,
                                               BlockchainAddress $addressEntity,$transactionArray)
    {

        $eventFactory = $blockchain->getEventFactory();
        $eventFactory2 = clone($eventFactory);
        $addressBlockchainEntity = $blockchain->getAddressFactory()->get($addressEntity->getAddress());

        $allTransactionsArrayTo = $this->getEventsSegment($eventFactory, $pagination->limit, $pagination->offset,
            $addressEntity, array(BlockchainEventFactory::EVENT_SOURCE_ADDRESS => $addressBlockchainEntity));

        $allTransactionsArrayFrom = $this->getEventsSegment($eventFactory2, $pagination->limit, $pagination->offset,
            $addressEntity, array(BlockchainEventFactory::EVENT_DESTINATION_VERB => $addressBlockchainEntity));

        return array_merge($transactionArray,$allTransactionsArrayTo, $allTransactionsArrayFrom);



    }

    public function getByTx($txString)
    {

        $eventFactory = new GenericEventFactory();
        $eventFactory->populateFromSearchResults($txString,Blockchain::$txidConceptName);
        $events = $eventFactory->getEntities();
        $response = array();

        foreach ($events ?? array() as $event){
            /** @var $event BlockchainEvent  **/

            $response[] = $event->display()->return();

        }

        $responseService = new ResponseService();
        $tx = end($response);

        return $responseService->prepare($tx)->getFormattedResponse();



    }


}
