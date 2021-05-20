<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 07.06.20
 * Time: 10:22
 */

namespace App\Services;


use App\BlockchainSyncProcess;
use App\ProcessFactory;

use App\Services\Factories\BindPoolFactory;
use App\Sog\SogService;
use CsCannon\Asset;
use CsCannon\AssetFactory;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainContractStandard;
use CsCannon\Blockchains\BlockchainEvent;
use CsCannon\Blockchains\BlockchainEventFactory;
use CsCannon\Blockchains\Counterparty\XcpContractFactory;
use CsCannon\Blockchains\Ethereum\EthereumBlockchain;
use CsCannon\Blockchains\Ethereum\Interfaces\ERC721;
use CsCannon\Blockchains\Klaytn\KlaytnContractFactory;
use CsCannon\Blockchains\Klaytn\KlaytnEventFactory;
use CsCannon\SandraManager;
use CsSog\SogCardFactory;
use Ethereum\Sandra\EthereumContractFactory;
use InnateSkills\SandraHealth\MemoryManagement;
use SandraCore\CommonFunctions;
use ZMQ;
use ZMQContext;
use ZMQSocket;
use ZMQSocketException;

// tick use required
declare(ticks = 1);

class JetskiService
{

    private $env;
    private $chain;
    private $processFactory;
    public $running;
    private $metaSandra;
    private $currentProcess = null;
    private $notificationService = null;
    private $insideLoop = true;


    public function __construct($envName, $chain, $insideLoop = true)
    {


        $this->env = $envName;
        $this->chain = $chain;
        $this->metaSandra = app('Sandra')->getMetaSandra();
        $this->processFactory = new ProcessFactory($this->metaSandra);


        if (!function_exists('pcntl_signal')) {
            printf("Error, you need to enable the pcntl extension in your php binary, see http://www.php.net/manual/en/pcntl.installation.php for more info%s", PHP_EOL);
            exit(1);
        }


        pcntl_signal(SIGTERM, [$this, 'sig_handler']);
        pcntl_signal(SIGHUP, [$this, 'sig_handler']);
        pcntl_signal(SIGUSR1, [$this, 'sig_handler']);


        $this->notificationService = new NotificationService();
        $this->insideLoop = $insideLoop;




    }


    public function loop()
    {


        register_shutdown_function([$this, 'shutdown']);
        $data = '';

        if (class_exists('ZMQContext')) {
            $context = new ZMQContext();

            //  Socket to talk to clients
            $responder = new ZMQSocket($context, ZMQ::SOCKET_REP);
            $responder->bind("tcp://*:5558");
        }


        $this->running = true;

        //$currentProcess = $this->processFactory->registerUniqueProcess("$processUniqueIdentifier");
        $this->currentProcess = $this->processFactory->registerUniqueProcess("jetski_$this->env" . '_' . $this->chain);
        $this->message("process " . $this->currentProcess->getName());
        $this->notificationService->jetskiNotify("" . $this->currentProcess->getName() . " has started");


        while ($this->checkProcessContinue()) {
            try {
                $string = '';
                if (class_exists('ZMQContext____WEREMOVE')) {
                    try {
                        $string = $responder->recv(); //  The recv call will throw an ZMQSocketException when interrupted
                        // PHP Fatal error:  Uncaught exception 'ZMQSocketException' with message 'Failed to receive message: Interrupted system call' in interrupt.php:35
                    } catch (ZMQSocketException $e) {
                        if ($e->getCode() == 4) //  4 == EINTR, interrupted system call (Ctrl+C will interrupt the blocking call as well)
                        {
                            usleep(1); //  Don't just continue, otherwise the ticks function won't be processed, and the signal will be ignored, try it!
                            continue; //  Ignore it, if our signal handler caught the interrupt as well, the $running flag will be set to false, so we'll break out
                        }

                        throw $e; //  It's another exception, don't hide it to the user
                    }
                }
                //printf("Received request: [%s]%s", $string, PHP_EOL);

                $output = null;
                $return_var = null;

                $this->message("php artisan sync:chain $this->chain $this->env --jetski=$this->insideLoop 2>&1");

                exec("php artisan sync:chain $this->chain $this->env --jetski=$this->insideLoop 2>&1", $output, $return_var);

                $this->message(MemoryManagement::getSystemMemory());
                $this->message(print_r($output, 1));
                $this->currentProcess->processHasExecuted();
                $processInfo = print_r($this->currentProcess->getDisplayRef(), 1);


                    exec("php artisan bindPool ".$this->env);


                $this->notificationService->jetskiNotify("" . $this->currentProcess->getName() . " Has looped $processInfo", false);



                sleep(0.1);
                //$data .= str_repeat('#', PHP_INT_MAX);
            } catch (\Exception $exception) {
                $this->sigTermCloseProcess($this->currentProcess);
                $this->notificationService->jetskiNotify("exeption " . $exception->getMessage());
                $this->message($exception->getMessage());
                break;

            }

        }


    }


    public function sigTermCloseProcess(?BlockchainSyncProcess $process)
    {

        if (!$process) return;
        if (!$process->hasSigTerm()) {
            $process->setSigterm(self::class);

        }

        $process->closeProcess("Jetski finish");
        $this->message("Jetski closing process " . $process->getName());
        $this->notificationService->jetskiNotify("" . $process->getName() . " has closed");


        $this->currentProcess = null;


    }

    private function checkProcessContinue()
    {


        $metaSandra = app('Sandra')->getMetaSandra(false);

        $processFactory = new ProcessFactory($metaSandra);
        $processFactory->refreshActiveProcess();


        if ($this->currentProcess->hasSigTerm()) {
            $this->sigTermCloseProcess($this->currentProcess);
            return false;
        }


        if ($this->currentProcess->isClosed()) {

            return false;
        }

        $metaSandra->destroy();
        return true;

    }

    public function __destruct()
    {
        $this->sigTermCloseProcess($this->currentProcess);
        $this->notificationService->jetskiNotify("destruct called has closed");
        echo 'Destruct: ' . __METHOD__ . '()' . PHP_EOL;
    }

    function shutdown()
    {
        $this->sigTermCloseProcess($this->currentProcess);
        $this->notificationService->jetskiNotify("shutdown called has closed");
        echo 'Shutdown: ' . __FUNCTION__ . '()' . PHP_EOL;
    }

    // signal handler function
    function sig_handler($signo)
    {

        switch ($signo) {
            case SIGTERM:
                echo "Caught SIGTERM...\n";
                $this->sigTermCloseProcess($this->currentProcess);
                $this->notificationService->jetskiNotify("Process sigterm");
                exit;
                break;
            case SIGHUP:
                echo "Caught SIGHUP...\n";
                break;
            case SIGUSR1:
                echo "Caught SIGUSR1...\n";

                break;
            default:
                echo "Caught HOHO...\n";
        }

    }


    public function message($message)
    {

        echo $message . PHP_EOL;


    }

}


// setup signal handlers


