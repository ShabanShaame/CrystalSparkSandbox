<?php

namespace App\Http\Controllers;



/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 26.03.19
 * Time: 10:27
 */




use App\BlockchainRouting;

use CsCannon\Asset;
use CsCannon\Blockchains\BlockchainAddressFactory;
use CsCannon\Blockchains\BlockchainContract;

use App\ResponseService;
use App\Sog\SogService;
use App\SystemConfig;
use App\XcpAddress;

use CsCannon\AssetCollection;
use CsCannon\AssetCollectionFactory;
use CsCannon\AssetFactory;
use CsCannon\AssetSolvers\PathPredictableSolver;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\BlockchainEvent;
use CsCannon\Blockchains\BlockchainEventFactory;
use CsCannon\Blockchains\Ethereum\EthereumEventFactory;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC721;
use CsCannon\Blockchains\Generic\GenericAddressFactory;
use CsCannon\Blockchains\Generic\GenericContractFactory;
use CsCannon\Blockchains\Substrate\Kusama\KusamaAddressFactory;
use CsCannon\MetadataProbeFactory;
use CsCannon\SandraManager;
use Ethereum\Sandra\EthereumContractFactory;
use SandraCore\displayer\AdvancedDisplay;
use SandraCore\Entity;
use SandraCore\EntityFactory;
use SandraCore\System;

class CollectionController extends Controller
{

    const STICK_VERB = 'stickTo';
    const STICK_FILE = 'stickerFile';
    const STICK_ISA = 'sticker';
    const STICK_ID = 'id';
    const STICKER_DEFAULT_ID = 'featured';

    public function getSingle($identifier)
    {

        $sandra = app('Sandra')->getSandra();

        if (substr($identifier, 0, 2) === "0x") {
            //   return response()->json(erc721FromOpenSea($identifier), 200);

        }

        $collectionFactory = new AssetCollectionFactory($sandra);
        $collectionFactory->populateLocal();
        $collectionEntity = $collectionFactory->get($identifier);

        /** @var AssetCollection $collectionEntity */

        $assetFactory = new AssetFactory();
        $contractFactory = new GenericContractFactory();
        $assetFactory->joinFactory(AssetFactory::$tokenJoinVerb, $contractFactory);

        $assetFactory->setFilter(AssetFactory::$collectionJoinVerb, $collectionEntity);
        $assetFactory->populateLocal(200);
        $assetFactory->joinPopulate();


        $assetVocabulary = array('imgURL' => 'imageUrl',
        AssetFactory::ID => AssetFactory::ID,
            AssetFactory::ASSET_NAME => AssetFactory::ASSET_NAME,


        );
        $todisplay = array_keys($assetVocabulary);
        $response['collection'] = $collectionEntity->getDefaultDisplay();

        foreach ($collectionEntity->getOwners() ?? array() as $owner){
            $response['collection']['owner'][] = $owner->getAddress();
        }

        $response['assets'] = $assetFactory->getDisplay('array', $todisplay, $assetVocabulary);

        $assets = $assetFactory->getEntities();
        if (empty($assets)) {
            $solvers = $collectionEntity->getSolvers();
            $solver = end($solvers);
            if ($solver instanceof PathPredictableSolver) {

               // $shouldResolveSampleAssets = true;
                //$standards = $collectionEntity->getStoredSamples();
                //$response['assets'] = $this->loadSampleOrbs($standards, $collectionEntity);
                $response['assets'] = $this->getFrontSamples($collectionEntity);
            }
        }

        foreach ($assetFactory->getEntities() as $asset) {

            /**@var Asset $asset */
            $assetData = $asset->getContracts();


            foreach ($assetData ? $assetData : array() as $contract) {
                /**@var BlockchainContract $contract */

                $response['assets'][$asset->subjectConcept->idConcept]['contracts'][] = $contract->display($contract->getStandard())->return();

            }


        }
        // $response['assetsM'] = $assetData ;

        //dd($response);


        $responseManager = new ResponseService();


        return $responseManager->prepare($response)->getFormattedResponse();

        // return response()->json($response, 200);


    }


