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
use App\ResponseService;
use App\Sog\SogService;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainAddressFactory;
use CsCannon\Blockchains\BlockchainBlockFactory;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Counterparty\XcpBlockchain;
use CsCannon\Blockchains\Ethereum\DataSource\OpenSeaImporter;
use CsCannon\Blockchains\Generic\GenericAddressFactory;
use CsCannon\Blockchains\Generic\GenericBlockchain;
use CsCannon\SandraManager;
use Ethereum\Sandra\EthereumContractFactory;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Pelieth\LaravelEcrecover\EthSigRecover;
use SandraCore\DatabaseAdapter;
use SandraCore\displayer\AdvancedDisplay;
use SandraCore\Entity;
use SandraCore\Reference;
use SandraCore\System;

class UserService
{

    private $userFactory ;
    const USERNAME = 'username' ;
    const IDENTIFIER = 'identifier' ;
    public $mixpanelService;
    public function __construct($productionEnv=true)
    {

        $token = 'fa04326728f7e495eaba75c7c5dde93d'; // prod token //Crendentials
        $this->userFactory = new UserFactory();
        if (!$productionEnv){
            $token = '927058d22c46eae011ff971523c09b84';
        }

        $this->mixpanelService = \Mixpanel::getInstance($token);


    }

    /**
     * @param BlockchainAddress $address
     * @param  $autoCreate
     * @return UserEntity
     */
    public function getUserFromAddress(BlockchainAddress $address,$autoCreate = true){

        $this->userFactory->setFilter(UserFactory::AUTHENTICATE_USER_VERB,$address);
       $this->userFactory->populateLocal();
       $allUsers =  $this->userFactory->getEntities();
       $first = reset($allUsers);

       if ((is_null($first) or !$first)&&$autoCreate) $first = $this->createFromAddress($address);


       return $first ;


    }


    public function getUserFromJWT($jwt){

        $request = Request::capture();
        if (! $decoded = JWTService::verifyJwt($jwt,$request->ip())) return false ;

        $addressFactory = new GenericAddressFactory();
        $address = $addressFactory->get($decoded->addr);

       $user = $this->getUserFromAddress($address);

       return $user ;


    }



    public function bootstrap(BlockchainAddress $address){

       $user = $this->userFactory->getOrCreateFromRef(static::USERNAME,'edsAdmin');
       $user = $this->userFactory->getOrCreateFromRef(static::USERNAME,'edsAdmin');

       $roleFactory = new csRoleFactory();
       $admin = $roleFactory->getOrCreateFromRef(CsRole::NAME,'admin');
       $userRole = $roleFactory->getOrCreateFromRef(CsRole::NAME,'user');

       $user->setRole($admin);
       $user->assignAddress($address);


    }

    public function setUseApp(UserEntity $user, AppEntity $app, $appData,$autocommit = true){

        return $user->setBrotherEntity('appData',$app,$appData,$autocommit);



    }

    /**
     * @param UserEntity $user
     * @param AppEntity $app
     * @return Entity|null
     */
    public function getAppData(UserEntity $user, AppEntity $app){
        $userFactory = $user->factory ;
        /** @var UserFactory $userFactory */
        $userFactory->populateBrotherEntities('appData',$app);
        $brother = $user->getBrotherEntity('appData',$app);
        return $brother ;


    }


    public function createFromAddress(BlockchainAddress $address, $autocommit = true):UserEntity{

        $user = $this->userFactory->getOrCreateFromRef(static::IDENTIFIER,$address->getAddress());


        $roleFactory = new csRoleFactory();
        $visitor = $roleFactory->getOrCreateFromRef(CsRole::NAME,'visitor');


        $user->setRole($visitor);
        $user->assignAddress($address);

        return $user ;


    }

    public function getUsers(){
        $app = ['spellsofgenesis'];
        $sogUnid = $this->userFactory->system->systemConcept->get('spellsofgenesis');



       $this->userFactory->populateLocal();
        $this->userFactory->populateBrotherEntities('appData');
        $appDataUnid = $this->userFactory->system->systemConcept->get('appData');


       $advancedDisplayer = new AdvancedDisplay();
        $advancedDisplayer->setShowUnid(true);
        $return = array();
        $tempData = array();
        $temp = array();

        foreach ($this->userFactory->getDisplay('json',null,null,$advancedDisplayer) ?? array() as $key => $user){

            $unid = $user['unid'];
            if (!isset($this->userFactory->brotherEntitiesArray[$unid][$appDataUnid])) continue ;
            $appData = $this->userFactory->brotherEntitiesArray[$unid][$appDataUnid];
            foreach ($appData ?? array() as $appUnid => $appValue) {
                $targetAppData = $appValue;

                $returnData = array();

                foreach ($targetAppData->entityRefs ?? array() as $ref) {
                    /** @var  Reference $ref */
                    $returnData[$ref->refConcept->getShortname()] = $ref->refValue;
                }
                $user['appData'][$appUnid] = $returnData;

                //SOG quick hack
                if (2046665==$appUnid) {
                    $tempData = $returnData;
                    echo"sogUserFound";
                    $temp[$key] = $tempData ;
                }



            }

            $return[$key] =  $user ;


        }


       return $return ;


    }




