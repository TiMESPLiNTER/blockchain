<?php

namespace Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
final class ProofOfWorkMineStrategy implements MineStrategyInterface
{

    /**
     * @var int
     */
    private $difficulty;

    /**
     * @param int $difficulty
     */
    public function __construct(int $difficulty)
    {
        $this->difficulty = $difficulty;
    }

    /**
     * @param BlockInterface $block
     * @return bool
     */
    public function mine(BlockInterface $block): bool
    {
        $nonce = 0;
        $prefix = str_repeat('0', $this->difficulty);

        while(substr(hash('sha256', $block->getHash() . $nonce), 0, $this->difficulty) !== $prefix) {
            ++$nonce;
        }

        return true;
    }
}
