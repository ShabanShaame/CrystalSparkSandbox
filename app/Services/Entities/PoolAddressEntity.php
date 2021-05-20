<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 16.06.20
 * Time: 17:23
 */

namespace App\Services\Entities;


use App\Services\Factories\PoolAddressFactory;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainDataSource;
use CsCannon\Blockchains\Generic\GenericAddress;
use CsCannon\Blockchains\Generic\GenericBlockchain;
use CsCannon\Blockchains\SignableBlockchainAddress;
use CsCannon\SandraManager;
use SandraCore\Entity;
use SandraCore\System;


class PoolAddressEntity extends GenericAddress
{

    protected static  $className = 'App\Services\Entities\PoolAddressEntity' ;

    /**
     * Blockchain when known
     * @var Blockchain
     */
    public $knownBlockchain ;

    public function __construct($sandraConcept, $sandraReferencesArray, $factory, $entityId, $conceptVerb, $conceptTarget, System $system)
    {

        parent::__construct($sandraConcept, $sandraReferencesArray, $factory, $entityId, $conceptVerb, $conceptTarget, $system);
        //we get the isa information
        $triplets = $this->subjectConcept->getConceptTriplets();
        $isa = $triplets[$system->systemConcept->get('is_a')] ;
        //first isa only
        $isa = reset($isa);

    }


    public function getSignableAddress():SignableBlockchainAddress{

        $privateKey =  $this->getBrotherEntity(PoolAddressFactory::CONTROLLED_BY,PoolAddressFactory::CONTROLLED_BY_SYSTEM);

        if (!$privateKey) $this->system->systemError("",self::class,3,"no private key for address");
        $privateString = $privateKey->getStorage();

        try {
            $signable = new SignableBlockchainAddress($this, decrypt($privateString));
        }catch (\Exception $exception){

            $signable = new SignableBlockchainAddress($this, "nope");
        }

        return $signable ;

    }

    public function setKnownBlockchain(Blockchain $blockchain){

        $this->knownBlockchain = $blockchain ;
    }





    public function getBlockchain(): Blockchain
    {
        if ($this->knownBlockchain) return $this->knownBlockchain ;
        return GenericBlockchain::getStatic();
    }



    public function getDefaultDataSource(): BlockchainDataSource

    {


        if ($this->knownBlockchain) return $this->knownBlockchain->getAddressFactory()->get("any")->getDefaultDataSource();


        return  new \CsCannon\Blockchains\DataSource\DatagraphSource();
    }

}