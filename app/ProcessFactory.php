<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 07.01.20
 * Time: 09:38
 */

namespace App;



use SandraCore\EntityFactory;
use SandraCore\System;

class ProcessFactory extends EntityFactory{

    public $entityIsa = 'blockchainSyncProcess';
    public $entityContainedIn = 'processFile';
    public $notClosedProcessFactory = null ; /**@var \App\Console\Commands\Jetski\ProcessFactory $notClosedProcessFactory **/
    public $currentProcess ;
    const ACTIVE_PROCESS = 'active';
    const ACTIVE_CLOSED = 'closed';
    const HAS_STATUS = 'hasStatus';
    const PROCESS_DISTINCT_NAME = 'processDistinctName';
    const RUN_COUNT = 'runCount';
    const EXEC_TIME = 'execTime';



    const PID = 'pid';
    const PID_LAST_UPDATE = 'pidLastUpdate';
    const START_TIMESTAMP = 'startTimestamp';
    const END_TIMESTAMP = 'endTimestamp';
    const SIGTERM_VERB = 'remoteCall';
    const SIGTERM_TARGET = 'sigterm'; // when the user ask to kill the process

    public function __construct(System $system, $activeOnly = null)
    {
        $this->setGeneratedClass(BlockchainSyncProcess::class);

        parent::__construct($this->entityIsa, $this->entityContainedIn, $system);


        if (!$activeOnly) {
            $notClosedProcessFactory = new ProcessFactory($system, true);
            $this->notClosedProcessFactory = $notClosedProcessFactory ;
        } else{

            $this->setFilter(self::HAS_STATUS, self::ACTIVE_CLOSED, 1);
        }

        $this->populateLocal();
        $this->getTriplets();

        $this->populateBrotherEntities(self::SIGTERM_VERB);
        $this->populateBrotherEntities(self::HAS_STATUS);

        //register_shutdown_function(array($this,'shutdownProcess'));

    }

    public function register(String $processName,
                             int $pid,
                             int $startTimestamp)
    {
        $data[self::PROCESS_DISTINCT_NAME] = $processName;
        $data[self::START_TIMESTAMP] = $startTimestamp;
        $data[self::PID] = $pid;

        $createdProcess = $this->createNew($data);
        //set the process active
        $createdProcess->setBrotherEntity(self::HAS_STATUS,
            self::ACTIVE_PROCESS,
            null,true,
            true);



        return $createdProcess;

    }

    public function refreshActiveProcess()
    {
        $sigtem = $this->notClosedProcessFactory->populateBrotherEntities(self::SIGTERM_VERB);
        $process = $this->notClosedProcessFactory->populateBrotherEntities(self::HAS_STATUS);



        return $process ;

    }

    public function registerUniqueProcess($processType):?BlockchainSyncProcess
    {
        if ($this->isSimilarProcessRunning($processType)){

            echo"similar process is not closed".PHP_EOL;

            return null ;
        }

        $this->currentProcess = $this->register($processType,getmypid(),time());

        return $this->currentProcess ;

    }

    public function isSimilarProcessRunning($processType):bool
    {

        $activeFactory = $this->notClosedProcessFactory ;
        $activeFactory->setFilter(self::HAS_STATUS, self::ACTIVE_CLOSED, 1);
        $activeFactory->populateLocal();

        /**@var ProcessFactory $activeFactory **/
        $existingProcess = $activeFactory->last(self::PROCESS_DISTINCT_NAME,$processType);

        /**@var BlockchainSyncProcess $existingProcess **/

        if ($existingProcess && $existingProcess->hasSigTerm()) $existingProcess->closeProcess("forced by reopening the process");

        if($existingProcess) return true ;

        return false ;
    }

    public function sigTermProcess($processCode,$sender='undefined'):bool
    {

        $process = $this->notClosedProcessFactory->first(self::PROCESS_DISTINCT_NAME,$processCode);
        //Process not active
        if (is_null($process)){

            $nonActiveProcess = $this->first(self::PROCESS_DISTINCT_NAME,$processCode);
            if ($nonActiveProcess){
                $this->info("process $processCode not active");
            }
            else {
                $this->info("process $processCode not found");
            }

            return null ;

        }

        $process->setSigterm($sender);

        return false ;
    }

    function shutdownProcess()
    {

        echo 'Prcess shutdown', PHP_EOL;
        if($this->currentProcess){

            $this->currentProcess->closeProcess("shutdown");
        }
        else{
            echo 'No Active script', PHP_EOL;

        }

    }




}



