<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\SystemConfig;
use CsCannon\Asset;
use CsCannon\AssetCollection;
use CsCannon\AssetCollectionFactory;
use CsCannon\AssetFactory;
use CsCannon\AssetSolvers\BooSolver;
use CsCannon\AssetSolvers\LocalSolver;
use CsCannon\Blockchains\BlockchainBlockFactory;
use CsCannon\Blockchains\BlockchainToken;
use App\Blockchains\Counterparty\XcpTokenFactory;
use App\Blockchains\Waves\WavesSynchronizationService;
use App\EnvRouting;
use App\SandraEnv;
use CsCannon\Blockchains\Counterparty\XcpBlockchain;
use CsCannon\Blockchains\Counterparty\XcpContractFactory;
use CsCannon\Blockchains\Ethereum\EthereumBlockchain;
use CsCannon\Blockchains\Ethereum\EthereumContractFactory;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC20;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC721;
use CsCannon\Blockchains\Ethereum\RopstenEthereumBlockchain;
use CsCannon\Blockchains\Ethereum\Sidechains\Matic\MaticBlockchain;
use CsCannon\Blockchains\Ethereum\Sidechains\Matic\MaticContractFactory;
use CsCannon\Blockchains\Klaytn\KlaytnBlockchain;
use CsCannon\Blockchains\Klaytn\KlaytnContractFactory;
use CsCannon\Blockchains\Substrate\Kusama\KusamaAddressFactory;
use CsCannon\Blockchains\Substrate\Kusama\KusamaBlockchain;
use CsCannon\Blockchains\Substrate\Kusama\KusamaEventFactory;
use CsCannon\Blockchains\Substrate\Kusama\WestendBlockchain;
use CsCannon\Blockchains\Substrate\Kusama\WestendEventFactory;
use CsCannon\Blockchains\Substrate\RMRK\RmrkContractFactory;
use CsCannon\Blockchains\Substrate\RMRK\RmrkContractStandard;
use CsCannon\SandraManager;
use CsCannon\TokenPathToAssetFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use InnateSkills\LearnFromWeb\LearnFromWeb;
use Kickstart;
use SandraCore\EntityFactory;
use SandraCore\System;
use const CsCannon\COLLECTION_CODE;


ini_set('max_execution_time', 600);

class EnvController extends Controller
{
    //

    public function getEnv(){


        return $_ENV ;

    }

    public function getEnvFactory(){

        $metaSandra = app('Sandra')->getMetaSandra();

        $envFactory = new EntityFactory(EnvRouting::ENV_ENTITY,EnvRouting::ENV_FILE,$metaSandra);
        $envFactory->setGeneratedClass(SandraEnv::class);
        $envFactory->populateLocal();

        return $envFactory ;


    }

    public function create($name){

        $factory = self::getEnvFactory();
       $newEnv = $factory->createNew([EnvRouting::ENV_IDENTIFIER =>$name]);

        return $newEnv ;


    }


    public function envExist($envName){

        $factory = self::getEnvFactory();
       return $factory->first(EnvRouting::ENV_IDENTIFIER,$envName);



    }

    public function enterEnv($envName){

        //we look at the env stored in the console cache

        $metaSandra = app('Sandra')->getMetaSandra();
        /** @var System $metaSandra */

        if(self::envExist($envName)){
            $_ENV["SANDRAENV"] = $envName ;
            return EnvRouting::getCurrentSandraEnv();
        }

       $metaSandra->systemError("",self::class,3," $envName env not found");







    }


    public function enterConsoleEnv(){

        //we look at the env stored in the console cache

        $metaSandra = app('Sandra')->getMetaSandra();
        /** @var System $metaSandra */

        $consoleManager = $metaSandra->factoryManager->create('consoleStatus','consoleStatus','consoleStatusFile');
        $consoleManager->populateLocal();

        $currentConsole = $consoleManager->getOrCreateFromRef('console','defaultConsole');
        $currentEnv = $currentConsole->getOrInitReference('currentEnv',env('SANDRA_ENV'));

       // die("dead at current env".$currentEnv->refValue);


        $_ENV["SANDRAENV"] = $currentEnv->refValue ;

        return EnvRouting::getCurrentSandraEnv();



    }

