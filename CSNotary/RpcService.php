<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 29.04.20
 * Time: 09:48
 */

namespace CSNotary;

class RpcService
{


    private $serverUrl;
    private $serverCredentials;


    public function __construct($serverUrl, $serverCredentials)
    {

        $this->serverUrl                = $serverUrl;
        $this->serverCredentials        = $serverCredentials;


    }


    public function sendRequest($method, $params, $network)
    {
        $ch       = curl_init();
        $curlData = [
            "jsonrpc" => "2.0",
            "id"      => "curltext",
            "method"  => $method,
            "params"  => $params,
        ];


            curl_setopt($ch, CURLOPT_USERPWD, $this->serverCredentials);
            curl_setopt($ch, CURLOPT_URL, $this->serverUrl);
            curl_setopt($ch, CURLOPT_PORT, 4001);


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlData));
        //curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        //if ($result === false) {
        //    throw new \Exception(curl_error($ch), curl_errno($ch));
        //}
        curl_close($ch);

        return json_decode($result);
    }




}