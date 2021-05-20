<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 07.01.20
 * Time: 10:15
 */
namespace App\Services;

use App\BlockchainSyncProcess;
use App\ProcessFactory;

class ProcessesService
{

    public function sigTermAllActive(){

        $metaSandra = app('Sandra')->getMetaSandra();
        $processFactory = new ProcessFactory($metaSandra);

        foreach ($processFactory->notClosedProcessFactory->getEntities() as $process){

            $process->setSigterm();

            $message[] = $process->getName() . ' sigtermed';

        }

        return $message ;


    }

    public function sigTermProcess($processName){

        $metaSandra = app('Sandra')->getMetaSandra();
        $processFactory = new ProcessFactory($metaSandra);
        $targetProcess = $processFactory->notClosedProcessFactory->first(ProcessFactory::PROCESS_DISTINCT_NAME, $processName);

        if($targetProcess == null){

            $message['error'] = "processNotFound";
            $message['active'] = $this->getActiveProcessArray();

            return $message ;
        }



        $targetProcess->setSigterm();

            $message[] = $targetProcess->getName() . ' sigtermed';



        return $message ;


    }

    public function closeProcess($processName){

        $metaSandra = app('Sandra')->getMetaSandra();
        $processFactory = new ProcessFactory($metaSandra);

        $targetProcess = $processFactory->notClosedProcessFactory->first(ProcessFactory::PROCESS_DISTINCT_NAME, $processName);

        if($targetProcess == null){

            $message['error'] = "processNotFound";
            $message['active'] = $this->getActiveProcessArray();

            return $message ;
        }



        $targetProcess->closeProcess("close all command");

            $message[] = $targetProcess->getName() . ' closed';


        return $message ;


    }

    public function closeAllActive(){

        $metaSandra = app('Sandra')->getMetaSandra();
        $processFactory = new ProcessFactory($metaSandra);

        foreach ($processFactory->notClosedProcessFactory->getEntities() as $process){

            $process->closeProcess("close all command");

            $message[] = $process->getName() . ' closed';

        }

        return $message ;


    }

    public function getActiveProcessArray(){

        $metaSandra = app('Sandra')->getMetaSandra();
    $processFactory = new ProcessFactory($metaSandra);

   return  $processFactory->notClosedProcessFactory->dumpMeta();


    }



}