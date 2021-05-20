<?php

namespace App\Http\Controllers;

use CsCannon\SandraManager;
use Illuminate\Http\Request;
use SandraCore\Concept;
use SandraCore\ConceptManager;
use SandraCore\DatabaseAdapter;

class SearchController extends Controller
{


    public function search($string)
    {
        $sandra = SandraManager::getSandra();
        $concepts = DatabaseAdapter::searchConcept($string, null, $sandra);

        foreach ($concepts ?? array() as $conceptId) {


        $concept = new Concept($conceptId, $sandra);
        $triplets = $concept->getConceptTriplets();

        }


        dd($triplets);

    }

    public function returnNavigationalSearch(){






    }

}
