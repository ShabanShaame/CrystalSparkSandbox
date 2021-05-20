<?php

namespace App\Http\Controllers;


use App\ResponseService;
use App\Services\UserService;
use App\Sog\SogGameDataService;
use App\Sog\SogService;
use CsCannon\Asset;
use CsCannon\AssetCollection;
use CsCannon\AssetCollectionFactory;
use CsCannon\AssetFactory;
use CsCannon\AssetSolvers\AssetSolver;
use CsCannon\AssetSolvers\PathPredictableSolver;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Ethereum\EthereumBlockchain;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC721;
use CsCannon\Blockchains\Generic\GenericBlockchain;
use CsCannon\Blockchains\Generic\GenericContractFactory;
use CsCannon\MetadataSolverFactory;
use CsCannon\SandraManager;
use CsCannon\TokenPathToAssetFactory;
use CsSog\Sandra7Bridge;
use CsSog\SogCard;
use CsSog\SogCardFactory;
use Ethereum\Ethereum;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use  Illuminate\Http\Request ;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\DataTables;


class SogController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $sogService;
    private $sogGameDataService;

     public function __construct(SogService $sogService, SogGameDataService $sogGameDataService)
     {

         $this->sogService = $sogService ;
         $this->sogGameDataService = $sogGameDataService;



     }


    public function getMetadata($contractAlias,$tokenId)
    {

       return  $this->sogService->getMetaDataForContract($contractAlias,$tokenId);


    }

    public function getFactoryMetadata($contractAlias,$factoryId)
    {

        return  $this->sogService->getMetaDataForFactory($contractAlias,$factoryId);


    }

    public function getImage($contract,$tokenPath)
    {


         $sandra = $this->sogService->getSogSandra();

        if ($tokenPath == 0 or $tokenPath == 1381) $this->sogService->renderUnbound(true);

        $this->sogService->createSogContract(EthereumBlockchain::getStatic());

        $sogContracts = $this->sogService->getSogContracts(GenericBlockchain::getStatic());

        $target =
        collect($sogContracts)->where('id', $contract)->toArray();
        $target = end($target);

        /**@var \CsCannon\Blockchains\BlockchainContract $target */

        $standard = $target->getStandard();


        return $this->sogService->getImageFromContract($target,$standard::init($tokenPath));


    }

    public function getCards($bundle = null)
    {

        if ($bundle && $bundle != 'defaultBundle')
        $output = $this->sogService->getAllCards($bundle);
        else
            $output = $this->sogService->getAllCards();

        return response()->json($output, 200);


    }

    //public function token
    public function getPlayerFromAddress($addressString)
    {


        $blockchain = new GenericBlockchain() ;

        $addressEntity = $blockchain->getAddressFactory()->get($addressString);
        if (!$addressEntity) return response("address not found",404);
        $players = $this->sogGameDataService->getPlayerDataFromAddress($addressEntity);
        if (!$players)  return response("no players",404);
        $player = end($players);

       // $userService = new UserService();
        //$userEntity = $userService->getUserFromAddress($addressEntity);

       // if (!$userEntity) {

         //   $userEntity = $userService->createFromAddress($addressEntity);
       // }




        return $player ;

    }



    public function bindSogCardToToken($contractAlias, $tokenId,$cardId)
    {



        if ($cardId == 0 or $cardId == 'unbind')
            $card = $this->sogService->getNullCard();
        else
            $card = $this->sogService->getCardFromId($cardId);


        $standard = ERC721::init($tokenId);

        if (!$card) return response("card not found",404);

        $contract = $this->sogService->getContractByAlias($contractAlias);


       return $this->sogService->bindCardToContract($contract,$standard,$card);


    }

    public function getBindDatatable($sogContract)
    {

        $collection = collect($this->getBindInfo($sogContract));

       return DataTables::of($collection)->toJson();

    }

    public function getBindInfo($sogContract)
    {

        $contract = $this->sogService->getContractByAlias($sogContract);
        $return = array();

        $tokentPathFactory = new TokenPathToAssetFactory(SandraManager::getSandra());
        $tokentPathFactory->populateLocal();

        $assetFactory = new SogCardFactory('sogCardProdFile',SandraManager::getSandra());
        $tokentPathFactory->joinFactory($contract->subjectConcept->idConcept,$assetFactory);
        $tokentPathFactory->joinPopulate();

        foreach ($tokentPathFactory->getEntities() ?? array() as $path){

            $joinedAssets = $path->getJoinedEntities($contract);
            $assetArray = array();
            foreach ($joinedAssets ?? array() as $asset){
                /** @var SogCard $asset */

                $assetData['name'] = $asset->getName();
                $assetData['moongaId'] = $asset->getMoongaId();
                $assetData['tokenId'] = $path->get(TokenPathToAssetFactory::ID);
                $assetData['title'] = "hello";
                $assetData['imgUrl'] = $asset->imageUrl ;
               // $assetData['all'] = $asset->dumpMeta();
               // $assetArray[]['asset'] = $assetData ;
                $assetArray = $assetData ;
                $return[] = $assetArray ;


            }
            $head['tokenId']= $path->get(TokenPathToAssetFactory::ID);


        }

        return($return);

    }

    public function bindRange($contractAlias, $firstToken,$lastToken,$cardId)
    {

        // dd("should bind $tokenId,$cardId");
        if ($cardId == 0 or $cardId == 'unbind')
            $card = $this->sogService->getNullCard();
        else
            $card = $this->sogService->getCardFromId($cardId);


        if (!$card) return response("card not found",404);

        $contract = $this->sogService->getContractByAlias($contractAlias);
        if (! $contract ) return response("Contract $contractAlias not found",404);
        $response = [] ;


        for ($tokenId=$firstToken;$tokenId<=$lastToken;$tokenId++) {
            $standard = ERC721::init($tokenId);
            $response[] = $this->sogService->bindCardToContract($contract, $standard, $card);

        }

        return $response ;


    }



    public function fixSogCards(){

    $this->sogService->fixSogCards();

    }

    public function getCardImage($size,$filename){

        $filePath = storage_path()."/app/public/$size/".$filename;

        $defaultPath = storage_path().'/app/public/'.$filename;

        if ( ! File::exists($filePath) or ( ! $mimeType = getImageContentType($filePath)))
        {
            $filePath = $defaultPath;

            if ( ! File::exists($filePath) or ( ! $mimeType = getImageContentType($filePath))) {

                return Response::make("File does not exist.", 404);
            }
        }

        $fileContents = File::get($filePath);

        return Response::make($fileContents, 200, array('Content-Type' => $mimeType));

    }


}

function getImageContentType($file)
{
    $mime = exif_imagetype($file);

    if ($mime === IMAGETYPE_JPEG)
        $contentType = 'image/jpeg';

    elseif ($mime === IMAGETYPE_GIF)
        $contentType = 'image/gif';

    else if ($mime === IMAGETYPE_PNG)
        $contentType = 'image/png';

    else
        $contentType = false;

    return $contentType;
}
