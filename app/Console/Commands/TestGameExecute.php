<?php

namespace App\Console\Commands;

use App\Shaban\MainExerciseResponse;
use App\Guillermo\MainExercises;
use Illuminate\Console\Command;
use SandraCore\EntityFactory;
use SandraCore\System;

class TestGameExecute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testgame:execute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $mainExercise = new MainExercises();

        print_r($mainExercise->exercise1());

        print_r($mainExercise->exercise2());


        print_r($mainExercise->exercise3('french'));
        print_r($mainExercise->exercise3('english'));
        print_r($mainExercise->exercise3('german'));

        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));


        $peopleFactory = new EntityFactory('person','peopleFile',$sandra);
        $peopleFactory->populateLocal();
        $peopleFactory->populateBrotherEntities();
        $kurt = $peopleFactory->last('firstName','Kurt');
        $daniela = $peopleFactory->last('firstName','Daniela');
        $antoine = $peopleFactory->last('firstName','Antoine');

        print_r($mainExercise->exercise4($kurt));
        print_r($mainExercise->exercise4($daniela));
        print_r($mainExercise->exercise4($antoine));

    }
}
