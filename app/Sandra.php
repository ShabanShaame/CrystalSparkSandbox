<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.03.2019
 * Time: 20:45
 */

namespace App;



use CsCannon\SandraManager;
use SandraCore\System;

class Sandra
{

    public static $sandra ;
    public static $metaSandra ;
    public static $env ;
    public static $routingDataGraph ;


    public function __construct()
    {






        return  self::$sandra ;
    }

    public function getSandra($autoInstall=true){

        if(!isset(self::$sandra)){




            if (EnvRouting::isCustomEnv()){
                EnvRouting::getCurrentSandraEnv();
                return self::getSandra();

            }

            //self::$sandra = new System(env('SANDRA_ENV', ''),1,Config('database.connections.mysql.host'),
            self::$sandra = new System(env('SANDRA_ENV'),$autoInstall,Config('database.connections.mysql.host'),
                Config('database.connections.mysql.database'),
                Config('database.connections.mysql.username'),
                Config('database.connections.mysql.password'));
/*
            //self::$sandra = new System(env('SANDRA_ENV', ''),1,Config('database.connections.mysql.host'),
           self::$sandra = new System('shab',1,Config('database.connections.mysql.host'),
               Config('database.connections.mysql.database'),
               Config('database.connections.mysql.username'),
               Config('database.connections.mysql.password'));*/


        }

        SandraManager::setSandra(self::$sandra);
        return self::$sandra;
    }

    public function flushAndGetUnitTestSandra($autoInstall=true){



            //self::$sandra = new System(env('SANDRA_ENV', ''),1,Config('database.connections.mysql.host'),
            $sandraUnit = new System(env('phpUnit'),$autoInstall,env('DB_HOST_UNIT'),
                env('DB_DATABASE_UNIT'),
                env('DB_USERNAME_UNIT'),
                env('DB_PASSWORD_UNIT')
                );

            //security check
            if($sandraUnit->env == 'phpUnit') {
                \SandraCore\Setup::flushDatagraph($sandraUnit);
            }

        $sandraUnit = new System(env('phpUnit'),$autoInstall,env('DB_HOST_UNIT'),
            env('DB_DATABASE_UNIT'),
            env('DB_USERNAME_UNIT'),
            env('DB_PASSWORD_UNIT')
        );

        self::$sandra = $sandraUnit;



        SandraManager::setSandra(self::$sandra);
        return self::$sandra;
    }

    public function setSandra(System $sandra){

        self::$sandra = $sandra;
        SandraManager::setSandra($sandra);
        return $sandra ;
    }

    public function getMetaSandra($autoInstall=true){

       // if(!isset(self::$metaSandra)){

            self::$metaSandra =  $sandraRouting = new System('routingDatagraph',$autoInstall,Config('database.connections.mysql.host'),
                Config('database.connections.mysql.database'),
                Config('database.connections.mysql.username'),
                Config('database.connections.mysql.password'));

       // }

        return self::$metaSandra ;

    }


    public function setEnvEntity(SandraEnv $env){

        self::$env = $env;
        return $env ;
    }



}