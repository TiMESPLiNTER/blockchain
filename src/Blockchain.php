<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

use Timesplinter\Blockchain\Storage\StorageInterface;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
final class Blockchain implements BlockchainInterface
{

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StrategyInterface $strategy
     * @param StorageInterface $storage
     * @param BlockInterface $genesisBlock
     */
    public function __construct(StrategyInterface $strategy, StorageInterface $storage, BlockInterface $genesisBlock)
    {
        if (false === $strategy->supports($genesisBlock)) {
            throw new \InvalidArgumentException(
                sprintf('Genesis block of type "%s" is not a valid type one for this chain', get_class($genesisBlock))
            );
        }

        $this->strategy = $strategy;
        $this->storage  = $storage;

        if (0 === $this->storage->count()) {
            $this->storage->addBlock($genesisBlock);
        }
    }

    /**
     * @param BlockInterface $block
     * @return void
     */
    public function addBlock(BlockInterface $block): void
    {
        if (false === $this->strategy->supports($block)) {
            throw new \InvalidArgumentException(
                sprintf('Block of type "%s" is not supported by this strategy', get_class($block))
            );
        }

        $block->setPreviousHash($this->getLatestBlock()->getHash());

        if (false === $this->strategy->mine($block)) {
            throw new \RuntimeException(
                sprintf('Could not mine block with hash "%s"', $block->getHash())
            );
        }

        $this->storage->addBlock($block);
    }

    /**
     * @return BlockInterface
     */
    public function getLatestBlock(): BlockInterface
    {
        return $this->storage->getLatestBlock();
    }

    /**
     * Checks if the blockchain is in a valid state
     * @return bool
     */
    public function isValid(): bool
    {
        $chainLength = count($this->storage);

        for ($i = 0; $i < $chainLength; ++$i) {
            $block = $this->storage->getBlock($i);

            // Block has been tempered
            if ($block->getHash() !== $block->calculateHash()) {
                return false;
            }

            // Skip checking link to previous block for genesis block
            if (0 === $i) {
                continue;
            }

            $previousBlock = $this->storage->getBlock($i-1);

            // Block is not linked to previous block
            if ($block->getPreviousHash() !== $previousBlock->getHash()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns an iterator to iterate over the blocks in this blockchain
     * @return StorageInterface
     */
    public function getIterator(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Returns the total number of blocks currently in the blockchain
     * @return int Number of blocks within this chain
     */
    public function count()
    {
        return $this->storage->count();
    }

    /**
     * Returns the block at the specified position
     * @param int $position
     * @return BlockInterface
     * @throws \OutOfBoundsException
     */
    public function getBlock(int $position): BlockInterface
    {
        return $this->storage->getBlock($position);
    }
}
