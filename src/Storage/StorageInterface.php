<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Storage;

use Timesplinter\Blockchain\BlockInterface;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface StorageInterface extends \SeekableIterator, \Countable
{
    /**
     * @param BlockInterface $block
     * @return bool
     */
    public function addBlock(BlockInterface $block): bool;

    /**
     * @return BlockInterface
     */
    public function getLatestBlock(): BlockInterface;

    /**
     * @param int $position
     * @return BlockInterface
     * @throws \OutOfBoundsException
     */
    public function getBlock(int $position): BlockInterface;
}