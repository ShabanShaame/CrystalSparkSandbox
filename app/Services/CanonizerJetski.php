<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 07.01.20
 * Time: 10:15
 */
namespace App\Services;

 use CsCannon\SandraManager;
 use SandraCore\EntityFactory;

 class CanonizerJetski {

     public function viewActiveJetski(){

         $jetskiFactory = new EntityFactory('jetskiRun','jetskiRunFile',SandraManager::getSandra());

         $jetskiFactory->populateLocal();

         print_r( $jetskiFactory->dumpMeta());
         return ['ok'];


     }




}
