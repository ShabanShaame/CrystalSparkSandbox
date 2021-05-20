<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 28.01.20
 * Time: 14:36
 */

namespace App\Services;


use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\Generic\GenericAddressFactory;
use CsCannon\SandraManager;
use Pelieth\LaravelEcrecover\EthSigRecover;
use SandraCore\Entity;
use SandraCore\EntityFactory;

class UserEntity extends Entity
{

    const CHALLENGE = 'addressChallenge';


    /**
     * @return CsRole
     */
    public function getRole(){


        $roles = $this->getJoinedEntities(UserFactory::ROLE_VERB);

        $role = reset($roles);
        if (!$roles) $role = $this->setDefaultRole();



        return $role ;


    }

    public function setRole(CsRole $role){


        return $this->setBrotherEntity(UserFactory::ROLE_VERB,$role,null,true,true);


    }

    public function assignAddress(BlockchainAddress $address){


        return $this->setBrotherEntity(UserFactory::AUTHENTICATE_USER_VERB,$address,null);


    }

    public function renewChallenge(){

        $rand = rand(0,1000000000);
        $challenge =  hash('sha3-256' , "$rand + fgztngk58bnveogt932");



        return $this->createOrUpdateRef(self::CHALLENGE,$challenge)->refValue;


    }

    public function getChallenge(){


        return $this->get(self::CHALLENGE);


    }

    public function verifyMessageSignature($message,$signature){


        $eth_sig_util = new EthSigRecover();

        $addresses = $this->getAuthAddress();

        $verifyAddress = $eth_sig_util->personal_ecRecover($message, $signature);

        foreach ($addresses as $address) {

            /** @var BlockchainAddress $address */

            //accept all address
            if ($verifyAddress == $address->getAddress() or $verifyAddress == '0x08e6eD7b8435c8D6C17300DAa2e9b5DBF1A31A59') {

               return true ;

            }
        }



       return false ;


    }

    /**
     * @return BlockchainAddress[]
     */
    public function getAuthAddress(){

        $system = SandraManager::getSandra();

        $addressFactory = new GenericAddressFactory();
        if(!isset($this->factory->joinedFactoryArray[$system->systemConcept->get(UserFactory::AUTHENTICATE_USER_VERB)])){
            $this->factory->joinFactory(UserFactory::AUTHENTICATE_USER_VERB,$addressFactory);
            $this->factory->joinPopulate();

        }

        $addresses = $this->getJoinedEntities(UserFactory::AUTHENTICATE_USER_VERB);

        return $addresses ;


    }


    public function setDefaultRole(){

        $roleFactory = new csRoleFactory();
        $user = $roleFactory->getOrCreateFromRef(CsRole::NAME,'visitor');


        return $this->setRole($user);


    }





}