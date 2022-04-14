<?php

namespace Tests\Feature;


use App\Console\Commands\GameAnswers;



use App\Shaban\MainExerciseResponse;
use App\yourNameExercise\MainExercises;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;
use SandraCore\EntityFactory;
use SandraCore\System;


class LocalizationTest extends TestCase
{



    /**
     * Test localization
     *
     * @return void
     */
    public function test_localizationTest()
    {

        $exercice = new MainExercises();
       // $responses = new MainExerciseResponse();

        print_r($exercice->exercise1());

       // $this->assertEquals($exercice->exerciseLocalizedCountries(),$responses->exerciseLocalizedCountries());


        //create  a front end allowing to modify any name in the localisation file

    }

    /**
     * Test localization
     *
     * @return void
     */
    public function test_localizationWithUser()
    {

        $sandra = new System('testgame',true);
        $exercice = new MainExercises();
        $responses = new MainExerciseResponse();

        $peopleFactory = new EntityFactory('person','peopleFile',$sandra);
        $peopleFactory->populateLocal();
        $peopleFactory->populateBrotherEntities();
        $kurt = $peopleFactory->last('firstName','Kurt');
        $daniela = $peopleFactory->last('firstName','Daniela');
        $antoine = $peopleFactory->last('firstName','Antoine');


        print_r($responses->exerciseLocalizedCountries($antoine));

        //$this->assertEquals($exercice->exerciseLocalizedCountries($kurt),$responses->exerciseLocalizedCountries($kurt));




    }

    /**
     * Test localization
     *
     * @return void

    public function test_SQLSimplifiedTable()
    {

        $exercice = new MainExercises();
        $responses = new MainExerciseResponse();

        $this->assertEquals($exercice->exerciseSQLReadableTriplets(),$responses->exerciseSQLReadableTriplets());

    }
      */
}
