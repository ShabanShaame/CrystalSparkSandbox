<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDOException;

class TableViewController extends Controller
{

    private static $tableName;


    public static function getTableName(string $table,$env=null)
    {
        if (!$env) $env = env('SANDRA_ENV');

        self::$tableName = $env .'_view_'. $table;
        return self::$tableName;
    }

    public static function getTableHead(string $table)
    {
        $tableExists = self::checkExists($table);

    }


    public static function countTable(string $table)
    {

        $tableExists = self::checkExists($table);

        if($tableExists){

            return DB::table(self::getTableName($table))
                ->count();
        }

        return $tableExists;

    }


    public static function get(string $table)
    {

        $tableExists = self::checkExists($table);

        if($tableExists){

            return DB::table(self::getTableName($table))
                ->select('*');

        }

        return $tableExists;
    }


    private static function checkExists(string $table)
    {
        $checkExists = DB::table(self::getTableName($table))->exists();

        if(!$checkExists){
            try{
                $collectionController = new CollectionController;
                $collectionController->createEntityAndViewTable($table);

            }catch(PDOException $e){

                return false;
            }
            return true;
        }
        return $checkExists;
    }



}
