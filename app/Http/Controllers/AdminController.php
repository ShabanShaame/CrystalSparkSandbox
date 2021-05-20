<?php

namespace App\Http\Controllers;




use App\Services\UserService;
use App\Sog\SogAlexandria;
use App\SystemConfig;
use CsCannon\Asset;
use CsCannon\AssetCollectionFactory;
use CsCannon\AssetFactory;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainAddressFactory;
use CsCannon\Blockchains\BlockchainToken;
use CsCannon\Blockchains\Counterparty\XcpContractFactory;
use CsCannon\SandraManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InnateSkills\LearnFromWeb\LearnFromWeb;

use SandraCore\DatabaseAdapter;
use SandraCore\displayer\AdvancedDisplay;
use SandraCore\EntityFactory;
use Yajra\DataTables\Facades\DataTables;

ini_set('max_execution_time', 600);

class AdminController extends GenericSandraController
{

    private $userService ;

    public function __construct(UserService $userService)
    {

        $this->userService = $userService;

    }

    //

    public function createLearner(){


        $system = app('Sandra')->getSandra();
        $weblearner = new LearnFromWeb($system);

        $url = 'http://sandradev.everdreamsoft.com/activateTrigger.php?trigger=gameCenterApi&action=getEnvironments&responseType=JSON&apik=18a48545-96cd-4e56-96aa-c8fcae302bfd&apiv=3&dev=3';

        $vocabulary = array(
            'envCode' => 'envCode',
            'Title'=> 'Title',
        );

        $learner = $weblearner->createOrUpdate("BooCollectionsLearner",$vocabulary,$url,'Environements','booEnv','BooFile','envCode','envCode');
        $weblearner->learn($learner);

       //dd($learner->factory);
        //we are going to build a learner for each counterparty collections




        echo"Before cycling \n";

       error_reporting(0);

        //die();

        $weblearner = new LearnFromWeb($system);

        $factory = $weblearner->getFactoryFromLearnerName('BooCollectionsLearner');
        $tokenCreated = 0 ;
        $counterpartyTokenFactory = new XcpContractFactory();
        $counterpartyTokenFactory->populateLocal();
       // dd($counterpartyTokenFactory);

        $assetFactory = new AssetFactory(SandraManager::getSandra());
        $assetFactory->populateLocal();
        //$assetFactory->getRefMap('assetId');

        //dd($assetFactory);

        $collectionFactory = new  AssetCollectionFactory(SandraManager::getSandra());
        $collectionFactory->populateLocal();
        //we build the collection list
        foreach ($factory->entityArray as $booCollection){

            $envCode = $booCollection->get('envCode');
           $collectionEntity = $collectionFactory->first('collectionId',$envCode);

           if(is_null($collectionEntity)){

               $data['title'] = $booCollection->get('Title');
               $data['masterCurrency'] = $booCollection->get('MasterCurrency');
               $data['symbol'] = $booCollection->get('ticker');

               $data['name'] = $booCollection->get('Title');
               $data['symbol'] = $booCollection->get('ticker');
               $data['bundleId'] = $booCollection->get('bundleId');
               $data['imageUrl'] = $booCollection->get('bannerImage');
               $data['description'] = $booCollection->get('description');
               $data['wideIcon'] = $booCollection->get('wideIcon');
               $data['wideIcon'] = $booCollection->get('wideIcon');

               $data['collectionId'] = $booCollection->get('envCode');

               $links['hasSource'] = 'BookOfOrbs';

               //dd($data);

               $collectionFactory->createNew($data,$links);

           }

        }



        foreach ($factory->entityArray as $collectionEntity) {


            $collectionCode = $collectionEntity->get('envCode');

            $vocabulary = array(
                'image' => 'image',
                'assetName'=> 'assetName',
                'id'=> 'id',
                'Divisible'=> 'divisible',
            );


            $counterpartyLearnerUrl = "http://sandradev.everdreamsoft.com/activateTrigger.php?trigger=gameCenterApi&action=getEnvironment&env=$collectionCode&responseType=JSON&apik=18a48545-96cd-4e56-96aa-c8fcae302bfd&apiv=3&dev=3";
            echo"creating learner BooLearner_".$collectionCode."\n";



            $learner = $weblearner->createOrUpdate("BooLearner_".$collectionCode,$vocabulary, $counterpartyLearnerUrl, 'Environements/$first/Assets', "booCollectionItem_$collectionCode", "BooCollectionFile_$collectionCode", 'assetName', 'assetName');
            $learner->factory->className = 'CsCannon\Asset' ;
            $myCollection = $weblearner->learn($learner,'CsCannon\Asset');
            //$myCollection = $weblearner->learn($learner,'App\Asset');


            $collectionFactory = new AssetCollectionFactory(SandraManager::getSandra());
            $collectionFactory->populateLocal();

            //we need to create tokens

            foreach ($myCollection->entityArray as $entityAsset){

                $tokenCreated++ ;
                $assetName = $entityAsset->get('assetName');

                echo" - \n ".  $entityAsset->get('assetName');

                if (!$assetName) continue ;



               // $collectionEntity = $collectionFactory->getOrCreateFromRef('collectionId',$collectionCode);
                $collectionEntity = $collectionFactory->first('collectionId',$collectionCode);

                if (is_null($collectionEntity)){

                    dd("error unexisting $collectionCode");


                }


                if(isset($tokenIndex[$assetName])){
                    $entityToken = $tokenIndex[$assetName] ;
                }
                else {
                    $entityToken = $counterpartyTokenFactory->getOrCreateFromRef('tokenId', $assetName);
                }
               //does the asset exists ?
               $currentAsset =  $assetFactory->first('assetId',"$collectionCode-$assetName");
              // $entityAsset->createOrUpdateRef('assetIdx',"$collectionCode-$assetName");
               if (!$currentAsset) {
                   echo "creating new asset  $collectionCode-$assetName \n";


                   //$dataArray['name']

                   $currentAsset = $assetFactory->createNew($entityAsset->entityRefs, array(AssetFactory::$collectionJoinVerb => $collectionEntity));
                   $currentAsset->createOrUpdateRef('assetId',"$collectionCode-$assetName");
                   $currentAsset->dumpMeta();
               }
                if($entityAsset instanceof Asset) {
                    $currentAsset->bindToToken($entityToken);


                }
                if($entityToken instanceof BlockchainToken && $currentAsset instanceof Asset) {
                    $entityToken->bindToAsset($currentAsset);
                    echo $entityToken->entityId." token binded $collectionCode-$assetName to asset $currentAsset->entityId  \n";

                }

                $tokenIndex[$assetName] = $entityToken;


            }


        }
        dd("created".$tokenCreated);
        dd($counterpartyTokenFactory->return2dArray());




    }

