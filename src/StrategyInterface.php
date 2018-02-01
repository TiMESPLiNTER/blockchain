<?php

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface StrategyInterface
{
    /**
     * @param BlockInterface $block The block to be mined
     * @return bool True if mining was successful otherwise false
     */
    public function mine(BlockInterface $block): bool;

    /**
     * @return BlockInterface
     */
    public function getGenesisBlock(): BlockInterface;

    /**
     * @param BlockInterface $block The block to be checked
     * @return bool True if block type is supported otherwise false
     */
    public function supports(BlockInterface $block): bool;
}
