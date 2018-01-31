<?php

namespace Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Blockchain
{
    /**
     * @var array|Block[]
     */
    private $chain;

    /**
     * @var int
     */
    private $difficulty;

    /**
     * @param int $difficulty
     */
    public function __construct(int $difficulty)
    {
        $this->chain = [$this->createGenesisBlock()];
        $this->difficulty = $difficulty;
    }

    /**
     * @param Block $block
     * @return Blockchain
     */
    public function addBlock(Block $block): self
    {
        $block->setPreviousHash($this->getLatestBlock()->getHash());
        $block->mine($this->difficulty);
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
