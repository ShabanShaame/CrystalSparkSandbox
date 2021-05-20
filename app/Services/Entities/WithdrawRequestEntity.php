<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 15.09.20
 * Time: 14:43
 */

namespace App\Services\Entities;


use App\Services\CSNotaryInterface;
use App\Services\Factories\WithdrawRequestEntityFactory;
use App\Services\FirstOasisService;
use App\Services\UserEntity;
use CsCannon\BlockchainRouting;
use CsCannon\Blockchains\BlockchainAddress;
use CsCannon\Blockchains\BlockchainContract;
use CsCannon\Blockchains\BlockchainContractStandard;
use CsCannon\Blockchains\Counterparty\Interfaces\CounterpartyAsset;
use CsCannon\Blockchains\Klaytn\KlaytnAddressFactory;
use CsCannon\Blockchains\Klaytn\KlaytnContractFactory;
use SandraCore\Entity;


class WithdrawRequestEntity extends Entity
{

    public function setSpendTxHash($txHash,$confirmed=false){

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::DEPOSIT_TX,$txHash);

        if ($confirmed){

            $this->setBrotherEntity(WithdrawRequestEntityFactory::SPEND_CONFIRMED_RELATION,
                WithdrawRequestEntityFactory::CONFIRMATION_TX_CONFIRMED,['timestamp'=>time()]);
        }

    }

    public function confirmSpend($timestamp = null){

        if (!$timestamp) $timestamp = time();

            $this->setBrotherEntity(WithdrawRequestEntityFactory::SPEND_CONFIRMED_RELATION,
                WithdrawRequestEntityFactory::CONFIRMATION_TX_CONFIRMED,['timestamp'=>$timestamp]);

    }

    public function confirmReceive($timestamp = null){

        if (!$timestamp) $timestamp = time();

        $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_CONFIRMED_RELATION,
            WithdrawRequestEntityFactory::CONFIRMATION_TX_CONFIRMED,['timestamp'=>$timestamp]);

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_CONFIRMED_RELATION,true);

    }

    public function setReceiveTxHash($txHash,$confirmed=false){

        $receivedConfirmation = false ;

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::WITHDRAW_TX,$txHash);


        if ($confirmed) {
            $receivedConfirmation = true ;
            $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_CONFIRMED_RELATION,
                WithdrawRequestEntityFactory::CONFIRMATION_TX_CONFIRMED, ['timestamp' => time()]);
            $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_CONFIRMED_RELATION,$receivedConfirmation);
        }


    }

    public function setReceiveTransactonData($txHash,
                                             BlockchainAddress $sender,
                                             BlockchainAddress $receiver,
                                             BlockchainContract $contract,
                                             BlockchainContractStandard $specifier,
                                             $confirmed=false){

        $receivedConfirmation = false ;
        $nowTimeReference = ['timestamp'=>time()];

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::WITHDRAW_TX,$txHash);

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_CONTRACT,$contract->getId());
        $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_CONTRACT_RELATION,$contract,$nowTimeReference);
        $standardJson = BlockchainContractStandard::getJsonFromStandardArray([$specifier]);

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::POOL_SENDER,$sender->getAddress());
        $this->setBrotherEntity(WithdrawRequestEntityFactory::POOL_SENDER_ADDRESS_RELATION,$sender,$nowTimeReference);

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_ADDRESS,$receiver->getAddress());
        $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_ADDRESS_RELATION,$receiver,$nowTimeReference);


        $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_BLOCKCHAIN,$contract->getBlockchain()::NAME);
        $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_BLOCKCHAIN,$contract->getBlockchain()::NAME,$nowTimeReference);

        $specifier = $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_TOKENPATH,'token',$nowTimeReference);
        $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_TOKENPATH,$standardJson);
        $specifier->setStorage($standardJson);


        if ($confirmed) {
            $receivedConfirmation = true ;
            $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_CONFIRMED_RELATION,
                WithdrawRequestEntityFactory::CONFIRMATION_TX_CONFIRMED, ['timestamp' => time()]);
            $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_CONFIRMED_RELATION,$receivedConfirmation);
        }


    }

    public function setSpendTransactonData($txHash,
                                             BlockchainContract $contract,
                                             BlockchainContractStandard $specifier,
                                             $confirmed=false){

        $receivedConfirmation = false ;
        $nowTimeReference = ['timestamp'=>time()];

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::DEPOSIT_TX,$txHash);

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::SPEND_CONTRACT,$contract->getId());
        $this->setBrotherEntity(WithdrawRequestEntityFactory::SPEND_CONTRACT_RELATION,$contract,$nowTimeReference);
        $standardJson = BlockchainContractStandard::getJsonFromStandardArray([$specifier]);

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::SPEND_BLOCKCHAIN,$contract->getBlockchain()::NAME);
        $this->setBrotherEntity(WithdrawRequestEntityFactory::SPEND_BLOCKCHAIN,$contract,$contract->getBlockchain()::NAME);

        $specifier = $this->setBrotherEntity(WithdrawRequestEntityFactory::SEND_TOKENPATH,'token',$nowTimeReference);
        $this->createOrUpdateRef(WithdrawRequestEntityFactory::SEND_TOKENPATH,$standardJson);
        $specifier->setStorage($standardJson);


        if ($confirmed) {
            $receivedConfirmation = true ;
            $this->setBrotherEntity(WithdrawRequestEntityFactory::SPEND_CONFIRMED_RELATION,
                WithdrawRequestEntityFactory::CONFIRMATION_TX_CONFIRMED, ['timestamp' => time()]);
            $this->createOrUpdateRef(WithdrawRequestEntityFactory::SPEND_CONFIRMED_RELATION,true);
        }


    }


    public function setReceiveAddress(BlockchainAddress $sender,
                                   BlockchainAddress $receiver){

        $nowTimeReference = ['timestamp'=>time()];

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_BLOCKCHAIN,$receiver->getBlockchain()::NAME);
        $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_BLOCKCHAIN,$receiver->getBlockchain(),$nowTimeReference);



        $this->createOrUpdateRef(WithdrawRequestEntityFactory::POOL_SENDER,$sender->getAddress());
        $this->setBrotherEntity(WithdrawRequestEntityFactory::POOL_SENDER_ADDRESS_RELATION,$sender,$nowTimeReference);

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::RECEIVE_ADDRESS,$receiver->getAddress());
        $this->setBrotherEntity(WithdrawRequestEntityFactory::RECEIVE_ADDRESS_RELATION,$receiver,$nowTimeReference);

    }

    public function setError($error){

        $nowTimeReference = ['timestamp'=>time()];

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::ERROR_MESSAGE,$error);
        $this->setBrotherEntity(WithdrawRequestEntityFactory::HAS_ERROR_RELATION,'true',$nowTimeReference);

    }

    public function refundFailed(UserEntity $user = null){

        if (!$this->hasOnlyOneWay()) throw new \Exception("tx has not failed (to my knowledge");

        $notaryInterface = new CSNotaryInterface(BlockchainRouting::getBlockchainFromName('klaytn'));
        $firstOasisService = new FirstOasisService($notaryInterface);
        $userAddress = KlaytnAddressFactory::getAddress($this->get(WithdrawRequestEntityFactory::USER_SPEND_ADDRESS));
        $contract = KlaytnContractFactory::getContract($this->get(WithdrawRequestEntityFactory::SPEND_CONTRACT));
        $token = BlockchainContractStandard::getStandardsFromJson($this->get('spendTokenPath'));
        $token = $token = reset($token);

        $response = $firstOasisService->firstOasisTransaction->foKingSendTo($userAddress,$contract,$token
            ,1);

        var_dump($response);

        $refunder = 'firstOasis';
        if ($user) $refunder = $user ;

        $this->setBrotherEntity(WithdrawRequestEntityFactory::REFUNDED_BY,$refunder,time());

        $this->createOrUpdateRef(WithdrawRequestEntityFactory::REFUND_TX,$response->txHash);



        return $response ;





    }

    public function reprocessFailed(){

        if (!$this->hasOnlyOneWay()) throw new \Exception("tx has not failed (to my knowledge");




    }

    public function hasOnlyOneWay(){

        //we have deposit but not withdraw
        if ($this->get(WithdrawRequestEntityFactory::DEPOSIT_TX)
            && !$this->get(WithdrawRequestEntityFactory::WITHDRAW_TX)
            && !$this->get(WithdrawRequestEntityFactory::REFUND_TX))
        {
            return true ;
        }

        return false ;
}

}
