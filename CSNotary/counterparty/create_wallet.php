<?php
/**
 * Created by EverdreamSoft.
 * User: Shaban Shaame
 * Date: 12.05.20
 * Time: 11:47
 */

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Address\SegwitAddress;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\WitnessProgram;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\TransactionOutput;



$classmap = require __DIR__ . '/../../vendor/autoload.php';

$network = \BitWasp\Bitcoin\Bitcoin::getNetwork();

$priv = (new BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory)->generateUncompressed(new \BitWasp\Bitcoin\Crypto\Random\Random());
$publicKey = $priv->getPublicKey();
$pubKeyHash = $publicKey->getPubKeyHash();
$privateWif = $priv->toWif();
### Key hash types
//echo "key hash types\n";
$p2pkh = new PayToPubKeyHashAddress($pubKeyHash);
$oldAddress = $p2pkh->getAddress() ;

$response = new WalletInterface($oldAddress,$privateWif);

$reponseJson = json_encode($response);


echo $reponseJson .PHP_EOL;

class WalletInterface {

    public $address  ;
    public $privateKey  ;

    public function __construct(string $publicKey,string $privateKey)
    {
        $this->address = $publicKey ;
        $this->privateKey = $privateKey ;

    }


}