<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 24.06.20
 * Time: 11:33
 */

namespace App\Services;


class NotificationService
{
    public $keybase ;

    const TEAM = 'edsoft.stream';
    const CHANNEL = 'jetski';
    private $noNotifForSecond = 60 ;
    private $lastNotifTimestamp = 0 ;

    public function __construct()
    {
       $keybasePath =  env('KEYBASE_PATH');
        $this->keybase = new KeybaseService($keybasePath);


    }

    public function notify($target,$message,$channel=null)
    {

        $this->keybase->sendToUser($target,$message,$channel);

    }


    public function read($team,$channel=null)
    {

        return $this->keybase->read($team,$channel);

    }

    public function jetskiNotify($message,$forceDelivery=true)
    {

        if ($this->lastNotifTimestamp + $this->noNotifForSecond <= time() or $forceDelivery) {

            $this->keybase->sendToUser(self::TEAM, $message, self::CHANNEL);

            if(!$forceDelivery) {
                $this->keybase->sendToUser(self::TEAM, "will not notify for the next second: ".$this->noNotifForSecond, self::CHANNEL);
                $this->lastNotifTimestamp = time();
            }

        }

    }






}