<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Examples;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */

use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\File\FileStorage;
use Timesplinter\Blockchain\Storage\File\LocalFile;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofBlock as Block;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofStrategy;

require __DIR__ . '/../vendor/autoload.php';

$serializeBlock = function(BlockInterface $block) {
    return gzcompress(serialize($block));
};

$deserializeBlock = function(string $blockData) {
    return unserialize(gzuncompress($blockData));
};

$fileStorage = new FileStorage(
    new LocalFile(sys_get_temp_dir() . '/blockchain.idx'),
    new LocalFile(sys_get_temp_dir() . '/blockchain.dat'),
    $serializeBlock,
    $deserializeBlock
);

$genesisBlock = new Block('This is the genesis block', new \DateTime('2017-01-01'));

$blockchain = new Blockchain(new NoProofStrategy(), $fileStorage, $genesisBlock);

$blockchain->addBlock(new Block('This is another block.', new \DateTime()));

echo 'Total number of blocks: ' , count($blockchain) , PHP_EOL , PHP_EOL;

foreach ($blockchain as $i => $block) {
    echo 'block ' , $i , ': ' , $block->getData() , PHP_EOL;
}
