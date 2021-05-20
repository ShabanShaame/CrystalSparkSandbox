<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 18.06.19
 * Time: 15:41
 */

namespace App;


use SandraCore\EntityFactory;
use SandraCore\System;

class EnvRouting
{
    CONST ENV_IDENTIFIER = 'envId';
    CONST ENV_ENTITY = 'dataGraph';
    CONST ENV_FILE = 'dataGraphFile';

    public static function getCurrentSandraEnv(){




        if(isset($_ENV["SANDRAENV"])) {
            $currentEnv = $_ENV["SANDRAENV"];
        }



        $sandraRouting = new System('routingDatagraph',true,Config('database.connections.mysql.host'),
            Config('database.connections.mysql.database'),
            Config('database.connections.mysql.username'),
            Config('database.connections.mysql.password'));

            $envFactory = new EntityFactory(self::ENV_ENTITY,self::ENV_FILE,$sandraRouting);
            $envFactory->setGeneratedClass(SandraEnv::class);

            $envFactory->populateLocal();

            if($envFactory->first(self::ENV_IDENTIFIER,$currentEnv)){

                $sandra = new System($currentEnv,true,Config('database.connections.mysql.host'),
                    Config('database.connections.mysql.database'),
                    Config('database.connections.mysql.username'),
                    Config('database.connections.mysql.password'));

                app('Sandra')->setSandra($sandra);




            return $sandra;

            }



             $sandra = new System($currentEnv,true,Config('database.connections.mysql.host'),
            Config('database.connections.mysql.database'),
            Config('database.connections.mysql.username'),
            Config('database.connections.mysql.password'));

            $envFactory->getOrCreateFromRef(self::ENV_IDENTIFIER,$currentEnv);

            app('Sandra')->setSandra($sandra);



            return $sandra;



    }

    public static function isCustomEnv(){

        if(isset($_ENV["SANDRAENV"])) {

        return true ;
        }

        return false ;


    }


    public static function getMetaDatagraph(){

        if(isset($_ENV["SANDRAENV"])) {

            return true ;
        }

        return false ;


    }


}