    public function getActiveBlockchains()
    {

        $config = new SystemConfig();
        $activeBlockchains = $config->getActiveBlockchains();
        foreach ($activeBlockchains as $blockchain){

           $response[$blockchain->get('blockchain')] = $blockchain->dumpMeta();
        }

        return  $response;


    }

    public function setActiveBlockchain($blockchainName)
    {

       $blockchain =  BlockchainRouting::getBlockchainFromName($blockchainName);

       if (is_null($blockchain)) $this->returnNotFound();

       $config = new SystemConfig();
       $config->toggleActiveBlockchain($blockchainName,true);


    }

    public function buildCollectionLearners()
    {



        $sandra = app('Sandra')->getSandra();

        $weblearner = new LearnFromWeb($sandra);

        $url = 'http://sandradev.everdreamsoft.com/activateTrigger.php?trigger=gameCenterApi&action=getEnvironments&responseType=JSON&apik=18a48545-96cd-4e56-96aa-c8fcae302bfd&apiv=3&dev=3';

        $vocabulary = array(
            'envCode' => 'envCode',
            'Title'=> 'Title',
        );

        $weblearner->createOrUpdate("myLearner",$vocabulary,$url,'Environements','booEnv','BooFile','envCode','envCode');
//print_r($weblearner->test());

        $vocabulary = array(

            "nft_version"=>"nftVersion",
            "external_link"=>"link",
            "short_description"=>"shortDescription",
            "address"=>"ethContractAddress",
            "image_url"=>"openseaImage",

            "link"=>"link",
            "name"=>"name",



        );

      //  $url = 'https://api.opensea.io/api/v1/asset_contracts/';

        /*$learner = $weblearner->createOrUpdate("openSeaLearner",
            $vocabulary,$url,
            '',
            'ethCollection',
            'ethCollectionFile',
            'ethContractAddress','address');

$weblearner->learn($learner);*/







    }

    public function buildShadowEra(){

        WavesSynchronizationService::buildShadowEra();

    }

    public function getCollections(){


        $allCollections = new AssetCollectionFactory(SandraManager::getSandra());
       $allCollections->populateLocal();


        $advancedDisplayer = new AdvancedDisplay();
        $advancedDisplayer->setShowUnid(true);


        return $allCollections->getDisplay('json',null,null,$advancedDisplayer) ;




    }

