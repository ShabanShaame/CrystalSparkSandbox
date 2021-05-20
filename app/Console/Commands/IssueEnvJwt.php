<?php

namespace App\Console\Commands;

use App\Http\Controllers\EnvController;
use App\Services\JWTService;
use Illuminate\Console\Command;

class IssueEnvJwt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:issuejwt {env} {--flushable}';

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

        $gossipKey = env("GOSSIPER_KEY");

        $envController = new EnvController();
        $env = $this->argument('env');
        $flushable = $this->option('flushable');
        $jwt = JWTService::issueServiceJwt(['env'=>$env,'flush'=>$flushable],$gossipKey,time()*60*60*180);



        $this->line("flushable ? $flushable");


        if(!$envController->envExist($env)){

            $envController->create($env);}


        $sandra = $envController->enterEnv($env);

        if ($flushable) $this->line(" flushable ".$flushable);

        $this->info($jwt);



        echo "$sandra->env";




    }
}
