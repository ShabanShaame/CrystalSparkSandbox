<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 14.02.20
 * Time: 14:17
 */

namespace App\Services;


use CsCannon\CSEntityFactory;

class AppEntityFactory extends CSEntityFactory
{

    public static $isa = 'app';
    public static $file = 'appFile'; //this has to be set in child class or will raise error
    protected static $className = AppEntity::class ;


}