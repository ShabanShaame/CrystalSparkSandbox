<?php

namespace App\Http\Controllers;

use App\ResponseService;
use App\Services\AppService;
use App\Services\JWTService;
use App\Services\RewardService;
use App\Services\UserEntity;
use App\Services\UserFactory;
use App\Services\UserService;
use App\Sog\SogGameDataService;
use App\Sog\SogService;
use CsCannon\BlockchainRouting;

use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\Counterparty\XcpAddressFactory;
use CsCannon\Blockchains\Ethereum\EthereumAddressFactory;
use CsCannon\Blockchains\Generic\GenericAddressFactory;
use CsCannon\Blockchains\Generic\GenericBlockchain;
use CsCannon\SandraManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Mixpanel;
use Pelieth\LaravelEcrecover\EthSigRecover;
use SandraCore\DatabaseAdapter;
use SandraCore\ForeignEntityAdapter;
use SandraCore\Reference;


class UserController extends Controller
{

    private $userService ;
    private $responseService ;
    private $mixpanelService ;

    public function __construct(UserService $userService, ResponseService $responseService)
    {
        $this->userService = $userService;
        $this->responseService = $responseService;

        $this->mixpanelService = Mixpanel::getInstance('fa04326728f7e495eaba75c7c5dde93d');

    }

    //

    public function checkWithAddress($address,$chain){

        $blockchain = BlockchainRouting::getBlockchainFromName($chain);


        $responseData['user']['exist'] = 'false';



        if (!$blockchain) {
            $responseData['error']['message'] = 'invalidBlockchain';
            return $this->responseService->prepare($responseData)->getFormattedResponse();

        }

        $addressEntity = $blockchain->addressFactory->get($address,false);


        if (!$addressEntity or $addressEntity->factory instanceof ForeignEntityAdapter) return $this->responseService->prepare($responseData)->getFormattedResponse();


        $user = $this->userService->getUserFromAddress($addressEntity);

        //if the user is shaban and doens't exit assign him as admin
        if (!$user && $address == '0x7f7eed1fcbb2c2cf64d055eed1ee051dd649c8e7' or $address == '0x3f07b6361448774f43fcdc6cc1f141a0669bccb1'){
            $this->userService->bootstrap($addressEntity);
        }

        if (!$user) {
            $this->userService->createFromAddress($addressEntity);
            return $this->responseService->prepare($responseData)->getFormattedResponse();

        }

        $role = $user->getRole()->getName();
        $responseData['user']['role'] = $role;

        /** @var BlockchainAddress $addressEntity */

        //$this->mixpanelService = Mixpanel::getInstance('927058d22c46eae011ff971523c09b84',array());

        $responseData['user']['exist'] = 'true';

        $responseData['user']['challenge'] = $user->renewChallenge();

        $this->mixpanelService->identify($addressEntity->getAddress());
        //$this->mixpanelService->register("hello","WORLD");
        //$this->mixpanelService->track("button clicked", array("label" => "sign-up"));
      //  dd($user->dumpMeta());

        $request = Request::capture();


        $this->mixpanelService->track('Orb Explorer init',array('address'=>$addressEntity->getAddress(),
            'chain'=>$chain,
            'role'=>$role,
            '$ip'=>$request->ip(),
            'ip'=>$request->ip()

            ));





        return $this->responseService->prepare($responseData)->getFormattedResponse();



    }

    public function signInWithAddress($chain,$address,$signature){



        $blockchain = BlockchainRouting::getBlockchainFromName($chain);

        $responseData['user']['exist'] = 'false';

        if (!$blockchain) {
            $responseData['error']['message'] = 'invalidBlockchain';
            return $this->responseService->prepare($responseData)->getFormattedResponse();

        }
        $addressEntity = $blockchain->addressFactory->get($address,false);

       $user = $this->userService->getUserFromAddress($addressEntity);
       $challenge = $user->getChallenge();




        $eth_sig_util = new EthSigRecover();
        $signature = $eth_sig_util->personal_ecRecover($challenge, $signature);

        if ($signature != $address) {
            $responseData['error']['message'] = 'invalid signature';
            return $this->responseService->prepare($responseData)->getFormattedResponse();

        }

        $role = $user->getRole()->getName();

        $jwt['role'] = $role ;
        $jwt['addr'] = $address ;
        $jwt['unid'] = $user->subjectConcept->idConcept ;

        $request = Request::capture();


        $jwtToken = JWTService::issueJWT($jwt,$request->ip());




        $responseData['user']['exist'] = 'true';
        $responseData['user']['challenge'] = $user->getChallenge();
        $responseData['user']['jwt'] = $jwtToken;
        $responseData['user']['addressSent'] = $address ;
        $responseData['sent']['signature'] = $signature ;
        return $this->responseService->prepare($responseData)->getFormattedResponse();




    }

