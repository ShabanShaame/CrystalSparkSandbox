<?php

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Address\SegwitAddress;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\WitnessProgram;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use CSNotary\RpcService;
use Illuminate\Http\JsonResponse;


require_once __DIR__ . '/../../vendor/autoload.php';

$network = \BitWasp\Bitcoin\Bitcoin::getNetwork();

$args     = getopt("", array("query:"));

$queryObject = json_decode($args['query']);
//var_dump($queryObject);
//ie();



//$rpcService = new \CsNotary\RpcService("http://www.google.com",'pass');
$rpc2 = new RpcService('https://public.coindaddy.io'	,"rpc:1234");




//fiddled parameters for RPC server
$params = [
    "source"      => $queryObject->from,
    "destination" => $queryObject->to,
    "asset"       => $queryObject->contract,
    "quantity"    => $queryObject->quantity,
    "fee"    => 20000,
];

$rpcResult =$rpc2->sendRequest('create_send',$params,'any');

if (isset($rpcResult->error)) {
    echo json_encode(["status" => "error", "rpcserverpayload" => $rpcResult]);

    return ;
}


$hex = $rpcResult->result;



$tx = TransactionFactory::fromHex($hex);

$transactionOutputs = [];
foreach ($tx->getInputs() as $idx => $input) {
    $transactionOutput = new TransactionOutput(0, ScriptFactory::fromHex($input->getScript()->getBuffer()->getHex()));
    array_push($transactionOutputs, $transactionOutput);
}

$priv = (new BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory)->fromWif($queryObject->key);
$signer = new Signer($tx, \BitWasp\Bitcoin\Bitcoin::getEcAdapter());

foreach ($transactionOutputs as $idx => $transactionOutput) {
    $signer->sign($idx, $priv, $transactionOutput);
}

$signed = $signer->get();

//echo $signed->getHex() . PHP_EOL;



$ch       = curl_init();


curl_setopt($ch, CURLOPT_URL, 'https://xchain.io/api/send_tx');



curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'tx_hex='. $signed->getHex());
//curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$result = curl_exec($ch);
//if ($result === false) {
//    throw new \Exception(curl_error($ch), curl_errno($ch));
//}
curl_close($ch);

$resultArray = (json_decode($result,1));
if (isset($resultArray['tx_hash'])) $resultArray['txHash'] = $resultArray['tx_hash'];
//$resultArray = json_decode($result,1);
echo json_encode($resultArray) ;



