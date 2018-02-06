<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

use Timesplinter\Blockchain\Storage\StorageInterface;

interface BlockchainInterface extends \IteratorAggregate, \Countable
{

    /**
     * Adds new block to the chain
     * @param BlockInterface $block
     * @return void
     */
    public function addBlock(BlockInterface $block): void;

    /**
     * Returns latest block of the chain
     * @return BlockInterface
     */
    public function getLatestBlock(): BlockInterface;

    /**
     * Returns the block at the specified position
     * @param int $position
     * @return BlockInterface
     * @throws \OutOfBoundsException
     */
    public function getBlock(int $position): BlockInterface;

    /**
     * Checks if the blockchain is in a valid state
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Returns an iterator to iterate over the blocks in this blockchain
     * @return StorageInterface
     */
    public function getIterator(): StorageInterface;
}
