<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
final class Blockchain implements BlockchainInterface
{
    /**
     * @var array|BlockInterface[]
     */
    private $chain;

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @param StrategyInterface $strategy
     * @param BlockInterface    $genesisBlock
     */
    public function __construct(StrategyInterface $strategy, BlockInterface $genesisBlock)
    {
        if (false === $strategy->supports($genesisBlock)) {
            throw new \InvalidArgumentException(
                sprintf('Genesis block of type "%s" is not a valid type one for this chain', get_class($genesisBlock))
            );
        }

        $this->strategy = $strategy;
        $this->chain    = [$genesisBlock];
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

        $this->chain[] = $block;
    }

    /**
     * @return BlockInterface
     */
    public function getLatestBlock(): BlockInterface
    {
        return end($this->chain);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $chainLength = count($this->chain);

        if ($this->chain[0]->getHash() !== $this->chain[0]->calculateHash()) {
            return false;
        }

        for ($i = 1; $i < $chainLength; ++$i) {
            $block = $this->chain[$i];
            $previousBlock = $this->chain[$i-1];

            // Block has been tempered
            if ($block->getHash() !== $block->calculateHash()) {
                return false;
            }

            // Block is not linked to previous block
            if ($block->getPreviousHash() !== $previousBlock->getHash()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return BlockchainIterator
     */
    public function getIterator(): BlockchainIterator
    {
        return new BlockchainIterator($this->chain);
    }
}
