<?php

namespace Timesplinter\Blockchain\Tests;

use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkBlock as Block;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkStrategy;
use Timesplinter\Blockchain\Transaction\Transaction;
use Timesplinter\Blockchain\Transaction\TransactionPool;

require __DIR__ . '/../vendor/autoload.php';

$blockchain = new Blockchain(new ProofOfWorkStrategy(5));

$txPool = new TransactionPool($blockchain);

$start = microtime(true);

$blockchain->addBlock($block1 = new Block([new Transaction(null, 'pascal', 200)], new \DateTime('2018-01-01')));
echo 'Block 1 successfully mined. Hash: ' , $block1->getHash() , PHP_EOL;

$blockchain->addBlock($block2 = new Block([], new \DateTime('2018-01-22')));
echo 'Block 2 successfully mined. Hash: ' , $block2->getHash() , PHP_EOL;

echo 'Duration: ' , round(microtime(true) - $start, 4) , ' seconds' , PHP_EOL;

echo 'Blockchain valid: ' , print_r($blockchain->isValid(), true) , PHP_EOL;

echo 'Transaction valid: ' , print_r($txPool->addTransaction(new Transaction('pascal', 'john', 10)), true) , PHP_EOL;