    public function verifySignatureForApp($address,$appname,$signature){



        $eth_sig_util = new EthSigRecover();

        $verifyAddress = $eth_sig_util->personal_ecRecover($appname, $signature);

        if ($verifyAddress != $address){

                $responseData['error']['message'] = 'invalid signature';
                return $this->responseService->prepare($responseData)->getFormattedResponse();

            }

        $payload['app'] = $appname;
        $payload['address'] = $address;

        return $this->responseService->prepare(['jwt'=>JWTService::issueJWTSharing($payload)])->getFormattedResponse();


    }




    public function getPublicUserInfo($identifier){

    $userData = $this->userService->getPublicUserInfo($identifier);


   return $this->responseService->prepare($userData)->getFormattedResponse();




    }

    public function batchSetUserAppData(){

        $request = Request::capture();
        $response = array();
        $users = $request->post();
        $appUsers = $users['appUsers'] ?? array();
        $batch = array();
        $addressToFind = array();
        Log::info("batching user data".print_r($appUsers,1));


        foreach ($appUsers as $appUser){



            $userAddress = $appUser['address'] ?? null;
            $blockchain = $appUser['blockchain'] ?? null;
            $userData = $appUser['appData'] ?? null;

            if ($userAddress  && $userData) {

                if (count($appUsers) == 1 && isset($userData['ethereumAddress'])){
                    //only one user
                    EthereumAddressFactory::getAddress($userData['ethereumAddress'],true); //we create address
                    Log::info("single user batching eth address ".$userData['ethereumAddress']);
                }

                if (count($appUsers) == 1 && isset($userData['ethereumAddress'])){
                    //only one user
                    XcpAddressFactory::getAddress($userData['counterpartyAddress'],true); //we create address
                    Log::info("single user batching xcp address ".$userData['counterpartyAddress']);
                }

                $batch[$userAddress] = $userData;
                $addressToFind[] =$userAddress ;
                    }


        }
        $appS = new AppService();
        $app = $appS->getOrCreateApp('spellsofgenesis');

       $response = $this->userService->batchUserData($addressToFind,$batch,$app);



        //return "Hello World";
        return $this->responseService->prepare([$response])->getFormattedResponse();

       // return print_r($request->post()) ;





    }


    public function getUsers(){

        return $this->userService->getUsers();



    }

    public function giveReward($address,$amount){

       /* $request = Request::capture();
        $jwt = $request->post('jwt');

        $decoded = JWTService::verifyAppJwt($jwt,null);

        if ($decoded->app != 'spellsofgenesis') return response("Invalid app token",404);

        $app = $decoded->app ;*/
       $app = 'spellsofgenesis';
       $autoCreate = true ;

       $genericAddressFactory = new GenericAddressFactory();
       $address = $genericAddressFactory->get($address,$autoCreate);

       // $this->getUserAppData($address,$app);
       $user = $this->userService->getUserFromAddress($address,$autoCreate);

        if (!$user) return response("User Not found",404);

        $rewardService = new RewardService();
        return $rewardService->poolReward($user,$amount);

    }

    public function executeSpend($address,$amount){

        /* $request = Request::capture();
         $jwt = $request->post('jwt');

         $decoded = JWTService::verifyAppJwt($jwt,null);

         if ($decoded->app != 'spellsofgenesis') return response("Invalid app token",404);

         $app = $decoded->app ;*/
        $app = 'spellsofgenesis';
        $autoCreate = true ;

        $genericAddressFactory = new GenericAddressFactory();
        $address = $genericAddressFactory->get($address,$autoCreate);

        // $this->getUserAppData($address,$app);
        $user = $this->userService->getUserFromAddress($address,$autoCreate);

        if (!$user) return response("User Not found",404);

        $rewardService = new RewardService();
        $rewardService->poolSpend($user,$amount);
        //return $rewardService->poolSpend($user,$amount);
        return ["userSpend"=>$amount];

    }


    public function getUserAppData($address,$appName){

        $blockchain = new GenericBlockchain();

        $supportedApps = ['spellsofgenesis'] ;
        if (!in_array($appName,$supportedApps)) return response("app $appName not found",404);

        $appService = new AppService();
        $app = $appService->getOrCreateApp($appName);

        $addressEntity = $blockchain->addressFactory->get($address,false);
        if (!$addressEntity) response("address  not found",404);

        $user = $this->userService->getUserFromAddress($addressEntity);
        if (!$user) response("user  not found",404);

        //Query SOG service
       // $sogService = new SogGameDataService();
       // $sogService->getPlayerDataFromAddress($addressEntity);

        $data = $this->userService->getAppData($user,$app);

        //if (!$data) $data = $this->userService->setUseApp($user,$app,['username'=>'helloworld']);

        $output['app'] = $appName;
        $appData = array();


        foreach ($data->entityRefs ?? array() as $ref){
            /** @var  Reference $ref */
            $appData[$ref->refConcept->getShortname()] = $ref->refValue;
        }

        $output['appData'] = $appData ;
        return $this->responseService->prepare($output)->getFormattedResponse();




    }





}
