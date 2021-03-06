<?php

namespace Timesplinter\Blockchain\Examples;

use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\Storage\InMemory\InMemoryStorage;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofBlock as Block;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofStrategy;

require __DIR__ . '/../vendor/autoload.php';

$blockchain = new Blockchain(
    new NoProofStrategy(),
    new InMemoryStorage(),
    new Block('This is the genesis block', new \DateTime('1970-01-01'))
);

$start = microtime(true);

$blockchain->addBlock($block1 = new Block('foo', new \DateTime('2018-01-01')));
$blockchain->addBlock($block2 = new Block('bar', new \DateTime('2018-01-22')));

echo round(microtime(true) - $start, 4) , ' seconds' , PHP_EOL;

foreach ($blockchain as $i => $block) {
    echo 'Block ' , $i , ': ' , $block->getHash() , PHP_EOL;
}

echo 'Blockchain valid: ' , ((int) $blockchain->isValid()) , PHP_EOL;
