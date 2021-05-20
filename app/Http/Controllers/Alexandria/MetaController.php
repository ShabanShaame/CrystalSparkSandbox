<?php

namespace App\Http\Controllers\Alexandria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EnvController;
use App\ResponseService;
use App\Services\Factories\WithdrawRequestEntityFactory;
use App\Services\JWTService;
use CsCannon\AssetCollectionFactory;
use CsCannon\Blockchains\Substrate\Kusama\KusamaEventFactory;
use CsCannon\SandraManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InnateSkills\Gossiper\Gossiper;

class MetaController extends Controller
{

    public function getViews($context = null)
    {


        $tables[] = ['table' => 'withdrawRequests', 'name' => 'Withdraw Request'];
        $tables[] = ['table' => 'AssetCollections2', 'name' => 'Asset Collection 2'];
        //$tables[]= ['table'=>'Events','name'=>'Blockchain Events'];
        $tables[] = ['table' => 'sog-eth-askian', 'name' => 'Sog Binds eth'];
        $tables[] = ['table' => 'sog-klay-firstOasis', 'name' => 'Sog Binds First Oasis'];

        return $tables;

    }

    public function listenGossip(Request $request)
    {

        $envController = new EnvController();
        $sandra = $envController->enterEnv('gossip');

        $gossiper = new Gossiper($sandra);
        $json = file_get_contents('php://input');

        $gossiper->receiveEntityFactory($json);
        return $gossiper->equalRefCount;


    }

    public function listenGossipAuth(Request $request)
    {

        $gossipKey = env("GOSSIPER_KEY");

        $jwt = $request->get('jwt') ?? '';

        try {
            $jwtDecoded = JWTService::verifyServiceJwt($jwt, $gossipKey);
        } catch (\Exception $e) {

            $responseService = new ResponseService();
            $responseService->prepareError('400','Invalid jwt '. $e->getMessage());
            return $responseService->response;

        }
        if (!$jwtDecoded->env) {
            $responseService = new ResponseService();
            $responseService->prepareError(400, 'Invalid jwt');
        }

        $envController = new EnvController();
        try {
            $sandra = $envController->enterEnv($jwtDecoded->env);
        } catch (\Exception $e) {
            $responseService->prepareError(400, $e->getMessage());
            return $responseService->response;
        }

        if ($this->flushRequest($request, $jwtDecoded)) return "succes";
        try {
            $gossiper = new Gossiper($sandra);
            $json = file_get_contents('php://input');
            $gossiper->receiveEntityFactory($json);
        } catch (\Exception $e) {
            $responseService = new ResponseService();
            $responseService->prepareError(400, 'could not decode json gossip');
            return $responseService->response;
        }


        $responseService = new ResponseService();


        $entityFactory = $gossiper->receiveEntityFactory($json);
        return $this->gossipResponse($gossiper, $responseService);

    }

    private function flushRequest(Request $request, $decodedJwt)
    {

        if ($request->get('flush') == true) {
            $responseService = new ResponseService();
            //if the user has the right to flush
            if ($decodedJwt->flush === true) {
                $sandra = SandraManager::getSandra();
                if ($sandra->env != 'baster' && $decodedJwt->env == $sandra->env) //hard stop security
                {
                    \SandraCore\Setup::flushDatagraph($sandra);
                    $envController = new EnvController();
                    $envController->create($decodedJwt->env);

                    $sandra = $envController->enterEnv($decodedJwt->env);

                    Log::info("flushing $decodedJwt->env");
                    return true;
                }

                Log::info("flushing failed $decodedJwt->env");
                $responseService->prepareError(400, 'could not flush');;
                die(json_encode($responseService->response));

            }
            Log::info("no right to flush $decodedJwt->env");
            $responseService->prepareError(400, 'no right to flush');;
            die(json_encode($responseService->response));


        }

        return false;

    }


    private function gossipResponse(Gossiper $gossiper, ResponseService $responseService)
    {

        $data['triplets']['new'] = $gossiper->rawNewTripletCount;
        $data['refs']['updated'] = $gossiper->updateRefCount;
        $data['refs']['created'] = $gossiper->createRefCount;

        return $responseService->prepare($data)->getFormattedResponse();


    }


    private function hasErrorInEnvRouting(Request $request){

        $gossipKey = env("GOSSIPER_KEY");
        $jwt = $request->get('jwt') ?? '';

        try {
            $jwtDecoded = JWTService::verifyServiceJwt($jwt, $gossipKey);
        }catch (\Exception $e){

            $responseService = new ResponseService();
            $responseService->prepareError('400','Invalid jwt: '. $e->getMessage());
            return $responseService->response ;

        }
        if (!$jwtDecoded->env) {
            $responseService = new ResponseService();
            $responseService->prepareError(400, 'Invalid jwt');
        }

        $envController = new EnvController();
        try {
            $sandra = $envController->enterEnv($jwtDecoded->env);
        }catch (\Exception $e){ $responseService->prepareError(400,$e->getMessage()); return $responseService->response;}



       return null ;



    }






    public function exposeGossip(Request $request){

        $errorResponse = $this->hasErrorInEnvRouting($request);
        if ($errorResponse)return $errorResponse ;

        $assetCollectionFactory = new KusamaEventFactory();
        $assetCollectionFactory->populateLocal(1);
        $gossiper = new Gossiper(SandraManager::getSandra());
        return $gossiper->exposeGossip($assetCollectionFactory);




}
}