    public function setCasa(){


        $request = Request::capture();
        $requestArray =$request->toArray();
        $addressEntityList = array();
        $sandra =  SandraManager::getSandra();
        $sc = $sandra->systemConcept;
        $chainAddress = array();

        foreach ($requestArray as $chain => $address){
            $blockchain = BlockchainRouting::getBlockchainFromName($chain);
            if (!$blockchain) continue ;
           $chainAddress[$chain]['blockchain'] = $blockchain ;
           $chainAddress[$chain]['address'] = $address;
           $addressEntityList[] =$address ;

        }
        if (count($addressEntityList)< 2) return ["error"=>"non full casa","address"=>$addressEntityList];

        $addressConcepts = DatabaseAdapter::searchConcept($addressEntityList,null,
            SandraManager::getSandra(),null,$sc->get(BlockchainAddressFactory::$file));

        if (!$addressConcepts) {
            //no u8ser found
            $user = null ;
            foreach ($chainAddress as $addressData) {

                $blockchain = $addressData['blockchain'];
                $address = $addressData['address'];
                /** @var Blockchain $blockchain */

                $addressEntity = $blockchain->getAddressFactory()->get($address, true);
                $addressData['entity'] = $addressEntity;

                //we take the last address
                if(!$user) {
                    $user = $this->userService->createFromAddress($addressData['entity']);
                }
                else {
                    $user->assignAddress($addressEntity);
                    }

        }
            $user->createOrUpdateRef("casaFull",count($addressEntityList));
            return ["created"=>$user->subjectConcept->idConcept];


        }
        //ok at least one address exist
        $last = end($chainAddress);
        //we take the last address entity
        /** @var Blockchain $blockchain */
       $blockchain = $last['blockchain'];
       $user = null ;
       $addressEntity = $blockchain->getAddressFactory()->get($last['address'],true);
       //we look if we find a user already
        if($addressEntity)
       $user = $this->userService->getUserFromAddress($addressEntity);
       //Ok user has all address registered already
        if ($user && $user->get("casaFull") == 2){
            //Ok user has all address registered already
            return ["message"=>"user has casafull already"];

        }
        $chainAddressWLast = $chainAddress;
        array_pop($chainAddressWLast);

        //here we might not have users or he doesn't have casa full
       if (!$user){
           $userFound = null ;
           foreach ($chainAddressWLast as $addressArray)
           {

               $blockchain = $addressArray['blockchain'];
               $address = $addressArray['address'];
               /** @var Blockchain $blockchain */

               $addressEntity = $blockchain->getAddressFactory()->get($address, true);
               $userFound = $this->userService->getUserFromAddress($addressEntity);

           }
           if($userFound) $user = $userFound ;
           else{
               $user = $this->userService->createFromAddress($addressEntity);

           }

       }

       //now that we have our user we bind
        foreach ($chainAddress as $addressData) {

            $blockchain = $addressData['blockchain'];
            $address = $addressData['address'];
            /** @var Blockchain $blockchain */

            $addressEntity = $blockchain->getAddressFactory()->get($address, true);
            $addressData['entity'] = $addressEntity;

            $user->assignAddress($addressEntity);


        }
        $user->createOrUpdateRef("casaFull",count($addressEntityList));


        return ["message"=>"Casa Bound"] ;

        //$this->userService->getUserFromAddress()


    }

    public function viewTable(Request $request,$view){

        $table = $view;

        $env = $this->envSwitcher($request);



        return view('datatableView.datatable_view', [
            'refMap'    => DB::getSchemaBuilder()->getColumnListing(TableViewController::getTableName($table,$env)),
            'table'     => $table
        ]);

    }

    /**
     * return Json for client side DataTables
     *
     * @param Request $request
     * @param String $tableName
     * @return array
     */
    public function dbView(Request $request, string $tableName){


         $this->envSwitcher($request);

            $myDatas = TableViewController::get($tableName)->pluck();

            if(!$myDatas){
                return [];
            }

            $response['recordsTotal'] = count($myDatas);
            $response['data'] = $myDatas;

            return $response;



    }

    /**
     * Ajax from DataTables with db view
     *
     * @param Request $request
     * @param String $table
     * @return DataTables
     * @throws \Exception
     */
    public function tableAjax(Request $request, string $table)
    {
        $this->envSwitcher($request);


        if (substr( $table, 0, 4 ) === "sog-"){
            return SogAlexandria::getBinds($table);

        }

        $datas = TableViewController::get($table);


        return DataTables::of($datas)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Ajax from DataTables with db view
     *
     * @param String $table
     * @return DataTables
     */
    public function getHeaders(Request $request,string $table)
    {

       $env =  $this->envSwitcher($request);

        if (substr( $table, 0, 4 ) === "sog-"){
            return SogAlexandria::getBindHeader();

        }

        $tableName = TableViewController::getTableName($table,$env);
        //dd($tableName);
        return Schema::getColumnListing($tableName);


    }


    public function countDbView(Request $request,$tableName){

        return $this->countDatas($tableName);


    }

    /**
     * count number of rows on db table
     *
     * @param String $table
     * @return Int|false
     */
    public function countDatas(Request $request,string $table)
    {
        return TableViewController::countTable($table);
    }

    public function envSwitcher(Request $request){

        if ($request->get('env')){

            $envController = new EnvController();
            $sandra = $envController->enterEnv($request->get('env'));
            return $request->get('env');
        }

        return null ;


    }





}
