<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 12.05.20
 * Time: 12:09
 */

namespace App\Services;


use App\Console\Commands\Jetski\FirstOasisConsoleInterface;
use Artisan;
use CsCannon\Blockchains\Blockchain;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractStandard;
use CsCannon\Blockchains\Klaytn\KlaytnBlockchain;
use CsCannon\Blockchains\SignableBlockchainAddress;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class CSNotaryInterface
{

    public $csNotartyPath = 'CSNotary';
    public $blockchain = null;
    public $executor = null;

    public function __construct(Blockchain $blockchain, $customPath = null)
    {

        $this->blockchain = $blockchain ;
        if ($customPath) $this->csNotartyPath = $customPath ;
        $this->executor = $this->getExecutor();
    }

    public function getExecutor(){

        $blockchain = $this->blockchain ;

        $nodeServices = ['ethereum','klaytn','matic','counterparty','goerli','bitcoin'];
        //$phpService = ['bitcoin'];

        $finalPath = $this->csNotartyPath.'/'.$blockchain::NAME.'/' ; //deprecated
        $finalPath = $this->csNotartyPath;

        if (in_array($blockchain::NAME,$nodeServices)) return new CSNotaryExecutor(new CSNotaryNodeJSService(),$finalPath);
        //if (in_array($blockchain::NAME,$phpService)) return new CSNotaryExecutor(new CSNotaryPHPService(),$finalPath);


    }



    public function createWallet():SignableBlockchainAddress{




        $notaryData =  $this->rawCreateWallet();
        $notaryData = json_decode($notaryData);

        $addressFactory =  $this->blockchain->getAddressFactory();
       $address = $addressFactory->get($notaryData->data->address);
        return new SignableBlockchainAddress($address,$notaryData->data->privateKey);


    }

    public function rawCreateWallet(){



        $this->executor->setCommand('createWallet');
        $this->executor->setChain($this->blockchain::NAME);
        return $this->executor->executeRaw();


    }
    public function broadcast($rawTx){



        $this->executor->setCommand('broadcast');
        $this->executor->addArgument("--tx=".escapeshellarg($rawTx));
        $this->executor->setChain($this->blockchain::NAME);
        return $this->executor->executeRaw();


    }

    public function getContractData(BlockchainContract $contract){



        $this->executor->setCommand('getContractData');
        $this->executor->addArgument("--contract=".escapeshellarg($contract->getId()));
        $this->executor->setChain($this->blockchain::NAME);
        return $this->executor->executeRaw();


    }


    public function mintTo(BlockchainContract $contract,SignableBlockchainAddress $from,BlockchainAddress $to){

        $this->executor->setCommand('mintTo');
        $this->executor->setChain($this->blockchain::NAME);


        $this->executor->addArgument("--to=".$to->getAddress());
        $this->executor->addArgument("--contract=".$contract->getId());
        $this->executor->addArgument("--key=".$from->getPrivateKey());
        $this->executor->addArgument("--from=".$from->address->getAddress());
        return $this->executor->execute();




    }


    public function send(SignableBlockchainAddress $from,BlockchainAddress $to,
                         BlockchainContract $contract, BlockchainContractStandard $specifier, $quantity, $fee=null){

        if ($this->blockchain instanceof KlaytnBlockchain){

            $this->executor->addArgument("--feePayerPrivate=".env('CAVER_FEE_PAYER_PRIVATE'));
            $this->executor->addArgument("--feePayerPublic=".env('CAVER_FEE_PAYER_PUBLIC'));
        }

       // dd($from);

        $this->executor->setCommand('send');
        $this->executor->addArgument("--to=".escapeshellarg($to->getAddress()));
        $this->executor->addArgument("--contract=".escapeshellarg($contract->getId()));
        $this->executor->addArgument("--tokenPath='".json_encode($specifier->getSpecifierData())."'");
        $this->executor->addArgument("--from=".escapeshellarg($from->address->getAddress()));
        $this->executor->addArgument("--quantity=".escapeshellarg($quantity));
        $this->executor->addArgument("--key=".escapeshellarg($from->getPrivateKey()),true);
        if ($fee) {
            $this->executor->addArgument("--fee=" . escapeshellarg($fee));
        }
        $this->executor->setChain($this->blockchain::NAME);


         return   $this->executor->execute();


    }

    public function createSend(BlockchainAddress $from,BlockchainAddress $to,
                               ?BlockchainContract $contract, BlockchainContractStandard $specifier, $quantity, $fee=null){



        $this->executor->setCommand('createSend');
        $this->executor->addArgument("--to=".escapeshellarg($to->getAddress()));
        if ($contract) {
            $this->executor->addArgument("--contract=" . escapeshellarg($contract->getId()));
        }
        $this->executor->addArgument("--tokenPath='".json_encode($specifier->getSpecifierData())."'");
        $this->executor->addArgument("--from=".escapeshellarg($from->getAddress()));
        $this->executor->addArgument("--quantity=".escapeshellarg($quantity));

        if ($fee) {
            $this->executor->addArgument("--fee=" . escapeshellarg($fee));
        }

        $this->executor->setChain($this->blockchain::NAME);


        return   $this->executor->executeRaw();



    }


    public function balanceOf(BlockchainAddress $address,BlockchainContract $contract,BlockchainContractStandard $standard=null){

        $this->executor->addArgument("--to=".escapeshellarg($address->getAddress()));

        $this->executor->addArgument("--contract=".escapeshellarg($contract->getId()));
        if ($standard)
        $this->executor->addArgument("--tokenPath=".escapeshellarg(json_encode($standard->getSpecifierData())));

        $this->executor->setCommand('balanceOf');
        $this->executor->setChain($this->blockchain::NAME);
        return   $this->executor->execute();


    }




    public function execute(){

        $this->executor->setChain($this->blockchain::NAME);
        $this->executor->execute();

    }


}

