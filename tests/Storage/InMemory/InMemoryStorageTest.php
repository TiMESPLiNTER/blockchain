<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Storage\InMemory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\InMemory\InMemoryStorage;
use Timesplinter\Blockchain\Storage\StorageInterface;

/**
 * @covers \Timesplinter\Blockchain\Storage\InMemory\InMemoryStorage
 */
final class InMemoryStorageTest extends TestCase
{
    public function testIsInstanceOfStorageInterface()
    {
        $storage = new InMemoryStorage();

        self::assertInstanceOf(StorageInterface::class, $storage);
    }

    public function testAddBlockStoresBlock()
    {
        $block = $this->getBlock();

        $storage = new InMemoryStorage();
        $storage->addBlock($block);

        self::assertSame($block, $storage->getBlock(0));
    }

    public function testCountReturnsNumberOfBlocksStored()
    {
        $storage = new InMemoryStorage();
        $storage->addBlock($this->getBlock());

        self::assertCount(1, $storage);
    }

    public function testGetBlockThrowsOutOfBoundsExceptionIfRequestingUnavailableBlock()
    {
        $illegalOffset = 42;

        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage(sprintf('Block at offset %d does not exist', $illegalOffset));

        $storage = new InMemoryStorage();

        $storage->getBlock($illegalOffset);
    }

    public function getLatestBlockReturnsNewestBlockInStorage()
    {
        $block1 = $this->getBlock();
        $block2 = $this->getBlock();

        $storage = new InMemoryStorage();

        $storage->addBlock($block1);
        $storage->addBlock($block2);

        self::assertSame($block2, $storage->getLatestBlock());
    }

    public function testCurrentThrowsOutOfBoundsExceptionIfNoBlocksAreAvailable()
    {
        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage('Block at offset 0 does not exist');

        $storage = new InMemoryStorage();

        $storage->current();
    }

    public function testCurrentReturnsFirstBlockStoredByDefault()
    {
        $block1 = $this->getBlock();
        $block2 = $this->getBlock();

        $storage = new InMemoryStorage();

        $storage->addBlock($block1);
        $storage->addBlock($block2);

        self::assertSame($block1, $storage->current());
    }

    /**
     * @return BlockInterface|MockObject
     */
    private function getBlock(): BlockInterface
    {
        return $this->getMockBuilder(BlockInterface::class)
            ->getMockForAbstractClass();
    }
}
