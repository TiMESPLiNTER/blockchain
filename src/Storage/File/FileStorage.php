<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Storage\File;

use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\AbstractStorage;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
final class FileStorage extends AbstractStorage
{

    const CACHE_SIZE_MAX = 10;

    /**
     * @var FileInterface
     */
    private $fpData;

    /**
     * @var FileInterface
     */
    private $fpIndex;

    /**
     * @var int
     */
    private $offsetTableLength = null;

    /**
     * @var int
     */
    private $blockCount = 0;

    /**
     * @var callable
     */
    private $serializeBlock;

    /**
     * @var callable
     */
    private $deserializeBlock;

    /**
     * @var array|BlockInterface[]
     */
    private $blockCache = [];

    public function __construct(
        FileInterface $indexFile,
        FileInterface $dataFile,
        callable $serializeBlock,
        callable $deserializeBlock
    ) {
        $this->fpData = $dataFile;
        $this->fpIndex = $indexFile;
        $this->serializeBlock = $serializeBlock;
        $this->deserializeBlock = $deserializeBlock;

        if ($this->fpData->getPath() === $this->fpIndex->getPath()) {
            throw new FileStorageException('Index and data file cannot be the same');
        }

        if (false === $this->fpData->open('c+')) {
            throw new FileStorageException(sprintf('Could not open data file: %s', $this->fpData->getPath()));
        }

        if (false === $this->fpIndex->open('c+')) {
            throw new FileStorageException(sprintf('Could not open index file: %s', $this->fpIndex->getPath()));
        }

        if (4 === strlen($blockCountData = $this->fpIndex->read(4))) {
            $this->blockCount = unpack('L', $blockCountData)[1];
        } else {
            $this->fpIndex->overwrite(pack('L', $this->blockCount), 0);
        }

        $this->offsetTableLength = ($this->blockCount + 1) * 4;
    }

    /**
     * @param BlockInterface $block
     * @return bool
     */
    public function addBlock(BlockInterface $block): bool
    {
        $data = ($this->serializeBlock)($block);

        // Append block data
        $this->fpData->append($data);

        $this->fpData->seek(0, SEEK_END);
        $endOffset = $this->fpData->tell();

        // Add offset to header
        $this->fpIndex->append(pack('L', $endOffset));
        $this->offsetTableLength += 4;

        ++$this->blockCount;

        // Update block count
        $this->fpIndex->overwrite(pack('L', $this->blockCount), 0);

        $this->pushBlockToCache($this->blockCount - 1, $block);

        return true;
    }

    /**
     * @return BlockInterface
     */
    public function getLatestBlock(): BlockInterface
    {
        $this->seek($this->blockCount-1);

        return $this->current();
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
        return $this->blockCount;
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

        if (true === isset($this->blockCache[$position])) {
            return $this->blockCache[$position];
        }

        $offsetTablePosition = ($position > 0 ? $position : $position + 1) * 4;

        $this->fpIndex->seek($offsetTablePosition);

        if ($position === 0) {
            $dataOffset = 0;
            $nextDataOffset = unpack('L', $this->fpIndex->read(4))[1];
        } else {
            $dataOffset = unpack('L', $this->fpIndex->read(4))[1];
            $nextDataOffset = unpack('L', $this->fpIndex->read(4))[1];
        }

        $this->fpData->seek($dataOffset);
        $data = $this->fpData->read($nextDataOffset - $dataOffset);

        $block = ($this->deserializeBlock)($data);

        $this->pushBlockToCache($position, $block);

        return $block;
    }

    public function __destruct()
    {
        $this->fpData->close();
        $this->fpIndex->close();
    }

    /**
     * @param int $position
     * @return bool
     */
    protected function isPositionValid(int $position): bool
    {
        return $position >= 0 && $position < $this->blockCount;
    }

    private function pushBlockToCache($position, BlockInterface $block): void
    {
        $this->blockCache[$position] = $block;

        while (count($this->blockCache) > self::CACHE_SIZE_MAX) {
            // like array_shift($this->blockCache) but preserve keys
            reset($this->blockCache);
            unset($this->blockCache[key($this->blockCache)]);
        }
    }
}
