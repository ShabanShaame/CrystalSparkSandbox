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
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainBlockFactory;
use CsCannon\Blockchains\BlockchainContractFactory;
use SandraCore\DatabaseAdapter;
use SandraCore\System;

class BlockService
{

    public $blockchain = null ;

    public function getNotTimestampedBlocks(Blockchain $blockchain){

        $sandra = app('Sandra')->getSandra();
        /** @var System $sandra */
        $blockFactory = new BlockchainBlockFactory($blockchain);
        $blockfileUnid = $sandra->systemConcept->get($blockFactory->file);
        $this->blockchain = $blockchain;

      $blocks =  DatabaseAdapter::searchConcept('1',$sandra->systemConcept->get(BlockchainBlockFactory::BLOCK_TIMESTAMP),$sandra,'',$blockfileUnid,
           1000 );

     if ($blocks == null) return ["status"=>'done',"message"=>"No futher blocks"];
        $blockFactory->conceptArray = $blocks ;
        $blockFactory->populateLocal();
       $entities = $blockFactory->getEntities();

       $entityArray = $blockFactory->return2dArray() ?? array();

       foreach ($entityArray as $entity){

           $blockArray[] = $entity[BlockchainBlockFactory::INDEX_SHORTNAME] ;
       }

       $results['data'] = $this->sendToNode($blockArray);
        $results['status'] = "more";


       return $results ;


    }

    private function sendToNode(array $blockList){

        $blockListString = json_encode($blockList);


        $cmd = 'node '. "app/Node/EVM/BlockReader.js --blockList=".$blockListString. "";
        $response =  exec($cmd.">&1", $output, $return_var);
        /* Response format
        [0] => 5251724,1521000408
        [1] => 5251780,1521001305
        */

        $output = $this->updateBlocksTimestamp($output);

        return $output ;



    }

    private function updateBlocksTimestamp(array $blockList){

        $response = array();
        $blockFactory = new BlockchainBlockFactory($this->blockchain);

        foreach ($blockList ?? array() as $blockData){



            $blockData = explode(",",$blockData);
            $blockId = $blockData[0];
            $blockTime = $blockData[1] ;

            $block = $blockFactory->get($blockId);
            $block->setTimestamp($blockTime);



            $blockArray['id'] =$blockId;
            $blockArray['time'] =$blockTime;
            $response[] = $blockArray ;


        }

        return $response ;



    }





}