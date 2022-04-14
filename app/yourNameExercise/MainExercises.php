<?php


namespace App\yourNameExercise;


use App\shaban\SqlResponses;
use Illuminate\Support\Facades\DB;
use SandraCore\Entity;
use SandraCore\EntityFactory;
use SandraCore\System;

class MainExercises
{



    public function exercise1(string $favoriteLanguage = null):array{




        echo env('DB_HOST');
        //initialise datagraph to recreate tables
        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));



        //create an array with all cities names with their respective countries
            $response[] = "City Name".' - '."Country name";


        return $response ;

    }

    public function exercise2(Entity $entityToFindLanguage = null):array{

        //initialise datagraph to recreate tables
        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));

        //create an array with all cities names with their respective countries
        $response[] = "City Name".' - '."Country name";


        return $response ;

    }






}
