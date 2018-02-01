<?php

namespace Timesplinter\Blockchain\Tests;

use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofBlock as Block;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofStrategy;

require __DIR__ . '/../vendor/autoload.php';

$blockchain = new Blockchain(new NoProofStrategy());

$start = microtime(true);

$blockchain->addBlock($block1 = new Block('foo', new \DateTime('2018-01-01')));
$blockchain->addBlock($block2 = new Block('bar', new \DateTime('2018-01-22')));

echo round(microtime(true) - $start, 4) , ' seconds' , PHP_EOL;

var_dump($block1->getHash(), $block2->getHash(), $blockchain->isValid());