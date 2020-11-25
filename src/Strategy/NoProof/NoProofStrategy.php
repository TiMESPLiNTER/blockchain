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
}
