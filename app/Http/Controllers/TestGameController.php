<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SandraCore\EntityFactory;
use SandraCore\System;

class TestGameController extends Controller
{
    public function getPeople(){

        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));

        $peopleFactory = new EntityFactory('person','peopleFile',$sandra);
        $cityFactory = new EntityFactory('city','generalCityFile',$sandra);
        $peopleFactory->populateLocal();
        $peopleFactory->populateBrotherEntities();


        $peopleFactory->joinFactory('bornIn',$cityFactory);

        $antoine = $peopleFactory->last('firstName','Antoine');
        $arrayOfStrings = $antoine->getBrotherReference('bornInCity',null,'year');


        //assuming it has a born in city relation
        if (!empty($arrayOfEntities)){
            $bornIn = end($arrayOfEntities); //last entity of the array (it may have multiple bornInCity relation
           $bornYear = $bornIn->get('year'); // will return 1902
        }

        return $peopleFactory->dumpMeta();



    }
}
