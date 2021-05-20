<?php


/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 26.03.19
 * Time: 10:27
 */

namespace App\Http\Controllers;









use App\AuthManager;
use App\ResponseService;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractFactory;
use CsCannon\Blockchains\Generic\GenericContractFactory;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use SandraCore\EntityFactory;
use SandraCore\System;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{

    public function registerBot($botId,$botUsername,$botkey){

        $bot_api_key  = $botkey;
        $bot_username = $botUsername;

        $bot_api_key  = '878881451:AAFoXMJnWO6lPgwGsAbVz1pGab3pasekWR8';
        $bot_username = '@shabanSandboxBot';

        $hook_url     = "https://baster.bitcrystals.com/bot/handle/$botId";

        try {
            // Create Telegram API object
            $telegram = new Telegram($bot_api_key, $bot_username);


            // Set webhook
            $result = $telegram->setWebhook($hook_url);
            if ($result->isOk()) {
                echo $result->getDescription();
            }



        } catch (TelegramException $e) {
            // log telegram errors
            echo $e->getMessage();
        }

        echo"https://baster.bitcrystals.com/bot/handle/$botId";





    }


    public function handle($botId){


        //Log::channel('telegram')->info('Something happened!');
        //Log::channel('slack')->info('Something happened!');

        $bot_api_key  = '878881451:AAFoXMJnWO6lPgwGsAbVz1pGab3pasekWR8';
        $bot_username = '@shabanSandboxBot';

        $mysql_credentials = [
            'host'     => Config('database.connections.mysql.host'),
            'port'     => 3306, // optional
            'user'     => Config('database.connections.mysql.username'),
            'password' => Config('database.connections.mysql.password'),
            'database' => env('DB_DATABASE_TELEGRAM'),
        ];






        try {

            $telegram = new Telegram($bot_api_key, $bot_username);
            $telegram->enableAdmin(29881060);

            $commands_paths = [
                app_path() . '/bots/Commands',
            ];
// Add this line inside the try{}
            $telegram->addCommandsPaths($commands_paths);

            // Create Telegram API object

            $telegram->enableMySql($mysql_credentials);




            // Handle telegram webhook request
           $handle = $telegram->handle();
            $input =  $telegram->getCustomInput();









            Log::info('telegramHadled', ['id' => "telegram"]);
        } catch (TelegramException $e) {
            // Silence is golden!
            // log telegram errors
            echo $e->getMessage();
            Log::info('Error'.$e->getMessage(), ['id' => "telegram"]);
        }


    }







}