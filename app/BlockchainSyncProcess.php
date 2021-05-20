<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 07.01.20
 * Time: 09:40
 */

namespace App;


use SandraCore\Entity;

class BlockchainSyncProcess extends Entity {


    public function closeProcess($closingMessage = null ){

        $this->createOrUpdateRef(ProcessFactory::END_TIMESTAMP,time());
        $this->setBrotherEntity(ProcessFactory::HAS_STATUS,ProcessFactory::ACTIVE_CLOSED,null,true,true);

    }

    public function setSigterm($sender='undefined'){

        $data['sender'] = $sender ;
        $data['timestamp'] = time() ;

        $sigterm = $this->setBrotherEntity(ProcessFactory::SIGTERM_VERB,ProcessFactory::SIGTERM_TARGET,$data);

        return $sigterm ;


    }

    public function hasSigTerm(){

        $this->factory->populateBrotherEntities(ProcessFactory::SIGTERM_VERB,null,true);
        $sigterm = $this->getBrotherEntity(ProcessFactory::SIGTERM_VERB,ProcessFactory::SIGTERM_TARGET);

        if ($sigterm) return true ;

        return false ;

    }

    public function isClosed(){

        $this->factory->populateBrotherEntities(ProcessFactory::HAS_STATUS,null,true);
        $closed = $this->getBrotherEntity(ProcessFactory::HAS_STATUS,ProcessFactory::ACTIVE_CLOSED);

        if ($closed) return true ;

        return false ;

    }

    public function getName(){


       return $this->get(ProcessFactory::PROCESS_DISTINCT_NAME);

    }

    public function processHasExecuted(){

        $now = time();

        $previousPidLast = $this->getReference(ProcessFactory::PID_LAST_UPDATE);
        if (!$previousPidLast) $previousPidLastTime = 0;
        else $previousPidLastTime = $previousPidLast->refValue;

        //time difference
        $timeDiff = $now - $previousPidLastTime ;


        $pidLast  = $this->createOrUpdateRef(ProcessFactory::PID_LAST_UPDATE,time());
        $runCount = $this->getReference(ProcessFactory::RUN_COUNT);
        if (!$runCount) $runCountVal = 0;
        else $runCountVal = $runCount->refValue;
        $runCountVal++ ;

        $ref =  $this->createOrUpdateRef(ProcessFactory::RUN_COUNT,$runCountVal);
        $ref =  $this->createOrUpdateRef(ProcessFactory::EXEC_TIME,$timeDiff);


        return $this->getReference(ProcessFactory::PID_LAST_UPDATE);

    }

}