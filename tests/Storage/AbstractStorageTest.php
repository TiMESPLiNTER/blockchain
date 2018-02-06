<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Storage;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\AbstractStorage;

/**
 * @covers \Timesplinter\Blockchain\Storage\AbstractStorage
 */
final class AbstractStorageTest extends TestCase
{
    public function testCurrentCallsGetBlockWithCurrentPosition()
    {
        $block = $this->getBlock();

        $storage = $this->getStorage();
        $storage
            ->expects(self::once())
            ->method('getBlock')
            ->with(1)
            ->willReturn($block);

        self::assertEquals(0, $storage->key());

        $storage->next();

        self::assertEquals(1, $storage->key());

        self::assertSame($block, $storage->current());
    }

    public function testSeekSetsPosition()
    {
        $seekPosition = 4;

        $storage = $this->getStorage();
        $storage
            ->expects(self::once())
            ->method('isPositionValid')
            ->with($seekPosition)
            ->willReturn(true);

        $storage->seek($seekPosition);

        self::assertEquals($seekPosition, $storage->key());
    }

    public function testSeekThrowsOutOfBoundsExceptionOnIllegalPosition()
    {
        $seekPosition = 1;

        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage(sprintf('Invalid position (%d)', $seekPosition));

        $storage = $this->getStorage();
        $storage
            ->expects(self::once())
            ->method('isPositionValid')
            ->with($seekPosition)
            ->willReturn(false);

        $storage->seek($seekPosition);
    }

    public function testRewindSetsPositionToBeginning()
    {
        $seekPosition = 1;

        $storage = $this->getStorage();
        $storage
            ->expects(self::once())
            ->method('isPositionValid')
            ->with($seekPosition)
            ->willReturn(true);

        $storage->seek($seekPosition);

        self::assertEquals($seekPosition, $storage->key());

        $storage->rewind();

        self::assertEquals(0, $storage->key());
    }

    public function testNextIncreasesPosition()
    {
        $storage = $this->getStorage();

        $storage->addBlock($this->getBlock());
        $storage->addBlock($this->getBlock());

        self::assertEquals(0, $storage->key());

        $storage->next();

        self::assertEquals(1, $storage->key());
    }

    public function testValidChecksIfPositionExistsOrNot()
    {
        $storage = $this->getStorage();
        $storage
            ->expects(self::once())
            ->method('isPositionValid')
            ->with(1)
            ->willReturn(true);

        $storage->next();

        self::assertTrue($storage->valid());
    }

    /**
     * @return AbstractStorage|MockObject
     */
    private function getStorage(): AbstractStorage
    {
        $storage = $this->getMockBuilder(AbstractStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock', 'isPositionValid'])
            ->getMockForAbstractClass();

        return $storage;
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
