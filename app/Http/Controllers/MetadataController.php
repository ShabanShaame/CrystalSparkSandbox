<?php

namespace App\Http\Controllers;


use App\ResponseService;
use CsCannon\AssetCollection;
use CsCannon\AssetCollectionFactory;
use CsCannon\AssetSolvers\AssetSolver;
use CsCannon\AssetSolvers\PathPredictableSolver;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC721;
use CsCannon\Blockchains\Generic\GenericContractFactory;
use CsCannon\MetadataSolverFactory;
use CsCannon\SandraManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use  Illuminate\Http\Request ;

class etadataController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function getMetadata($collectionId,$tokenId = null)
    {



            $extension = '.jpg';
            $tokenId = 1422 ;

            $mainImageURL = 'https://api.moonga.com/RCT/cp/cards/view/normal/large/en/'.$tokenId.$extension ;

            $output['description'] = "Shapeshift the wanderer";
            $output['external_url'] = "https://www.spellsofgenesis.com/$tokenId";
            $output['image'] = $mainImageURL;
            $output['name'] = "Shapeshift the wanderer ";



            //$mimeType = self::get_mime_type($mainImageURL);
           // header("Content-type: $mimeType");

            return response()->json($output, 200);










        $collection = $this->getCollectionEntityOrDie($collectionId);

        $contractFactory = new GenericContractFactory();
        $contractFactory->setFilter(0,$collection);

        $contracts = $contractFactory->populateLocal();
        $contract = end($contracts);
        /** @var BlockchainContract $contract */
        $standard = $contract->getStandard();
        $standard->setTokenPath(array("tokenId"=>$tokenId));


        //we need to get the contract




        $url = $collection->getBrotherReference(AssetCollectionFactory::METADATASOLVER_VERB,
            AssetCollectionFactory::METADATASOLVER_TARGET,'simpleURLAppend');

        $extension = $collection->getBrotherReference(AssetCollectionFactory::METADATASOLVER_VERB,
            AssetCollectionFactory::METADATASOLVER_TARGET,AssetCollectionFactory::IMAGE_EXTENSION);

        $solvers = $collection->getSolvers();

        foreach ($solvers as $solver){

            $asset = $solver::resolveAsset($collection,$standard,$contract);

        }



        //we we don't have image solver
        if((is_null($extension) or is_null($tokenId)) && !is_null($collection)){

            $mainImageURL = $collection->get(AssetCollectionFactory::MAIN_IMAGE);
            $mimeType = self::get_mime_type($mainImageURL);
            header("Content-type: $mimeType");

            readfile($mainImageURL);
            exit;
        }





        $mimeType = self::get_mime_type($url.$tokenId.$extension);
header("Content-type: $mimeType");
readfile($url.$tokenId.$extension);



            exit;


    }



    public function getImageSolver($collectionId, Request $request)
    {


        $collection = $this->getCollectionEntityOrDie($collectionId);



        $solver = $collection->getSolvers();
        $solver = end($solver);

        $solverData = $collection->getBrotherEntity(AssetCollectionFactory::METADATASOLVER_VERB,$solver);
        /** @var AssetSolver $solver */

        return response()->json($solverData->dumpMeta(), 200);


    }

    public function setImageSolver($collectionId, Request $request)
    {
        $metaDasolverFactory = new MetadataSolverFactory(SandraManager::getSandra());


        if (!$request->has('type')) {
            $type = 'simpleURLAppend';
        }



        if (!$request->has('url')) {
            abort(404,'URL required');
        }

        $url = $request->input('url');
        $imgExt = $request->input('imageExtension');






        $collection = $this->getCollectionEntityOrDie($collectionId);

        $data[$type] = $url ;
        $data[AssetCollectionFactory::IMAGE_EXTENSION] = $imgExt ;

        $collection->setBrotherEntity(AssetCollectionFactory::METADATASOLVER_VERB,
            AssetCollectionFactory::METADATASOLVER_TARGET,$data);




        return response()->json($url, 200);
        exit;


    }

    public function setMetadataSolver($collectionId,Request $request){

        $responseService = new ResponseService();

        if (!$request->has('type')) {
            $response = $responseService->prepareError(403,'type is required');
            return response()->json($response, 403);
        }

        $collection = $this->getCollection($collectionId);

        if(is_null($collection)){
            $response = $responseService->prepareError(404,"collection : $collectionId not found");
            return response()->json($response, 404);

        }

        //compatible solvers
        PathPredictableSolver::getEntity();



        $metaDatasolver = new MetadataSolverFactory(SandraManager::getSandra());
        $solver = $metaDatasolver->getSolverWithIdentifier($request->type);
        //print_r($metaDatasolver->return2dArray());

        if(is_null($solver)){
            $response = $responseService->prepareError(404,"solver $request->type not found");
            return response()->json($response, 404);

        }

        $solver->setAdditionalParam($request->all());
        $solver->filterAndSetParam($request->all());

        $solver->validate();





        $collection->setSolver($solver);

        $responseData['mySolve'] = $solver->additionalSolverParam;
        $responseData['entity'] = $solver->entityId;
        $response = $responseService->prepare($responseData);
        return response()->json($response);






    }


    private function getCollectionEntityOrDie($collectionId) {

        $collectionFactory = new AssetCollectionFactory(SandraManager::getSandra());
        $collectionFactory->populateLocal();
        $collectionFactory->populateBrotherEntities(AssetCollectionFactory::METADATASOLVER_VERB);
        $collectionEntity = $collectionFactory->get($collectionId);


        if (is_null($collectionEntity)){
            abort(404,'collection not found');
        }

        return $collectionEntity ;
}

    private function getCollection($collectionId):?AssetCollection {

        $collectionFactory = new AssetCollectionFactory(SandraManager::getSandra());
        $collectionFactory->populateLocal();
        $collectionFactory->populateBrotherEntities(AssetCollectionFactory::METADATASOLVER_VERB);
        $collectionEntity = $collectionFactory->get($collectionId);


        return $collectionEntity ;
    }


    function get_mime_type($filename) {
        $idx = explode( '.', $filename );
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode-1]);

        $mimet = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset( $mimet[$idx] )) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }

}