class CSNotaryExecutor
{
    private $environment = null ;
    private $command = null ;
    private $path = null ;
    private $chain = null ;
    public $status = null ;
    private $arguments = '' ;
    private $sanitizedArguments = '' ;

    public function __construct(CSNotaryEnvironment $environment,$path)
    {

        $this->environment = $environment ;
        $this->path = $path;

        $this->addArgument("--legacy=false");


    }

    public function setCommand($commandName)
    {

       $this->command = $commandName ;

       return $this ;


    }

    public function addArgument($arguments,$sanitizeLog = false)
    {

        $sanitizeLog = false ;
        $this->arguments .= ' '.$arguments ;

        if (!$sanitizeLog) {
            $this->sanitizedArguments .= ' '.$arguments ;

        }

        return $this ;


    }


    public function setChain($chainName)
    {

        $this->chain = $chainName ;

        return $this ;


    }


    public function executeRaw(){

        $syscommand = $this->environment->getSysCommand();
        $extension = $this->environment->getExtension();

        //$cmd = "$syscommand  $this->path/$this->command.$extension";
        $cmd = "node  $this->path/NotaryExecutor.js --command=".escapeshellarg($this->command)
            .' --chain='.escapeshellarg($this->chain) ." ".$this->arguments;

        //echo $cmd.PHP_EOL ;
        log::debug($cmd);




        $notaryResponse = exec($cmd);
        log::debug($notaryResponse);


       return $notaryResponse;




    }


    public function execute(){

        $syscommand = $this->environment->getSysCommand();
        $extension = $this->environment->getExtension();

        //$cmd = "$syscommand  $this->path/$this->command.$extension";
        $cmd = "node  $this->path/NotaryExecutor.js --command=".escapeshellarg($this->command)
        .' --chain='.escapeshellarg($this->chain) ." ".$this->arguments;


        $sanizedForLog  = "node  $this->path/NotaryExecutor.js --command=".escapeshellarg($this->command)
            .' --chain='.escapeshellarg($this->chain) ." ".$this->sanitizedArguments;
         Log::info("CS NOTARY $sanizedForLog");

        $notaryResponse = exec($cmd);
        $myvar = '' ;


        //$output = $process->getOutput();
        $output = $notaryResponse;
        Log::info("CS NOTARY RESPONSE $output");



        //Artisan::call('firstoasis:exec',["cmd"=>$cmd]);
        //$output = Artisan::output() ;


       // dd($output);

        return $this->parseNotaryResponse($output);

    }

    private function parseNotaryResponse ($rawResponse){

        try {

            $notaryData = json_decode($rawResponse);

            if (!$notaryData->status)
                throw new \Exception("CS notary bad response".var_dump($notaryData));

            $this->status = $notaryData->status;

            return $notaryData->data;
        }
        catch (\Exception $e){

            Log::critical("Fail to parse notary response".$e->getMessage());

            throw $e ;

        }


    }




}

class CSNotaryNodeJSService extends CSNotaryEnvironment
{

   public  function getSysCommand(){

       return 'node';

}


    function getExtension()
    {
        return 'js';
    }
}

class CSNotaryPHPService extends CSNotaryEnvironment
{

    public  function getSysCommand(){

        return 'php';

    }

    function getExtension()
    {
        return 'php';
    }
}

abstract class CSNotaryEnvironment
{
    abstract function getSysCommand();
    abstract function getExtension();



}
