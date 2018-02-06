<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Storage\InMemory;

use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\AbstractStorage;

final class InMemoryStorage extends AbstractStorage
{

    /**
     * @var array
     */
    private $chain = [];

    /**
     * @param BlockInterface $block
     * @return bool
     */
    public function addBlock(BlockInterface $block): bool
    {
        $this->chain[] = $block;

        return true;
    }

    /**
     * @return BlockInterface
     */
    public function getLatestBlock(): BlockInterface
    {
        return end($this->chain);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->chain);
    }

    /**
     * @param int $position
     * @return BlockInterface
     * @throws \OutOfBoundsException
     */
    public function getBlock(int $position): BlockInterface
    {
        if (false === $this->isPositionValid($position)) {
            throw new \OutOfBoundsException(
                sprintf('Block at offset %d does not exist', $position)
            );
        }

        return $this->chain[$position];
    }

    /**
     * @param int $position
     * @return bool
     */
    protected function isPositionValid(int $position): bool
    {
        return isset($this->chain[$position]);
    }
}
