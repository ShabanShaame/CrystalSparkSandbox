<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.03.2019
 * Time: 20:45
 */

namespace App;





use CsCannon\SandraManager;
use SandraCore\EntityFactory;

class ResponseService
{

    public  $headArray ;
    public  $dataArray ;
    public  $response ;



    public function __construct()
    {


    }

    public function buildMeta(Array $head=null){

        $sandra = SandraManager::getSandra();

        $meta['server'] = env('SERVER_NAME');
        $meta['env'] = $sandra->tablePrefix ;
        $meta['version'] = '0.4.0';

        $activeBlockchains = app('SystemConfig')->getActiveBlockchains();



        foreach ($activeBlockchains ? $activeBlockchains : array() as $activeEntity){
            $blockchainData = null ;

            $network = $activeEntity->get('networkId');
            $blockchain = $activeEntity->get('blockchain');
            $txExplorer = $activeEntity->get('explorerTx');

            $blockchainData['networkId'] = $network;
            $blockchainData['explorerTx'] = $txExplorer;

            $meta['activeBlockchains'][$blockchain] = $blockchainData;

        }



        if (is_array($head))
        $meta = $meta + $head ;

        return $meta ;

    }

    public function prepare(Array $data,Array $head=null)
    {

        $this->response['meta'] = $this->buildMeta($head);

    $this->response['data'] = $data ;




        return  $this ;
    }

    public function  getFormattedResponse()
    {

        return  $this->response ;
    }

    public function  prepareError($code,$info,$head=null)
    {

        $this->response['error']['code'] =$code;
        $this->response['error']['message'] =$info;
        $this->response['meta'] = $this->buildMeta($head);

        return  $this->response ;
    }







}
