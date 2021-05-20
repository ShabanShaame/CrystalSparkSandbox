<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 06.11.19
 * Time: 08:51
 */

namespace App;


use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\Counterparty\XcpBlockchain;
use CsCannon\SandraManager;
use SandraCore\CommonFunctions;
use SandraCore\Entity;
use SandraCore\EntityFactory;

class SystemConfig
{
    public $activeBlockchainsEA ;
    public $loaded = false;

    public function load(){

        if (!$this->loaded) {

            $sandra = SandraManager::getSandra();

            //we get active blockchain and network form the datagraph
            $activeBlockchainData = new EntityFactory('activeBlockchain', 'activeBlockchainFile', $sandra);
            $this->activeBlockchainsEA = $activeBlockchainData->populateLocal();


            foreach ($this->activeBlockchainsEA ? $this->activeBlockchainsEA : array() as $activeEntity) {
                $blockchainData = null;

                $network = $activeEntity->get('networkId');
                $blockchain = $activeEntity->get('blockchain');

                $blockchainData['networkId'] = $network;

                $meta['activeBlockchains'][$blockchain][] = $blockchainData;

            }
            $this->loaded = true ;

        }


    }

    /**
     * @return Entity[]
     */
    public function getActiveBlockchains(){

        if (!$this->loaded) $this->load();

        return $this->activeBlockchainsEA ;


    }

    public function toggleActiveBlockchain($name,$active = null){

        $sandra = SandraManager::getSandra();

        $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
        $activeBlockchainData->populateLocal();

        $blockchain = BlockchainRouting::getBlockchainFromName($name);

        $data['blockchain'] = $blockchain::NAME;
        $data['explorerTx'] = $blockchain::getNetworkData('mainet','explorerTx');
        $activeBlockchainData->createOrUpdateOnReference('blockchain',$blockchain::NAME,$data,['onBlockchain'=>$blockchain::NAME]);

        $activeBlockchainData->createViewTable("activeBlockchains");


    }

    public function allowOnlyTestnet($isOnlyTestnet=true){

        $sandra = SandraManager::getSandra();

        $configPoint = new EntityFactory('config','mainConfig',$sandra);
        $configPoint->populateLocal();

        $data['onlyTestnet'] = $isOnlyTestnet ;

        $allReadyTestnet = $configPoint->last('onlyTestnet',1);
        $allReadyNotTestnet = $configPoint->last('onlyTestnet',0);


        if ($allReadyTestnet) $allReadyTestnet->createOrUpdateRef('onlyTestnet',$isOnlyTestnet);
        if ($allReadyNotTestnet) $allReadyNotTestnet->createOrUpdateRef('onlyTestnet',$isOnlyTestnet);
        if (!$allReadyNotTestnet && $allReadyTestnet) $configPoint->createOrUpdateOnReference('onlyTestnet', $data['onlyTestnet'],$data);



    }

    public function isOnlyTestnet(){

        $sandra = SandraManager::getSandra();

        $configPoint = new EntityFactory('config','mainConfig',$sandra);
        $configPoint->populateLocal();

        $configEntity = $configPoint->last('onlyTestnet',1);
        if ($configEntity == null)
            return false;

        if ($configEntity->get('onlyTestnet') == 1)
            return true ;



    }


    public function requireExplicitTrackingForChain(Blockchain $blockchain, $delete = false){

        $sandra = SandraManager::getSandra();

        $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
        $activeBlockchainData->populateLocal();

        $data['requireExplicitContractTrack'] = true ;

        $activeBlockchainData->createOrUpdateOnReference('blockchain',$blockchain::NAME,$data,['onBlockchain'=>$blockchain::NAME]);

    }

    public static function isRequiredExplicit(Blockchain $blockchain){

        $sandra = SandraManager::getSandra();

        $activeBlockchainData = new EntityFactory('activeBlockchain','activeBlockchainFile',$sandra);
        $activeBlockchainData->populateLocal();

        $configEntity = $activeBlockchainData->last('blockchain',$blockchain::NAME);
        if ($configEntity == null)
            return false;

        if ($configEntity->get('requireExplicitContractTrack') == 1)
        return true ;

       return false ;

    }

}