    public function bootstrap($envName,$type=null){



        $env = self::envExist($envName);

        $contractFactory = new KlaytnContractFactory();
        $sandra = SandraManager::getSandra();

        $_ENV["SANDRAENV"] = $envName ;
        EnvRouting::getCurrentSandraEnv();


        if ($type == 'testnet'){
            //we force this datagraph to be testnet

            $config = new SystemConfig();
            $config->allowOnlyTestnet();
            echo"only testnet";


        }

        if ($type == 'notTestnet'){
            //we force this datagraph to be testnet

            $config = new SystemConfig();
            $config->allowOnlyTestnet(false);


        }


        if (strtolower($type) == 'firstoasis'){

            $this->bootstrapFirstOasis();


        }



        if($envName == 'xcp'  or $type == 'xcp'  ){

            $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
            $activeBlockchainData->populateLocal();

            $data['blockchain'] = XcpBlockchain::NAME;
            $data['explorerTx'] = XcpBlockchain::getNetworkData('mainet','explorerTx');
            $activeBlockchainData->createOrUpdateOnReference('blockchain',XcpBlockchain::NAME,$data,['onBlockchain'=>XcpBlockchain::NAME]);

            $activeBlockchainData->createViewTable("activeBlockchains");



            $adminController = new AdminController(new UserService());
            $sandra = SandraManager::getSandra();
            BooSolver::update();
            //$adminController->buildCollectionLearners();
            //$adminController->deployOpensea();



            $env->getOrInitReference('bootstrap_opensea',mktime());
            $env->getOrInitReference('bootstrap_boo',mktime());



        }

        if ($type == "eth"){

            EthereumBlockchain::getStatic();

            $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
            $activeBlockchainData->populateLocal();


            $data['blockchain'] = EthereumBlockchain::NAME;
            $data['explorerTx'] = EthereumBlockchain::getNetworkData('mainet','explorerTx');
            $activeBlockchainData->createOrUpdateOnReference('blockchain',EthereumBlockchain::NAME,$data,['onBlockchain'=>EthereumBlockchain::NAME]);

            $activeBlockchainData->createViewTable("activeBlockchains");

        }

        if ($type == "maticTest"){

            MaticBlockchain::getStatic();

            $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
            $activeBlockchainData->populateLocal();


            $data['blockchain'] = MaticBlockchain::NAME;
            $data['explorerTx'] = MaticBlockchain::getNetworkData('mainet','explorerTx');
            $activeBlockchainData->createOrUpdateOnReference('blockchain',MaticBlockchain::NAME,$data,['onBlockchain'=>MaticBlockchain::NAME]);

            $activeBlockchainData->createViewTable("activeBlockchains");

            $contractFactory = new MaticContractFactory();


            $assetCollectionFactory = new \CsCannon\AssetCollectionFactory(SandraManager::getSandra());
            $assetCollectionFactory->populateLocal();
            $collectionEntity = $assetCollectionFactory->get("eSog");
            $collectionEntity->setName("Spells of Genesis (testnet)");
            $collectionEntity->setDescription("The 1st blockchain-based mobile game ever made, Spells of Genesis combines TCG functionalities with the point-and-shoot aspects of arcade games. Players have to collect and combine cards to create the strongest deck in order to fight their enemies.");
            $collectionEntity->setImageUrl("http://sandradev.everdreamsoft.com/images/orbcenter/eSog/banner.png");
            $collectionEntity->setSolver(\CsCannon\AssetSolvers\PathPredictableSolver::getEntity('https://metadata.spellsofgenesis.com/0x9227a3d959654c8004fa77dffc380ec40880fff6/image/{tokenId}.jpg',
                'https://metadata.spellsofgenesis.com/sog-eth-askian/{tokenId}',
                'http://sandradev.everdreamsoft.com/images/orbcenter/eSog/banner.png'));
            $contract = $contractFactory->get('0x87427374a2199e317d9A50DEf8Cf23603AEE0da7', true, ERC721::init());

            $contract->bindToCollection($collectionEntity);

            $collectionEntity = $assetCollectionFactory->getOrCreate("maticTest");
            $collectionEntity->setName("Matic Test Collection");
            $collectionEntity->setDescription("We could update this text in the future");
            $collectionEntity->setImageUrl("https://tokenpost.com/assets/uploads/20190502ac6b32835ae4c139a.png");
            $collectionEntity->setSolver(\CsCannon\AssetSolvers\PathPredictableSolver::getEntity('https://tokenpost.com/assets/uploads/20190502ac6b32835ae4c139a.png','https://matic.network/','https://tokenpost.com/assets/uploads/20190502ac6b32835ae4c139a.png'));

            $contract = $contractFactory->get('0x4fc2FC9AC135978604273B3B8A730B1b48b86d14', true, ERC721::init());

            $contract->bindToCollection($collectionEntity);

            // $this->track($contract,ERC721::init());


        }

        if ($type == "ropsten"){

            RopstenEthereumBlockchain::getStatic();

            $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
            $activeBlockchainData->populateLocal();


            $data['blockchain'] = RopstenEthereumBlockchain::NAME;
            $data['explorerTx'] = RopstenEthereumBlockchain::getNetworkData('mainet','explorerTx');
            $activeBlockchainData->createOrUpdateOnReference('blockchain',RopstenEthereumBlockchain::NAME,$data,['onBlockchain'=>RopstenEthereumBlockchain::NAME]);

            $activeBlockchainData->createViewTable("activeBlockchains");

            $contractFactory = new \Ethereum\Sandra\EthereumContractFactory();


            $assetCollectionFactory = new \CsCannon\AssetCollectionFactory(SandraManager::getSandra());
            $assetCollectionFactory->populateLocal();
            $collectionEntity = $assetCollectionFactory->get("eSog");
            $collectionEntity->setSolver(\CsCannon\AssetSolvers\PathPredictableSolver::getEntity('https://metadata.spellsofgenesis.com/0x9227a3d959654c8004fa77dffc380ec40880fff6/image/{tokenId}.jpg','https://metadata.spellsofgenesis.com/sog-eth-askian/{tokenId}','http://sandradev.everdreamsoft.com/images/orbcenter/eSog/banner.png'));
            $contract = $contractFactory->get('0x40b7696EB928111Ee7A0CDD9dCbFeDB56A00aECB', true, ERC721::init());

            $contract->bindToCollection($collectionEntity);
            // $this->track($contract,ERC721::init());


        }


        if($envName == 'cypress' or $type == 'cypress'){

            $erc20 = ERC20::init();
            $contract = $contractFactory->get('0xe3656452c8238334efdfc811d6f98e5962fe4461', true, ERC721::init());

            $assetCollectionFactory = new \CsCannon\AssetCollectionFactory(\CsCannon\SandraManager::getSandra());
            $assetCollectionFactory->populateLocal();
            $collectionEntity = $assetCollectionFactory->getOrCreate("CryptoCarto");
            $collectionEntity->setName("Crypto Carto");
            $collectionEntity->setDescription("With CryptoCarto, pin a message anywhere on the planet and get a corresponding ERC-721 token");

            $collectionEntity->setImageUrl("http://klaytn.orbexplorer.com/images/featured/CryptoCartoSplashCard.png");
            $assetCollectionFactory->populateLocal();
            $collectionEntity->setSolver(\CsCannon\AssetSolvers\PathPredictableSolver::getEntity('https://app.cryptocarto.xyz/img/pin-token/{tokenId}.png','https://cryptocarto.herokuapp.com/token/{tokenId}'));

            $contract->bindToCollection($collectionEntity);



            $contractBcyM = $contractFactory->get('0xedc2b5835c34267e9bbab213ca717c065031e705', true, $erc20);


            $firstOasisCollection = $assetCollectionFactory->getOrCreate("FirstOasisCurrencies");
            $firstOasisCollection->setName("First Oasis Currencies");
            $firstOasisCollection->setDescription("");

            $firstOasisCollection->setImageUrl("http://www.orbexplorer.com/images/firstOasis/OasisFull.png");
            $firstOasisCollection->setSolver(LocalSolver::getEntity());

            $contractBcyM->bindToCollection($firstOasisCollection);

            $assetFactory = new AssetFactory(SandraManager::getSandra());





            $metadataBCYM = [AssetFactory::IMAGE_URL => 'http://www.orbexplorer.com/images/tokenLogo/50/bitcrystalsM.png',
                AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/kim1",

            ];

            $asset = $assetFactory->create('BCYM', $metadataBCYM, [$firstOasisCollection], [$contractBcyM]);

            $asset->bindToCollection($firstOasisCollection);






            $networkId = 'cypress';


        }

        if($envName == 'klay'){

            $erc20 = ERC20::init();




            $kim1 = $contractFactory->get('0x60409aDAba3d10cc9B0ACBD91Ce09865831b3CDd', true,$erc20);
            $kim2 = $contractFactory->get('0x1Fd67f7fa77Ca5A5e700aC88B7787dDd4aA5173F', true,$erc20);
            $kim3 = $contractFactory->get('0xDe75611b129624D91ed8D595de35fff75bFcE28c', true,$erc20);
            $kim4 = $contractFactory->get('0xE4C0B80060Bec0C2B4337d88366F76b4C7d36740', true,$erc20);
            $kim5 = $contractFactory->get('0xAa9c9E734D799f20acf71C46740c978F7249d44A', true,$erc20);
            $kim6 = $contractFactory->get('0x7F288F2980ff4165185c961348D699CB127c124C', true,$erc20);


            $networkId = 'baobab';



        }

        if($envName == 'klay' or $envName == 'cypress' or  $type == 'cypress'){

            $erc20 = ERC20::init();


            $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
            $activeBlockchainData->populateLocal();

            $data['blockchain'] = KlaytnBlockchain::NAME;
            $data['explorerTx'] = KlaytnBlockchain::getNetworkData($networkId,'explorerTx');
            $activeBlockchainData->createOrUpdateOnReference('blockchain',KlaytnBlockchain::NAME,$data,['onBlockchain'=>KlaytnBlockchain::NAME]);

            $activeBlockchainData->createViewTable("activeBlockchains");

            /* only Klay

            $_ENV["SANDRAENV"] = $envName ;
             EnvRouting::getCurrentSandraEnv();

            $adminController = new AdminController();
            $adminController->createLearner();
            $adminController->buildCollectionLearners();
            $adminController->deployOpensea();



            $env->getOrInitReference('bootstrap_opensea',mktime());
            $env->getOrInitReference('bootstrap_boo',mktime());
            */
            $sandra = SandraManager::getSandra();

            echo "Writing in concept table ". $sandra->conceptTable. PHP_EOL ;



            $collectionEntity2 = $assetCollectionFactory->getOrCreate("FoodBadges");
            $collectionEntity2->setName("Kim's Art");
            $collectionEntity2->setDescription("Hello I'm Kim Self-taught artist, with a colorful and feminine universe. Inspired by manga and pretty things.");
            $collectionEntity2->setImageUrl("http://klay.bitcrystals.com/img/KimCollection.jpg");

            $collectionEntity2->setSolver(LocalSolver::getEntity());

            // Asset



            $kim1 = $contractFactory->get('0x13f846aACEfaF3f305cDb8f922F9117B102fc8aF', true,$erc20);
            $kim2 = $contractFactory->get('0x31562d55FeFD7466280F211d47583b4a8c29d459', true,$erc20);
            $kim3 = $contractFactory->get('0xab741bfc9Ee3A47Fe994E4a3640915972BfB7E9e', true,$erc20);
            $kim4 = $contractFactory->get('0x82F8e4c4439b66CDD12817E7F11370d6B4692f50', true,$erc20);
            $kim5 = $contractFactory->get('0x5374917B30F17fd3FBbBd1425B18AF00c043C2ad', true,$erc20);
            $kim6 = $contractFactory->get('0x054fF89500451062Bf00AD2F4dD1DeBA4a1c69B9', true,$erc20);





            $assetFactory = new AssetFactory(SandraManager::getSandra());





            $metaKim1 = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/kim1.jpg',
                AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/kim1",

            ];

            $metaKim2 = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/kim2.jpg',
                AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/kim2",

            ];

