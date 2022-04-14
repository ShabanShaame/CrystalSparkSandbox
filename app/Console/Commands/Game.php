<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SandraCore\EntityFactory;
use SandraCore\Setup;
use SandraCore\System;

class Game extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:start';

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

        //initialize datagraph to flush it
        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));
        Setup::flushDatagraph($sandra);

        //initialise datagraph to recreate tables
        $sandra = new System('testgame',true,env('DB_HOST'),env('DB_DATABASE'),
            env('DB_USERNAME'),env('DB_PASSWORD'));


        $cityFactory = new EntityFactory('city','generalCityFile',$sandra);

        //add annoying insects
        $this->addAnnoyingInsects($sandra);


        //create cities
        $london = $cityFactory->createNew(['name'=>'London']);
        $delhi = $cityFactory->createNew(['name'=>'Delhi']);
        $paris = $cityFactory->createNew(['name'=>'Paris']);
        $prague = $cityFactory->createNew(['name'=>'Praha']);
        $geneva = $cityFactory->createNew(['name'=>'Geneva']);
        $zurich = $cityFactory->createNew(['name'=>'Zurich']);

        //localize some city names
        $london->setBrotherEntity('localizedFile','french',['name'=>'Londres']);
        $prague->setBrotherEntity('localizedFile','english',['name'=>'Prague']);
        $geneva->setBrotherEntity('localizedFile','french',['name'=>'Genève']);
        $geneva->setBrotherEntity('localizedFile','german',['name'=>'Genf']);
        $zurich->setBrotherEntity('localizedFile','german',['name'=>'Zürich']);


        $countryFactory = new EntityFactory('country','generalCountryFile',$sandra);

        //add annoying insects
        $this->addAnnoyingInsects($sandra);


        //Create countries
        $uk = $countryFactory->createNew(['name'=>'UK']);
        $spain = $countryFactory->createNew(['name'=>'ES']);
        $germany = $countryFactory->createNew(['name'=>'DE']);
        $czech = $countryFactory->createNew(['name'=>'CZ']);
        $india = $countryFactory->createNew(['name'=>'IN']);
        $france = $countryFactory->createNew(['name'=>'FR']);
        $switzerland = $countryFactory->createNew(['name'=>'CH']);

        //localize countries
        $uk->setBrotherEntity('localizedFile','english',['name'=>'United Kingdom']);
        $uk->setBrotherEntity('localizedFile','french',['name'=>'Royaume Uni']);
        $uk->setBrotherEntity('localizedFile','german',['name'=>'Großbritannien']);

        $spain->setBrotherEntity('localizedFile','french',['name'=>'Espagne']);
        $spain->setBrotherEntity('localizedFile','english',['name'=>'Spain']);

        $germany->setBrotherEntity('localizedFile','english',['name'=>'Germany']);
        $germany->setBrotherEntity('localizedFile','french',['name'=>'Allemagne']);

        $czech->setBrotherEntity('localizedFile','english',['name'=>'Czech Republic']);
        $czech->setBrotherEntity('localizedFile','french',['name'=>'République Tchèque']);

        $india->setBrotherEntity('localizedFile','english',['name'=>'India']);
        $india->setBrotherEntity('localizedFile','french',['name'=>'Inde']);

        $france->setBrotherEntity('localizedFile','english',['name'=>'France']);

        $switzerland->setBrotherEntity('localizedFile','english',['name'=>'Switzerland']);
        $switzerland->setBrotherEntity('localizedFile','french',['name'=>'Suisse']);
        $switzerland->setBrotherEntity('localizedFile','german',['name'=>'Schweiz']);

        //put cities in countries
        $london->setBrotherEntity('locatedIn',$uk,array());
        $paris->setBrotherEntity('locatedIn',$france,array());
        $delhi->setBrotherEntity('locatedIn',$india,array());
        $prague->setBrotherEntity('locatedIn',$czech,array());
        $geneva->setBrotherEntity('locatedIn',$switzerland,array());
        $zurich->setBrotherEntity('locatedIn',$switzerland,array());

        //set some country spoken language
        $france->setBrotherEntity('speak','french',array());
        $france->setBrotherEntity('speak','french',array());

        $geneva->setBrotherEntity('speak','french',array());
        $zurich->setBrotherEntity('speak','german',array());

        $peopleFactory = new EntityFactory('person','peopleFile',$sandra);

        $ranjit = $peopleFactory->createNew(['firstName'=>'Ranjit']);
        $marketa = $peopleFactory->createNew(['firstName'=>'Marketa']);
        $antoine = $peopleFactory->createNew(['firstName'=>'Antoine']);
        $shaban = $peopleFactory->createNew(['firstName'=>'Shaban']);
        $daniela = $peopleFactory->createNew(['firstName'=>'Daniela']);
        $kurt = $peopleFactory->createNew(['firstName'=>'Kurt']);

        //Create born relations
        $ranjit->setBrotherEntity('bornInCity',$delhi,['year'=>1900]);
        $daniela->setBrotherEntity('bornInCity',$zurich,['year'=>1901]);
        $antoine->setBrotherEntity('bornInCity',$paris,['year'=>1902]);

        $antoine->createOrUpdateRef('name','Antonio');


        $kurt->setBrotherEntity('speak','german',array());


    }


    public function addAnnoyingInsects(System $sandra){

        $fliesFactory = new EntityFactory('fly','annoyingInsectFile',$sandra);

        $number = rand(1, 4);

        for ($i=0;$i<$number;$i++) {
            $fliesFactory->createNew(array('role' => 'be annoying'));
        }

        $number = rand(0, 2);

        $waspFactory  = new EntityFactory('wasp','annoyingInsectFile',$sandra);


        for ($i=0;$i<4;$i++) {
            $waspFactory->createNew(array('role' => 'be very annoying'));
        }



    }
}
