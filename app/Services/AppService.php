<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 14.02.20
 * Time: 14:15
 */

namespace App\Services;


class AppService
{
    public $appFactory = null ;

    public function __construct()
    {
        $this->appFactory = new AppEntityFactory();


    }

    public function getOrCreateApp($name):AppEntity{


        $app = $this->appFactory->getOrCreateFromRef('id',$name);

        return $app ;


    }


}