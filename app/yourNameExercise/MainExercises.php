<?php


namespace App\yourNameExercise;


use App\shaban\SqlResponses;
use Illuminate\Support\Facades\DB;
use SandraCore\Entity;
use SandraCore\EntityFactory;
use SandraCore\System;

class MainExercises
{

    public function exercise1():array{

        //complete the SQL statement please keep column names
        return DB::select( "SELECT t.id AS entity_id, t.idConceptStart AS Subject, t.idConceptLink AS Verb,
       t.idConceptTarget AS Target FROM testgame_SandraTriplets t" );


    }

    public function exercise2():array{

        //complete the SQL statement please keep column names
        return DB::select( "SELECT t.id AS entity_id, t.idConceptStart AS Subject, t.idConceptLink AS Verb,
       t.idConceptTarget AS Target FROM testgame_SandraTriplets t" );


    }

    public function exercise3(string $favoriteLanguage = null):array{


        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));

        //create an array with all cities names with their respective countries
            $response[] = "City Name".' - '."Country name";

        return $response ;

    }

    public function exercise4(Entity $entityToFindLanguage = null):array{

        //initialise datagraph to recreate tables
        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));

        //create an array with all cities names with their respective countries
        $response[] = "City Name".' - '."Country name";



        return $response ;

    }

}
