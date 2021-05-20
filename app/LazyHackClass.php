<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 07.01.20
 * Time: 09:40
 */

namespace App;


use CsCannon\Balance;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Ethereum\EthereumAddress;
use CsCannon\Blockchains\Klaytn\KlaytnAddressFactory;
use CsCannon\Blockchains\Substrate\Kusama\KusamaAddressFactory;
use CsCannon\Blockchains\Substrate\Kusama\KusamaBlockchain;
use SandraCore\Entity;
use SandraCore\System;

/**
 * Class LazyHackClass
 *
 * The class of the shame. We put here every lazy code hack we are ashamed of. Instead of hacking directely in the code
 * calling this class instead. This way it will be easy to see and debug where there are still used hack in production
 *
 * @package App
 */
class LazyHackClass extends Entity {




    public static function hackToIntegrateKlaytnBalance(BlockchainAddress $address, Balance $balance):Balance{


        if ($address instanceof EthereumAddress){
            $klaytnAddressFactory = new KlaytnAddressFactory();
            $klaytnAddress = $klaytnAddressFactory->get($address->getAddress());
            if (!$klaytnAddress->isForeign()){
                $balance->merge($klaytnAddress->getBalance());

            }

        }

        return $balance ;

    }

    public static function hackToFixOnBlockchainForKusama(string $address,System $sandra){



        if ($sandra->env == 'gossip' or $sandra->env == 'ksmjetski' or $sandra->env == 'cervin'){


            $address = KusamaAddressFactory::getAddress($address,false);
            $address->setBrotherEntity(BlockchainContractFactory::ON_BLOCKCHAIN_VERB,KusamaBlockchain::NAME,[]);

        }



    }



}
