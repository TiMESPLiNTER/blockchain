<?php

namespace Timesplinter\Blockchain\Tests;

use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\ProofOfWork\ProofOfWorkBlock;
use Timesplinter\Blockchain\ProofOfWork\ProofOfWorkStrategy;

require __DIR__ . '/../vendor/autoload.php';

$blockchain = new Blockchain(new ProofOfWorkStrategy(5));

$start = microtime(true);

$blockchain->addBlock($block1 = new ProofOfWorkBlock('foo', new \DateTime('2018-01-01')));
$blockchain->addBlock($block2 = new ProofOfWorkBlock('bar', new \DateTime('2018-01-22')));

echo round(microtime(true) - $start, 4) , ' seconds' , PHP_EOL;

var_dump($block1->getHash(), $block2->getHash(), $blockchain->isValid());