            $metaKim3 = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/kim3.jpg',
                AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/kim3",

            ];

            $metaKim4 = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/kim4.jpg',
                AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/kim4",

            ];

            $metaKim5 = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/kim5.jpg',
                AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/kim5",

            ];

            $metaKim6 = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/kim6.jpg',
                AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/kim6",

            ];

            $kim1->bindToCollection($collectionEntity2); // should be this trivial ?
            $kim2->bindToCollection($collectionEntity2); // should be this trivial ?
            $kim3->bindToCollection($collectionEntity2); // should be this trivial ?
            $kim4->bindToCollection($collectionEntity2); // should be this trivial ?
            $kim5->bindToCollection($collectionEntity2); // should be this trivial ?
            $kim6->bindToCollection($collectionEntity2); // should be this trivial ?




            $asset = $assetFactory->create('kim1', $metaKim1, [$collectionEntity2], [$kim1]);
            $asset = $assetFactory->create('kim2', $metaKim2, [$collectionEntity2], [$kim2]);
            $asset = $assetFactory->create('kim3', $metaKim3, [$collectionEntity2], [$kim3]);
            $asset = $assetFactory->create('kim4', $metaKim4, [$collectionEntity2], [$kim4]);
            $asset = $assetFactory->create('kim5', $metaKim5, [$collectionEntity2], [$kim5]);
            $asset = $assetFactory->create('kim6', $metaKim6, [$collectionEntity2], [$kim6]);


        }

        if ($type == 'stablecoins'){

            $assetCollectionFactory = new \CsCannon\AssetCollectionFactory(SandraManager::getSandra());

            $stableCoinCollection = $assetCollectionFactory->getOrCreate("stableCoins");
            $stableCoinCollection->setName("Stable Coins");
            $stableCoinCollection->setDescription("");
            $stableCoinCollection->setImageUrl("https://cdn.worldvectorlogo.com/logos/dai-2.svg");


            $ethereumCOntractFactory = new EthereumContractFactory();
            $daiContract = $ethereumCOntractFactory->get('0x6b175474e89094c44da98b954eedeac495271d0f',true)
                ->setAlias("Dai");
            $daiContract->setBrotherEntity(ContractController::TRACK_VERB,ContractController::TRACK_TARGET,[]);
            $daiContract->setStandard(ERC20::init());
            $daiContract->bindToCollection($stableCoinCollection);



            $assetFactory = new AssetFactory(SandraManager::getSandra());





            $metadataDai = [AssetFactory::IMAGE_URL => 'https://cdn.worldvectorlogo.com/logos/dai-2.svg',
                AssetFactory::METADATA_URL => "http://www.crystalsuite.com",

            ];

            $asset = $assetFactory->getOrCreate('DAI', $metadataDai, [$stableCoinCollection], [$daiContract]);



        }

        if ($type == 'kusama_rmrk'){

           $kusama = new KusamaBlockchain();
           \CsCannon\Kickstart::createViewForBlockchain($kusama);



            $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
            $activeBlockchainData->populateLocal();
            //dd($activeBlockchainData);


            $data['blockchain'] = KusamaBlockchain::NAME ;
            $data['explorerTx'] = KusamaBlockchain::getNetworkData('mainet','explorerTx');
            $activeBlockchainData->createOrUpdateOnReference('blockchain',KusamaBlockchain::NAME,$data,['onBlockchain'=>KusamaBlockchain::NAME]);

            $activeBlockchainData->createViewTable("activeBlockchains");

            $eventFactory = new KusamaEventFactory();
            $kusamaBlockchain = new KusamaBlockchain();

            $tokenToContractFactory = new TokenPathToAssetFactory($sandra);
            $tokenToContractFactory->populateLocal();
            $tokenToContractFactory->createViewTable("tokenToContract");

           //default local solver to all ksm collections
            $assetCollectionFactory = new AssetCollectionFactory($sandra);
            $assetCollectionFactory->populateLocal();


            foreach ($assetCollectionFactory->getEntities() as $collection){

                /** @var AssetCollection $collection */
                $collection->setSolver(LocalSolver::getEntity());

            }



        }

        if ($type == 'westend_rmrk'){

            $westend = new WestendBlockchain();
            \CsCannon\Kickstart::createViewForBlockchain($westend);



            $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
            $activeBlockchainData->populateLocal();
            //dd($activeBlockchainData);


            $data['blockchain'] = WestendBlockchain::NAME ;
            $data['explorerTx'] = WestendBlockchain::getNetworkData('mainet','explorerTx');
            $activeBlockchainData->createOrUpdateOnReference('blockchain',WestendBlockchain::NAME,$data,['onBlockchain'=>WestendBlockchain::NAME]);

            $activeBlockchainData->createViewTable("activeBlockchains");

            $eventFactory = new WestendEventFactory();
            $kusamaBlockchain = new WestendEventFactory();

            $tokenToContractFactory = new TokenPathToAssetFactory($sandra);
            $tokenToContractFactory->populateLocal();
            $tokenToContractFactory->createViewTable("tokenToContract");

            //default local solver to all ksm collections
            $assetCollectionFactory = new AssetCollectionFactory($sandra);
            $assetCollectionFactory->populateLocal();


            foreach ($assetCollectionFactory->getEntities() as $collection){

                /** @var AssetCollection $collection */
                $collection->setSolver(LocalSolver::getEntity());

            }



        }

        \CsCannon\Kickstart::createViews();

       return ;





    }

    public function addKlaySettlers(){

        $erc20 = ERC20::init();

        $contractFactory = new KlaytnContractFactory();

        $assetCollectionFactory = new \CsCannon\AssetCollectionFactory(SandraManager::getSandra());
        $assetCollectionFactory->populateLocal();
        $collectionEntity = $assetCollectionFactory->getOrCreate("KlaySettlers");
        $collectionEntity->setName("Klay Settlers");
        $collectionEntity->setDescription("Meet klaytn settlers the first ever cute collectibles on Klaytn Blockchain");
        $collectionEntity->setImageUrl("http://klay.bitcrystals.com/img/islands.jpg");
        $assetCollectionFactory->populateLocal();
        $collectionEntity->setSolver(LocalSolver::getEntity());

        $contractBunny = $contractFactory->get('0xB2c48D6384feA29283b51622f179dC51ffB178E0', true,$erc20);
        $contractSal = $contractFactory->get('0x53Dd98cA4B63178841155fCd80d4C4Ca7D5Ba331', true,$erc20);
        $contractHor = $contractFactory->get('0x7cDB98E90441DC2040B7a1627a1335D99B4C3859', true,$erc20);


        $contractBunny->bindToCollection($collectionEntity); // should be this trivial ?
        $contractSal->bindToCollection($collectionEntity); // should be this trivial ?
        $contractHor->bindToCollection($collectionEntity); // should be this trivial ?

        $assetFactory = new AssetFactory(SandraManager::getSandra());

        $metaDataB = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/lap_base_color.png',
            AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/bunny",

        ];

        $metaDataS = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/sal_base_color.png',
            AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/sal",

        ];

        $metaDataH = [AssetFactory::IMAGE_URL => 'http://klay.bitcrystals.com/img/hor_base_color.png',
            AssetFactory::METADATA_URL => "http://klay.bitcrystals.com/meta/sal",

        ];

        $asset = $assetFactory->create('bunny', $metaDataB, [$collectionEntity], [$contractBunny]);
        $asset = $assetFactory->create('salamander', $metaDataS, [$collectionEntity], [$contractSal]);
        $asset = $assetFactory->create('horse', $metaDataH, [$collectionEntity], [$contractHor]);





    }

    public function bootstrapFirstOasis(){

        $assetCollectionFactory = new AssetCollectionFactory(SandraManager::getSandra());

        $klaytnContractFactory = new KlaytnContractFactory();
        $klaytnContractFactory->populateLocal();


        $contractBcyM = $klaytnContractFactory->get('0xedc2b5835c34267e9bbab213ca717c065031e705', true, ERC20::init());


        $firstOasisCollection = $assetCollectionFactory->getOrCreate("FirstOasisCurrencies");
        $firstOasisCollection->setName("First Oasis Currencies");
        $firstOasisCollection->setDescription("");

        $firstOasisCollection->setImageUrl("http://www.orbexplorer.com/images/firstOasis/OasisFull.png");
        $firstOasisCollection->setSolver(LocalSolver::getEntity());

        $contractBcyM->bindToCollection($firstOasisCollection);

        $assetFactory = new AssetFactory(SandraManager::getSandra());


        $metadataBCYM = [AssetFactory::IMAGE_URL => 'http://www.orbexplorer.com/images/tokenLogo/50/bitcrystalsM.png',
            AssetFactory::METADATA_URL => "http://bitcrystals.com/",

        ];

        $asset = $assetFactory->create('BCYM', $metadataBCYM, [$firstOasisCollection], [$contractBcyM]);

        $asset->bindToCollection($firstOasisCollection);

        $xcpContractFactory = new XcpContractFactory();
        $xcpContractFactory->populateLocal();
        $bcyXCPContract = $xcpContractFactory->get("BITCRYSTALS",true)->setAlias("BitCrystals Counterparty");

        $metadataBCY = [AssetFactory::IMAGE_URL => 'http://www.orbexplorer.com/images/tokenLogo/50/bitcrystals.png',
            AssetFactory::METADATA_URL => "http://bitcrystals.com/",

        ];


        $ethereumCOntractFactory = new EthereumContractFactory();
        $bcyETHContract = $ethereumCOntractFactory->get('0xAdbBB02E20C44779e87F7eA90C47c9A7a8A93fee',true)
            ->setAlias("BitCrystals Ethereum");
        $bcyETHContract->setBrotherEntity(ContractController::TRACK_VERB,ContractController::TRACK_TARGET,[]);
        $bcyETHContract->setStandard(ERC20::init());
        $bcyETHContract->bindToCollection($firstOasisCollection);
        $bcyXCPContract->bindToCollection($firstOasisCollection);


        $assetBCY = $assetFactory->create('BCY', $metadataBCY, [$firstOasisCollection], [$bcyXCPContract,$bcyETHContract]);
        $assetBCY->bindToCollection($firstOasisCollection);
        $assetBCY->bindToContract($bcyETHContract);

        $erc20 = ERC20::init();


        $bcyETHContract->setAbi($erc20->getInterfaceAbi());


        //sog collection
        $assetCollectionFactory = new \CsCannon\AssetCollectionFactory(SandraManager::getSandra());
        $assetCollectionFactory->populateLocal();
        $collectionEntity = $assetCollectionFactory->getOrCreate("eSog");
        $collectionEntity->setSolver(\CsCannon\AssetSolvers\PathPredictableSolver::getEntity('https://metadata.spellsofgenesis.com/0x9227a3d959654c8004fa77dffc380ec40880fff6/image/{tokenId}.jpg','https://metadata.spellsofgenesis.com/sog-eth-askian/{tokenId}','http://sandradev.everdreamsoft.com/images/orbcenter/eSog/banner.png'));
        $collectionEntity->setDescription("The 1st blockchain-based mobile game ever made, Spells of Genesis combines TCG functionalities with the point-and-shoot aspects of arcade games. Players have to collect and combine cards to create the strongest deck in order to fight their enemies.");
        $collectionEntity->setImageUrl("http://sandradev.everdreamsoft.com/images/orbcenter/eSog/banner.png");


        echo"createing sog";

        $klaytnContractFactory = new KlaytnContractFactory();
        $sogCardKlaytn = $klaytnContractFactory->get('0xd346d304ea1837053452357c2066a4701de9a04b', true,\CsCannon\Blockchains\Ethereum\Interfaces\ERC721::init()); //FACTORY
        $sogCardKlaytn->setBrotherEntity(ContractController::TRACK_VERB,ContractController::TRACK_TARGET,[]);
        $sogCardKlaytn->bindToCollection($collectionEntity);






    }


}
