<?php

namespace App\Console\Commands;

use App\yourNameExercise\MainExercises;
use Illuminate\Console\Command;

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


    }
}