    public function getPublicUserInfo($identifier){

        //dirty hack for lowercases address
        if (substr( $identifier, 0, 2 ) === "0x"){
            $identifier = strtolower($identifier);

        }

        $blockcchain = new GenericBlockchain();
        $identifierAddressEntity = $blockcchain->getAddressFactory()->get($identifier);

        $return['user']['known'] = false ;
        if (!is_numeric($identifierAddressEntity->entityId)) return $return ;

       $this->userFactory->setFilter(UserFactory::AUTHENTICATE_USER_VERB,$identifierAddressEntity);
       $this->userFactory->populateLocal();
       $this->userFactory->joinFactory(UserFactory::AUTHENTICATE_USER_VERB,$blockcchain->getAddressFactory());
       $this->userFactory->joinPopulate();
       $users = $this->userFactory->getEntities();
       $user = end($users);
        $roleFactory = new csRoleFactory();



            if ($this->isBetaAllowed($identifierAddressEntity)) {
                $return['user']['role'] = 'beta';
                $beta = $roleFactory->getOrCreateFromRef(CsRole::NAME, 'beta');
                if(!$user) {

                    $user = $this->userFactory->getOrCreateFromRef(static::IDENTIFIER, $identifierAddressEntity->getAddress());
                    $user->assignAddress($identifierAddressEntity);
                    $user->setRole($beta);
                }
                else if (!$user->getRole() or $user->getRole()->getName()=='visitor'){
                    $user->setRole($beta);

                }
            }




       if (!$user) return $return ;



           $return['user']['known'] = true ;
       $addresses = $user->getJoinedEntities(UserFactory::AUTHENTICATE_USER_VERB);



       foreach ($addresses ?? array() as $address){

           //die("here");

           $addressBlockchains = BlockchainRouting::getBlockchainFromGenericAddress($address);
           $addressBlockchain = reset($addressBlockchains);

           $blockchainName = $addressBlockchain::NAME ;
           if ($address->hasVerbAndTarget(FirstOasisService::FO_ADDRESS_VERB,FirstOasisService::FO_ADDRESS_TARGET)) $blockchainName = 'firstOasisKlaytn';

           $entityAddress['blockchain'] = $blockchainName ;
           $entityAddress['address'] = $address->getAddress() ;
           $return['addresses'][] = $entityAddress ;


       }



        return $return;


    }

    public function isBetaAllowed(BlockchainAddress $address){

        return false ;

        $eth = new EthereumContractFactory();
        $contract = $eth->get(SogService::SOG_CONTRACT_ID_ASKIAN);
        $address->setDataSource(new OpenSeaImporter());

        $balance = $address->getBalanceForContract([$contract]);
        $lowercase = strtolower(SogService::SOG_CONTRACT_ID_ASKIAN);

       // dd($balance->contracts['ethereum']);

        if (isset ($balance->contracts['ethereum'][$lowercase]) && count($balance->contracts['ethereum'][$lowercase]) > 0)
            return true ;

        return false ;



    }


    public function batchUserData($addressToFind,$userData,AppEntity $app){

        //address are not user yet
        $addresses = DatabaseAdapter::searchConcept($addressToFind,null,SandraManager::getSandra(),
            0,BlockchainAddressFactory::$file);

        $genericBlockchain = new GenericBlockchain();
        $addressFactory = $genericBlockchain->getAddressFactory();
        $addressFactory->conceptArray = $addresses;
        $addressFactory->populateLocal();

        $autocommit = true ;




        $this->mixpanelService = \Mixpanel::getInstance('fa04326728f7e495eaba75c7c5dde93d');


        $this->userFactory->setFilter(UserFactory::AUTHENTICATE_USER_VERB,$addresses);
        $this->userFactory->populateLocal();
        $this->userFactory->populateBrotherEntities(UserFactory::AUTHENTICATE_USER_VERB);
        $users = $this->userFactory->getEntities();

        try {

            foreach ($addressToFind as $address) {
                $user = null;


                $addressEntity = $addressFactory->get($address);
                //do we have an existing user ?
                // the address entity doens't exist
                if (!is_numeric($addressEntity->entityId)){
                    continue ;

                }


                $users = $this->userFactory->getEntitiesWithBrother(UserFactory::AUTHENTICATE_USER_VERB, $addressEntity);
                if (is_array($users))
                    $user = end($users);
                /** @var UserEntity $user */
                $data = $userData[$addressEntity->getAddress()];

                if (!$user) $user = $this->createFromAddress($addressEntity, $autocommit);

                if ($user) {

                    //hack sog
                    $username = $data['username'] ;


                       $chains = BlockchainRouting::getBlockchainsFromAddress($addressEntity->getAddress());
                    $chainKey = null ;

                    foreach ($chains as $chain){

                        if ($chain instanceof XcpBlockchain) {$chainKey = 'XCPKey' ; break;}
                        if ($chain instanceof XcpBlockchain) {$chainKey = 'ethKey' ; break;}

                    }

                    $this->setUseApp($user, $app, $data);
                   // $this->mixpanelService->identify($addressEntity->getAddress());
                   // $this->mixpanelService->track("Orb Explorer binding wallet",array("address"=>$addressEntity->getAddress(),'$ignore_time'=>true,'ip'=>null));
                    $this->mixpanelService->people->set($addressEntity->getAddress(),array('Orb Explorer Bound'=>date('c', time())),0,true);
                    $this->mixpanelService->people->setOnce($addressEntity->getAddress(),array($chainKey=>$addressEntity->getAddress()),0,true);
                    $this->mixpanelService->people->set($addressEntity->getAddress(),array('Orb Explorer sog_username'=>$username),0,true);



                }



            }
        }catch (\Exception $e){

            echo"exeption";

        }

        return "ok";



    }











}
