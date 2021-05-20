<?php

namespace App\Console\Commands\Jetski;

use App\ProcessFactory;
use App\Services\JetskiService;
use ErrorException;
use Illuminate\Console\Command;
use InnateSkills\SandraHealth\MemoryManagement;
use SandraCore\EntityFactory;

class RemoteJetski extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remoteJetski:status {env?} ';

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
     * @return mixed
     */
    public function handle()

    {
        $sandra = $this->metaSandra = app('Sandra')->getSandra();

        $jettskiFactory = new EntityFactory("jetskiRun","jetskiRunFile",$sandra);
        $jettskiFactory->populateLocal();

        dd($jettskiFactory->dumpMeta());






    }








}


