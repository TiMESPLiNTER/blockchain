<?php

namespace Timesplinter\Blockchain\Tests;

use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkBlock as Block;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkStrategy;
use Timesplinter\Blockchain\Transaction\Transaction;
use Timesplinter\Blockchain\Transaction\TransactionBlockchain;

require __DIR__ . '/../vendor/autoload.php';

//
// Pub/Priv key generation
//
$info = (new \Phactor\Key())->GenerateKeypair();

$publicAddress = $info['public_key_compressed'];
$privateKey = $info['private_key_hex'];

echo 'Public address: ' , $publicAddress , PHP_EOL;
echo 'Private key: ' , $privateKey , ' (keep this a secret!)' ,  PHP_EOL;
echo PHP_EOL , '---' , PHP_EOL , PHP_EOL;

//
// Blockchain stuff
//
$blockchain = new Blockchain(
    new ProofOfWorkStrategy(2),
    new Block('This is the genesis block', new \DateTime('1970-01-01'))
);

$txPool = new TransactionBlockchain($blockchain);

$start = microtime(true);

$blockchain->addBlock($block1 = new Block([new Transaction(null, $publicAddress, 200)], new \DateTime('2018-01-01')));
echo 'Block 1 successfully mined. Hash: ' , $block1->getHash() , PHP_EOL;

$blockchain->addBlock($block2 = new Block([], new \DateTime('2018-01-22')));
echo 'Block 2 successfully mined. Hash: ' , $block2->getHash() , PHP_EOL;

echo 'Duration: ' , round(microtime(true) - $start, 4) , ' seconds' , PHP_EOL;

echo 'Blockchain valid: ' , print_r($blockchain->isValid(), true) , PHP_EOL;

$tx = new Transaction($publicAddress, 'john', 10);
$tx->sign($privateKey);

echo 'Transaction valid: ' , print_r($txPool->addTransaction($tx), true) , PHP_EOL;
