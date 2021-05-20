<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 12.05.20
 * Time: 11:36
 */

namespace App\Services\Factories;


use App\Services\Entities\PoolAddressEntity;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainAddressFactory;
use CsCannon\Blockchains\Generic\GenericAddressFactory;
use CsCannon\Blockchains\SignableBlockchainAddress;

class PoolAddressFactory extends GenericAddressFactory
{

    public static $file = 'blockchainAddressFile';
    private $nativeAddressFactory = null ;
    protected static $className = PoolAddressEntity::class ;

    public const CONTROLLED_BY = 'controlledBy';
    public const CONTROLLED_BY_SYSTEM = 'system';
    public const HAS_PATH = 'hasPath';


    public function populateFromBlockchain(Blockchain $blockchain, $path = null,$limit = 100,$offset = 0,$asc='ASC'){

        $isa = $blockchain->getAddressFactory()->entityIsa ;
        $this->nativeAddressFactory = $blockchain->getAddressFactory();

       $this->setFilter('is_a',$isa);
       $this->setFilter(self::CONTROLLED_BY,self::CONTROLLED_BY_SYSTEM);

       if ($path)
           $this->setFilter(self::HAS_PATH,$path);

       $populated = $this->populateLocal($limit,$offset,$asc);

       $this->setBlockchain($blockchain);
       return $populated;


    }



    public function saveSignableAddress(SignableBlockchainAddress $signable){

        if($signable->address->isForeign()) $this->system->systemError("",self::class,4,"Signable address not stored locally ".$signable->address->getAddress());

        $controller = $signable->address->setBrotherEntity(self::CONTROLLED_BY,self::CONTROLLED_BY_SYSTEM,null);
        if($controller->getStorage()) $this->system->systemError("",self::class,4,"Private key Exist refuse to ovveride for address ".$signable->address->getAddress());

        $controller->setStorage(encrypt($signable->getPrivateKey()));


    }

    private function setBlockchain(Blockchain $blockchain){

        foreach ($this->getEntities() as $entity){

            $entity->setKnownBlockchain($blockchain);


        }


    }

}