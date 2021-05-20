<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 28.01.20
 * Time: 14:36
 */

namespace App\Services;


use App\Sog\SogService;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainAddressFactory;
use CsCannon\SandraManager;
use SandraCore\Entity;
use SandraCore\EntityFactory;

class UserFactory extends EntityFactory
{

    public static $isa = null;
    public static $file = 'csUser';
    protected static $className = 'App\Services\UserEntity' ;
    const AUTHENTICATE_USER_VERB = 'authenticateUser';
    const ROLE_VERB = 'hasRole';

    public function __construct(){
        parent::__construct(self::$isa,self::$file,SandraManager::getSandra());
        $this->generatedEntityClass = static::$className ;

        $roleFactory = new CsRoleFactory();
        $this->joinFactory(self::ROLE_VERB,$roleFactory);
    }

    public function populateLocal($limit = 1000, $offset = 0, $asc = 'DESC',$sortByRef = null, $numberSort = false)
    {

        $return = parent::populateLocal($limit, $offset, $asc, $sortByRef, $numberSort);
        $this->populateBrotherEntities(0);
        $this->joinPopulate();

    }

    public static function getUserFactoryFromAddressFactory(BlockchainAddressFactory $addressFactory){

       $userFactory = new UserFactory();



        //$userFactory->conceptArray = $addresses;
        if ($addressFactory->entityArray)
       $userFactory->setFilter(self::AUTHENTICATE_USER_VERB,$addressFactory->entityArray);


       $userFactory->populateLocal();

       $userFactory->populateBrotherEntities(self::AUTHENTICATE_USER_VERB);

       foreach ($userFactory->getEntities() ?? array() as $user){


       }

        return $userFactory ;



    }

    public  function getAddressMap(BlockchainAddressFactory $addressFactory){

        $addressItem = array();

        foreach ($addressFactory->getEntities() ?? array() as $addressEntity){
            /** @var BlockchainAddress $addressEntity  */

            $user = $this->getEntitiesWithBrother(self::AUTHENTICATE_USER_VERB,$addressEntity);
            if ($user)
           $addressItem[$addressEntity->getAddress()] = $user ;




        }

        return $addressItem ;



    }










}

class csRoleFactory extends EntityFactory
{

    public function __construct(){
        parent::__construct(static::$isa,static::$file,SandraManager::getSandra());
        $this->generatedEntityClass = static::$className ;
    }

    public static $isa = 'csUserRoleFile';
    public static $file = 'csUserRole';
    protected static $className = 'App\Services\CsRole' ;

}

class CsRole extends Entity
{
     const NAME = 'name';

     public function getName(){

         return $this->get(self::NAME);

     }


}
