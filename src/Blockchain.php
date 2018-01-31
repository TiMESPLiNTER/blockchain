<?php

namespace Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Blockchain
{
    /**
     * @var array|BlockInterface[]
     */
    private $chain;

    /**
     * @var MineStrategyInterface
     */
    private $mineStrategy;

    /**
     * @param MineStrategyInterface $mineStrategy
     */
    public function __construct(MineStrategyInterface $mineStrategy)
    {
        $this->chain = [$this->createGenesisBlock()];
        $this->mineStrategy = $mineStrategy;
    }

    /**
     * @param Block $block
     * @return Blockchain
     */
    public function addBlock(Block $block): self
    {
        $block->setPreviousHash($this->getLatestBlock()->getHash());

        if (false === $this->mineStrategy->mine($block)) {
            throw new \RuntimeException(
                sprintf('Could not mine block with hash "%s"', $block->getHash())
            );
        }

        $this->chain[] = $block;

        return $this;
    }

    /**
     * @return Block
     */
    public function getLatestBlock(): Block
    {
        return end($this->chain);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $chainLength = count($this->chain);

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
     * @return Block
     */
    private function createGenesisBlock(): Block
    {
        return new Block('This is the genesis block', new \DateTime());
    }
}
