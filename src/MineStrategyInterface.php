<?php

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface MineStrategyInterface
{
    /**
     * @param BlockInterface $block
     * @return bool
     */
    public function mine(BlockInterface $block): bool;
}
