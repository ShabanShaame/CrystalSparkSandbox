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
        $peopleFactory->populateLocal();

        return $peopleFactory->dumpMeta();



    }
}
