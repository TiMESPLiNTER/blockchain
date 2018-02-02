<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

final class BlockchainIterator implements \SeekableIterator
{

    /**
     * @var array
     */
    private $chain;

    /**
     * @var int
     */
    private $position;

    /**
     * @param array $chain
     */
    public function __construct(array $chain)
    {
        $this->chain = $chain;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return BlockInterface Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->chain[$this->position];
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
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->chain[$this->position]);
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
        if (!isset($this->array[$position])) {
            throw new \OutOfBoundsException(sprintf('Invalid blockchain position (%d)', $position));
        }

        $this->position = $position;
    }
}