    public function getAll()
    {


//convert ethCollection vocabular
        $booCollectionVocabulary = array('bannerImage' => 'image',
            'collectionId' => 'id',
            'openseaImage' => 'image',
            'creationTimestamp' => 'creationTimestamp',
            'Title' => 'name',
            'description' => 'description',
            'name' => 'name',
            'envCode' => 'identifier',
            'maxSupply' => 'maxSupply',
            'externalLink' => 'link',
            'website' => 'link',
            'address' => 'contract',
            'creationBlock' => 'creationBlock',



        );

        $allCollections = new AssetCollectionFactory(SandraManager::getSandra());


        if (request()->owner) {

           $filterOwner =  GenericAddressFactory::getAddress(request()->owner);
            //hack for antoine
           //$filterOwner2 = GenericAddressFactory::getAddress('5CwdLCoW7N3Lhnx1axryueg2SdoSJoKTvUorvNuiBPXfEpGQ');



            if ($filterOwner && !$filterOwner->isForeign()) $allCollections->setFilter(AssetCollectionFactory::COLLECTION_OWNER,$filterOwner);
            if ($filterOwner->isForeign() or request()->owner == '') {$allCollections->setFilter('always','null');}


        }

        $allCollections->populateLocal();



        $keepList = array();

        //hack to remove non active open sea collections ------<
        $ethereumCollectionToKeep = ContractController::getTrackedContracts();
        foreach ($ethereumCollectionToKeep as $contract) {

            $keepList[$contract->getId()] = $contract;
        }

        foreach ($allCollections->getEntities() as $collection) {

            $owners = $collection->getOwners();
            $collectionSubject = $collection->subjectConcept->idConcept;
            $entityCollection = $collection->entityId;
            /** @var AssetCollection $collection */

            //dd($collection->dumpMeta());



            if (substr($collection->getId(), 0, 2) === "0x" //we keep all collections for now
                && !isset($keepList[$collection->getId()]) && $collection->getId() !== '0xuniverse') {

                unset($allCollections->entityArray[$collectionSubject]);
                unset($collection);
            }



            foreach ($owners ?? [] as $owner) {
                $ownerArray[$collection->getId()][] = $owner->getAddress();
                }

            //hack to remove non active open sea collections ------>


        }


        $todisplay = array_keys($booCollectionVocabulary);



        $responseManager = new ResponseService();
        $displayArray = $allCollections->getDisplay('array', $todisplay, $booCollectionVocabulary);


        if (request()->sortBy){
            if (request()->orderBy == 'desc') {
                $displayArray = collect($displayArray)->sortByDesc(request()->sortBy)->toArray();
            }
            else{
                $displayArray = collect($displayArray)->sortBy(request()->sortBy)->toArray();
            }

        }
        else {

            $displayArray = collect($displayArray)->sortBy('name')->toArray();
        }




        $final = array();


        foreach ($displayArray as $displayable){

            $collectionId =  $displayable['id'] ?? 'null' ;


            if (isset($ownerArray[$collectionId])){

                foreach ($ownerArray[$collectionId] as $owner){
                    $displayable['owner'] = $owner ;
                }

            }


            $final[] = $displayable;

        }


        return $responseManager->prepare($final)->getFormattedResponse();



    }

    public function analyse(){

        $sandra = app('Sandra')->getSandra();

        if (substr($identifier, 0, 2) === "0x") {
            //   return response()->json(erc721FromOpenSea($identifier), 200);

        }

        $collectionFactory = new AssetCollectionFactory($sandra);
        $collectionFactory->populateLocal();




    }

    public function loadSampleOrbs($standards, AssetCollection $collection)
    {

        $contractFactory = new GenericContractFactory();
        $contractFactory->setFilter(BlockchainContractFactory::JOIN_COLLECTION, $collection);
        $contracts = $contractFactory->populateLocal();
        $contract = end($contracts);
        $assets = array();
        $assetsFull = array();
        $response = array();

        foreach ($standards ? $standards : array() as $standard) {

            $assetsFull[] = PathPredictableSolver::resolveAsset($collection, $standard, $contract);

        }

        foreach ($assetsFull as $assets) {
            foreach ($assets as $asset) {
                /** @var Asset $asset */

                $thisAsset = $asset->display()->return();
                $thisAsset['contract'] = $contract->display($contract->getStandard())->return();

                $response[] = $thisAsset;
            }
        }

        return $response;


    }

    public function getCarousels($stickerName = self::STICKER_DEFAULT_ID)
    {


        $sandra = app('Sandra')->getSandra();
        //$sandra = new SandraCore\System(null,true);


        $booCollectionVocabulary = array('bannerImage' => 'image',
            'openseaImage' => 'image',
            'Title' => 'name',
            'description' => 'description',
            'name' => 'name',
            'envCode' => 'identifier',

            'externalLink' => 'link',
            'website' => 'link',
            'address' => 'contract',


        );


        $allCollections = new AssetCollectionFactory($sandra);
        $sticker = $this->getOrCreateSticker($stickerName);
        $allCollections->setFilter(self::STICK_VERB, $sticker);
        $allCollections->populateLocal();

        //dd($allCollections->return2dArray());


        $todisplay = array_keys($booCollectionVocabulary);

        //$factory->getDisplay('array',$todisplay,$booCollectionVocabulary);
        //$factory->displayer->addFactory($factoryEth);
        $displayer = new AdvancedDisplay();
        $displayer->conceptDisplayProperty('imageUrl', 'imagePath');

        if (count($allCollections->getEntities()) < 1) return array();


        return response()->json($allCollections->getDisplay('array', $todisplay, $booCollectionVocabulary, $displayer), 200);


    }

    public function stickCollection($collectionId, $stickerName)
    {

        $sandra = app('Sandra')->getSandra();

        $collection = $this->getCollectionEntity($collectionId);

        $stickerEntity = $this->getOrCreateSticker($stickerName);


        $collection->setBrotherEntity(self::STICK_VERB, $stickerEntity, null);


    }

