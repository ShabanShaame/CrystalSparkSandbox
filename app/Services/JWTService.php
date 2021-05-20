<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 29.01.20
 * Time: 14:44
 */

namespace App\Services;


use Firebase\JWT\JWT;

class JWTService
{


    public static function issueJWTSharing($payload,$expiration=null){

        $key = 'urei94t5nfifu4503ngldg0945u3234';

        if ($expiration) {$payload['exp'] = $expiration ;}
        else{
        $payload['exp'] = time()+60*60 ; }//1 hour



        $jwt = JWT::encode($payload, $key);

        return $jwt ;

    }

    public static function issueServiceJwt($payload,$key,$expiration=null){



        if ($expiration) {$payload['exp'] = $expiration ;}
        else{
            $payload['exp'] = time()+60*60 ; }//1 hour



        $jwt = JWT::encode($payload, $key);

        return $jwt ;

    }




    public static function issueJWT($payload, $ip){

        $key = env("APP_KEY");
        $payload['ip'] = $ip ;
        $payload['exp'] = time()+60*60*4 ;
        sleep(2);

        $jwt = JWT::encode($payload, $key);

        return $jwt ;

    }

    public static function serviceJWT($payload,$serial =1){

        $key = env("APP_KEY");
       $payload['serial'] = $serial ;
       // $payload['exp'] = time()+60*60*4 ;
      //  sleep(2);

        $jwt = JWT::encode($payload, $key);

        return $jwt ;

    }


    public static function verifyJwt($jwt,$ip){

        $key = env("APP_KEY");

        try {

            $decoded = JWT::decode($jwt, $key, array('HS256'));
            if ($ip == '::1') $ip = '127.0.0.1'; //fix localhost issues

            if ($decoded->ip != $ip) Throw new \Exception("not matching IP",1);


        }
        catch (\Exception $e){
            return false ;

        }

        return $decoded ;

    }

    public static function verifyAppJwt($jwt,$otherConstraints){

        $key = 'urei94t5nfifu4503ngldg0945u3234';

        try {

            $decoded = JWT::decode($jwt, $key, array('HS256'));

        }
        catch (\Exception $e){

            return false ;

        }

        return $decoded ;

    }

    public static function verifyServiceJwt($jwt,$key){



        try {

            $decoded = JWT::decode($jwt, $key, array('HS256'));

        }
        catch (\Exception $e){

           throw $e ;

        }

        return $decoded ;

    }


}
