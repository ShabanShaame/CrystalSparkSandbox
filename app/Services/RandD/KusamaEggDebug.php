<?php


namespace App\Services\RandD;


use CsCannon\Blockchains\Substrate\Kusama\KusamaEventFactory;
use CsCannon\Tools\BalanceBuilder;

class KusamaEggDebug
{

    public function getInvalidKanariaEvents (){




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


}