    private function getCollectionEntity($collectionId): AssetCollection
    {

        $sandra = app('Sandra')->getSandra();

        $collectionFactory = new AssetCollectionFactory($sandra);
        $collectionFactory->populateLocal();


        $collectionEntity = $collectionFactory->get($collectionId);

        return $collectionEntity;


    }

    private function getOrCreateSticker($stickerName): Entity
    {

        $sandra = app('Sandra')->getSandra();

        $stickerFactory = new EntityFactory(self::STICK_ISA, self::STICK_FILE, $sandra);
        $stickerEntity = $stickerFactory->getOrCreateFromRef(self::STICK_ID, $stickerName);

        return $stickerEntity;


    }

    private function getFrontSamples(AssetCollection $collectionEntity)
    {

        //IF path predicatable we get last transactions orbs
        $maxCount = 20 ;

        $blockchainContractFactory = new GenericContractFactory();
        $blockchainContractFactory->setFilter(BlockchainContractFactory::JOIN_COLLECTION,$collectionEntity);
        $contracts = $blockchainContractFactory->populateLocal(); //TODO is joining only one contract per collection

        // dd($collectionEntity->dumpMeta());

        //$filter = [BlockchainEventFactory::EVENT_CONTRACT=>$contracts];

        $transactionController = new TransactionsController();
      // $transactions = $transactionController->getEvents(null,$collectionName);
       $blockchainEventFactory = new EthereumEventFactory();
        $blockchainEventFactory->setFilter(BlockchainEventFactory::EVENT_CONTRACT,$contracts);
        $blockchainEventFactory->populateLocal(150);
        $events = $blockchainEventFactory->getEntities();

       $returnArray = $blockchainEventFactory->display()->return();

       $soloContract = end($contracts);
       /**@var \Ethereum\Sandra\EthereumContract $soloContract */
        $count = 0 ;
       foreach ($returnArray ? $returnArray : array() as $event){
           $count++;

           $erc721 = ERC721::init(12);

           $display = end($event['orbs']);
           $display['contract'] = $soloContract->display($erc721)->return();
           $asset[] = $display ;

           if ($count>=$maxCount) break ;


       }

        return $asset ;

    }

    public function countTransactions($collectionId = null){

        $sandra = app('Sandra')->getSandra();

        $collectionFactory = new AssetCollectionFactory($sandra);
        $collectionFactory->populateLocal();
        $collections = $collectionFactory->getEntities();

        if ($collectionId){
            $collectionEntity = $collectionFactory->get($collectionId);
            $collections = array($collectionEntity);

        }




          foreach ($collections ?? array() as $collectionEntity){



           $transactionController = new TransactionsController();
          $events[$collectionEntity->name] = $transactionController->getEventsByCollection($collectionEntity,$sandra,true);


       }


        return $events ;





    }

    public function probeErc721($collectionString,$contractString,$tokenId){

        $sandra = SandraManager::getSandra();


        $probFactory = new MetadataProbeFactory();
        $probFactory->populateLocal();
        $assetCollectionFactory = new AssetCollectionFactory($sandra);

        $collection =  $assetCollectionFactory->get($collectionString);
        $contract = EthereumContractFactory::getContract($contractString);

        if (!$contract) return ['message'=>'contract not found','probe'=>"$collection - $contractString $collection"];
        if (!$collection) return ['message'=>'collection not found','probe'=>"$collection - $contractString $collection"];


        $probe = $probFactory->get($collection,$contract);

        if (!$probe) return ['message'=>'probe not found','probe'=>"$collection - $contractString $collection"];

        $probe->probe(ERC721::init($tokenId));

        return ['message'=>'probed token id : $tokenId'];



    }

    public function getStats($identifier){

        $sandra = app('Sandra')->getSandra();

        $collectionFactory = new AssetCollectionFactory($sandra);
        $collectionFactory->populateLocal();
        $responseManager = new ResponseService();

        $collectionEntity = $collectionFactory->get($identifier);
        /** @var AssetCollection $collectionEntity */

        //add 404 if collection not found
        if (!$collectionEntity) {
            $responseManager->prepareError(404, 'collection not found');
            return $responseManager->getFormattedResponse() ;
        }

        $blockchainEventFactory = new BlockchainEventFactory();

        $blockchainContractFactory = new GenericContractFactory();
        $blockchainContractFactory->setFilter(BlockchainContractFactory::JOIN_COLLECTION, $collectionEntity);
        $contracts = $blockchainContractFactory->populateLocal();

        // dd($collectionEntity->dumpMeta());


        $blockchainEventFactory->setFilter(BlockchainEventFactory::EVENT_CONTRACT,$contracts);
        //mint
        $blockchainEventFactory->setFilter(BlockchainEventFactory::EVENT_SOURCE_ADDRESS,
            GenericAddressFactory::getAddress(BlockchainAddressFactory::NULL_ADDRESS));

        $count = $blockchainEventFactory->countEntitiesOnRequest();


        $response['total']['raw']['mint'] = $count ;
        $response['total']['valid']['mint'] = $count ;


        return $responseManager->prepare($response)->getFormattedResponse();



    }

}
