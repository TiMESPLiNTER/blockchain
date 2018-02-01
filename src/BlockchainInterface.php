<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

interface BlockchainInterface
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
     * Checks if the blockchain is in a valid state
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return array|BlockInterface[]
     */
    public function getChain(): array;
}
