<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Strategy\NoProof;

use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\StrategyInterface;

final class NoProofStrategy implements StrategyInterface
{

    /**
     * @param BlockInterface $block The block to be mined
     * @return bool True if mining was successful otherwise false
     */
    public function mine(BlockInterface $block): bool
    {
        return true;
    }

    /**
     * @return BlockInterface
     */
    public function getGenesisBlock(): BlockInterface
    {
        return new NoProofBlock('This is the genesis block', new \DateTime('1970-01-01'));
    }

    /**
     * @param BlockInterface $block The block to be checked
     * @return bool True if block type is supported otherwise false
     */
    public function supports(BlockInterface $block): bool
    {
        return $block instanceof NoProofBlock;
    }
}
