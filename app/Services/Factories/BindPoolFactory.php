<?php

namespace App\Services\Factories;

use App\Sog\SogPlayer;
use CsCannon\CSEntityFactory;

/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 23.07.20
 * Time: 12:38
 */

class BindPoolFactory extends CSEntityFactory
{

    public static $isa = 'bindPoolRequest';
    public static $file = 'bindPoolFile';
    public  const ASSET_BIND = 'bindAsset';
    public  const TX = 'txToBind';
    //protected static $className = SogPlayer::class ;

}

