<?php


namespace App\Services\Entities;




use App\Services\Factories\ServicePoolingFactory;
use App\Services\UserEntity;
use SandraCore\Entity;

class ServicePoolingEntity extends Entity
{

    public function getUser():UserEntity{


       $userArray = $this->getJoinedEntities(ServicePoolingFactory::TO_USER);
       if ($userArray) return reset($userArray);

    }

    public function close():bool{

        $status = $this->get(ServicePoolingFactory::STATUS);
        if ($status != 'open') {$this->factory->poolError("trying to pool a closed reward"); return false;}
        $this->createOrUpdateRef(ServicePoolingFactory::STATUS,'closed');
        $this->setBrotherEntity(ServicePoolingFactory::STATUS,'closed',null,true,true);

        return true ;

    }

    public function getAmount():int{

       return $this->get(ServicePoolingFactory::AMOUNT);
    }

    public function isSpend():bool {

        $kind = $this->get(ServicePoolingFactory::KIND);

        if($kind == ServicePoolingFactory::SPEND_KIND) return true ;

        return false ;
    }



}