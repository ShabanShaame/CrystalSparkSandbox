<?php

namespace App\Http\Controllers;

use App\Services\CrystalCollectionService;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Ethereum\EthereumContractFactory;
use CsCannon\SandraManager;
use Illuminate\Http\Request;
use SandraCore\ForeignEntityAdapter;

class CrystalCollectionController extends Controller
{


    /**
     * @var CrystalCollectionService
     */
    private $ccService;

    public function __construct()
    {
        $envController = new EnvController();

        $this->ccService = new CrystalCollectionService();


    }

    public function redirectCCartwork($artworkId,$artworkIndex){


        if ($artworkId == 1)
        return redirect('https://crystalsuite.com/crystal_collection_gemz/?');

        if ($artworkId == 2)
            return redirect('https://crystalsuite.com/crystal_collection_gemz/');

        if ($artworkId == 3)
            return redirect('https://crystalsuite.com/crystal_collection_doge/');


        if ($artworkId == 4)
            return redirect('https://crystalsuite.com/crystal_collection_mt-gox/');



    }

    public function getMetadata($contractAlias,$tokenId){

        return $this->ccService->getMetadata($contractAlias,$tokenId);


    }

    public function getMetadataPatch($tokenId){

        return $this->ccService->getMetadata('ecc-rinkeby',$tokenId);

    }

    public function synchAssetsFromGoogleSheet(){

        $blockchainContractFactory = new EthereumContractFactory();
        $blockchainContractFactory->populateLocal();


        return $this->ccService->synchAssetsFromGoogleSheet();



    }




}
