<?php

namespace App\Services\Factories;

use App\Sog\SogPlayer;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\Blockchain;
use CsCannon\CSEntityFactory;

/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 23.07.20
 * Time: 12:38
 */

class jetskiFactory extends CSEntityFactory
{

    public static $isa = 'jetskiRun';
    public static $file = 'jetskiRunFile';
    public const LATEST_JETSKI = 'latest_' ;//append blockchain name
    public const JETSKI_INSTANCE = 'instanceName';
    public const  LATEST_BLOCK = 'latestBlock';
    //protected static $className = SogPlayer::class ;

    public static function getLast(Blockchain $blockchain){

        $jetskiFactory = new jetskiFactory();

       return $jetskiFactory->last(self::JETSKI_INSTANCE,self::LATEST_JETSKI.$blockchain::NAME);

    }



}

