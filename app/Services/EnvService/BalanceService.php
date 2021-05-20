<?php


namespace App\Services\EnvService;


use CsCannon\Blockchains\BlockchainEvent;
use CsCannon\Blockchains\Substrate\Kusama\KusamaEventFactory;
use CsCannon\Tools\BalanceBuilder;

class BalanceService
{


    public function clearBalances(){


        BalanceBuilder::resetBalanceBuilder(new KusamaEventFactory(),true);
        BalanceBuilder::flagAllForValidation(new KusamaEventFactory(),true);
        $return[] = "Balances reset" ;

        return $return ;

    }

    public function buildBalances(){


        for ($i=0;$i<100;$i++) {
            BalanceBuilder::buildBalance(new KusamaEventFactory(), true,$i*5000);
            $return[] ="we are rebuilding balances";
        }

        return $return ;

    }


    public function getEventData(){


        $eventFactory = new KusamaEventFactory();
        $eventFactory->setFilter(BalanceBuilder::PROCESS_STATUS_VERB,BalanceBuilder::PROCESS_STATUS_VALID);

        $response[] ="valid events ".$eventFactory->countEntitiesOnRequest();

        $eventFactory = new KusamaEventFactory();
        $eventFactory->setFilter(BalanceBuilder::PROCESS_STATUS_VERB,BalanceBuilder::PROCESS_STATUS_INVALID);

        $response[] ="invalid events ".$eventFactory->countEntitiesOnRequest();


        $eventFactory = new KusamaEventFactory();
        $eventFactory->setFilter(BalanceBuilder::PROCESS_STATUS_VERB,BalanceBuilder::PROCESS_STATUS_PENDING);
        $response[] ="pending events ".$eventFactory->countEntitiesOnRequest();


        $eventFactory = new KusamaEventFactory();
        $eventFactory->setFilter(BalanceBuilder::PROCESS_STATUS_VERB,0,true);
        $response[] ="unflagged events ".$eventFactory->countEntitiesOnRequest();

        return $response ;

    }

    public function displayInvalidTransactions(){

        $max = 1000 ;
        $eventFactory = new KusamaEventFactory();
        $eventFactory->setFilter(BalanceBuilder::PROCESS_STATUS_VERB,BalanceBuilder::PROCESS_STATUS_INVALID);
        $eventFactory->populateLocal($max);

        $response[] ="showing max $max";

        foreach ($eventFactory->getEntities() as $event){
            /** @var $event BlockchainEvent */

            $response[] = print_r($event->display()->return(),1);

        }

        return $response ;


    }

}
