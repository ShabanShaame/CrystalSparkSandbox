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
use App\SystemConfig;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\BlockchainContractStandard;
use CsCannon\Blockchains\Counterparty\DataSource\XchainOnBcy;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC20;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC721;
use CsCannon\Blockchains\Generic\GenericContractFactory;
use CsCannon\BlockchainStandardFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ContractController extends GenericSandraController
{

    const TRACK_VERB = 'tracker';
    const TRACK_TARGET= 'tracked';
    const STICK_ISA = 'sticker';
    const STICK_ID = 'id';
    const STICKER_DEFAULT_ID = 'featured';

    public function trackContract($contractId, Request $request){

        AuthManager::adminOnly();

        $contract = $this->getContractFromId($contractId);


        $standard = null ;

        $standardName = $request->post('standard');
        if ($standardName == 'ERC721') $standard = ERC721::init();
        if ($standardName == 'ERC20') $standard = ERC20::init();

        if(is_null($standard)) $this->returnError("Standard could not be found please specify a valid standard ".$standardName);

       return $this->track($contract,$standard);

    }

    public static function getTrackedContracts(){

        $sandra = app('Sandra')->getSandra();

        $genericContractFactory = new GenericContractFactory();

        /** @var BlockchainContractFactory $trackedFactory */
        $genericContractFactory->setFilter(self::TRACK_VERB,self::TRACK_TARGET);
        $genericContractFactory->populateLocal();

        return $genericContractFactory->getEntities();

    }



    private function track(BlockchainContract $contract,  BlockchainContractStandard $standard)
    {




        $sandra = app('Sandra')->getSandra();
        $contract->setBrotherEntity(self::TRACK_VERB,self::TRACK_TARGET,$sandra);
        $contract->setStandard($standard);

        $responseService = new ResponseService();

        $blockchain = $contract->getBlockchain();
       $trackedFactory =  $blockchain->getContractFactory();
       $saveAbi = null ;



       //get ABI
        $client = new Client();
        try {
            $res = $client->request('GET', 'https://api.etherscan.io/api?module=contract&action=getabi&address=' . $contract->getId() . '&apikey=21AH14S1UUEXRJRPT6PA1HVCK5DVXHTT3V');

            $result = json_decode($res->getBody());
            $abiRaw = $result->result ;
            $abiRefined = $abiRaw;
            $abi = $abiRefined;
            $saveAbi = $abiRefined;

            // $abi = stripslashes($abi);
        } catch (ClientException  $e) {
            $abi = null;
            $this->returnError('abi not found https://api.etherscan.io/api?module=contract&action=getabi
                &address=' . $contract->getId() . '');

        }

        $contract->setAbi($saveAbi);


       /** @var BlockchainContractFactory $trackedFactory */
       $trackedFactory->setFilter(self::TRACK_VERB,self::TRACK_TARGET);
       $trackedFactory->populateLocal();

        $responseService->prepare($trackedFactory->return2dArray());
        return $responseService->getFormattedResponse();


    }

    public function requireExplicitTrackForChain($blockchainName)
    {

        AuthManager::adminOnly();
      $config = new SystemConfig();
      $config->load();
      $sandra = $this->getSandra();
      $blockchainConcept = $sandra->systemConcept->tryGetSC($blockchainName);

      if (is_null($blockchainConcept)) $this->returnNotFound();

      $blockchainClass = BlockchainRouting::getBlockchainFromName($blockchainName);

      $config->requireExplicitTrackingForChain($blockchainClass);

      return 'ok';

    }

    public function getContractInfo($contractId){

        $genericContractFactory = new GenericContractFactory();
        $genericContractFactory->populateFromSearchResults($contractId,BlockchainContractFactory::MAIN_IDENTIFIER);
        $response = array();

        foreach ($genericContractFactory->getEntities() as $contract){
            /** @var BlockchainContract $contract */


        $blockchain = $contract->getBlockchain();
        XchainOnBcy::$dbHost = env('DB_HOST_XCP');
        XchainOnBcy::$db = env('DB_DATABASE_XCP');
        XchainOnBcy::$dbUser = env('DB_USERNAME_XCP');
        XchainOnBcy::$dbpass = env('DB_PASSWORD_XCP');

        $contract = $blockchain->getContractFactory()->get($contractId, false);
        $contract->getId();
        $contract->setDataSource(new XchainOnBcy());
        $metadata = $contract->metadata->refreshData();
        $response[$blockchain::NAME] = $metadata->getDisplay();

    }

        $responseService = new ResponseService();
        return  $responseService->prepare($response)->getFormattedResponse();









    }





}