<?php

require_once __DIR__ . '/../../vendor/autoload.php';


$args     = getopt("", array("tx:"));
$tx = $args['tx'];

$ch       = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://xchain.io/api/send_tx');



curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'tx_hex='. $tx);
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