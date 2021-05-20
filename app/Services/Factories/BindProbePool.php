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

class BindProbePool extends CSEntityFactory
{

    public static $isa = 'bindProbe';
    public static $file = 'bindProbeFile';
    public  const TOKEN_ID = 'tokenId';

    //protected static $className = SogPlayer::class ;

}

