<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 13.07.20
 * Time: 08:15
 */

namespace App\Services;


class PaginateDatabase
{

    public $limit ;
    public $offset ;
    public $direction ;

    public static $maxLimit = 500;
    public static $defaultLimit = 500;

    public  function __construct($limit,$offset,$direction='ASC')
    {

        if (!is_numeric($limit)) {
            $limit = self::$defaultLimit;
        }

        if ($limit > self::$maxLimit) {
            $limit = self::$maxLimit;
        }

        if (!is_numeric($offset)) {
            $offset = 0;
        }


        $this->limit = $limit;
        $this->offset = $offset ;
        $this->direction = $direction ;

    }

}