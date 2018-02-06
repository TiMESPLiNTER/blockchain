<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Storage;

use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\StorageInterface;

abstract class AbstractStorage implements StorageInterface
{

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return BlockInterface Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->getBlock($this->position);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->isPositionValid($this->position);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Seeks to a position
     * @link http://php.net/manual/en/seekableiterator.seek.php
     * @param int $position <p>
     * The position to seek to.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function seek($position)
    {
        if (false === $this->isPositionValid($position)) {
            throw new \OutOfBoundsException(sprintf('Invalid position (%d)', $position));
        }

        $this->position = $position;
    }

    /**
     * @param int $position
     * @return bool
     */
    abstract protected function isPositionValid(int $position): bool;
}
