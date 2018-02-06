<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Storage\File;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\File\FileInterface;
use Timesplinter\Blockchain\Storage\File\FileStorage;
use Timesplinter\Blockchain\Storage\File\FileStorageException;

/**
 * @covers \Timesplinter\Blockchain\Storage\File\FileStorage
 */
final class FileStorageTest extends TestCase
{
    public function testInitialisationThrowsExceptionIfIndexAndDataFileIsTheSame()
    {
        self::expectException(FileStorageException::class);
        self::expectExceptionMessage('Index and data file cannot be the same');

        $path = 'file.txt';

        $emptyCallback = function () {};

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($path);

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($path);

        new FileStorage($idxFile, $datFile, $emptyCallback, $emptyCallback);
    }

    public function testInitialisationThrowsExceptionIfDataFileCannotBeOpened()
    {
        $idxFilePath = 'file.idx';
        $dataFilePath = 'file.dat';

        self::expectException(FileStorageException::class);
        self::expectExceptionMessage(sprintf('Could not open data file: %s', $dataFilePath));

        $emptyCallback = function () {};

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($idxFilePath);

        $datFile = $this->getFile();
        $datFile
            ->expects(self::exactly(2))
            ->method('getPath')
            ->willReturn($dataFilePath);

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(false);

        new FileStorage($idxFile, $datFile, $emptyCallback, $emptyCallback);
    }

    public function testInitialisationThrowsExceptionIfIndexFileCannotBeOpened()
    {
        $idxFilePath = 'file.idx';
        $dataFilePath = 'file.dat';

        self::expectException(FileStorageException::class);
        self::expectExceptionMessage(sprintf('Could not open index file: %s', $idxFilePath));

        $emptyCallback = function () {};

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::exactly(2))
            ->method('getPath')
            ->willReturn($idxFilePath);

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(false);

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($dataFilePath);

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        new FileStorage($idxFile, $datFile, $emptyCallback, $emptyCallback);
    }

    public function testInitialisationWritesInitialBlockCountOnEmptyIndexFile()
    {
        $blockCount = 0;

        $idxFilePath = 'file.idx';
        $dataFilePath = 'file.dat';

        $emptyCallback = function () {};

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($idxFilePath);

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::once())
            ->method('read')
            ->with(4)
            ->willReturn('');

        $idxFile
            ->expects(self::once())
            ->method('overwrite')
            ->with(pack('L', $blockCount));

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($dataFilePath);

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $storage = new FileStorage($idxFile, $datFile, $emptyCallback, $emptyCallback);

        self::assertEquals($blockCount, $storage->count());

        $offsetTableLengthProperty = new \ReflectionProperty(FileStorage::class, 'offsetTableLength');
        $offsetTableLengthProperty->setAccessible(true);

        self::assertEquals(($blockCount + 1) * 4, $offsetTableLengthProperty->getValue($storage));
    }

    public function testInitialisationReadsAndSetsBlockCountFromIndexFile()
    {
        $blockCount = 42;

        $idxFilePath = 'file.idx';
        $dataFilePath = 'file.dat';

        $emptyCallback = function () {};

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($idxFilePath);

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::once())
            ->method('read')
            ->with(4)
            ->willReturn(pack('L', $blockCount));

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($dataFilePath);

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $storage = new FileStorage($idxFile, $datFile, $emptyCallback, $emptyCallback);

        self::assertEquals($blockCount, $storage->count());

        $offsetTableLengthProperty = new \ReflectionProperty(FileStorage::class, 'offsetTableLength');
        $offsetTableLengthProperty->setAccessible(true);

        self::assertEquals(($blockCount + 1) * 4, $offsetTableLengthProperty->getValue($storage));
    }

    public function testGetBlockThrowsExceptionIfPositionIsNotValid()
    {
        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage('Block at offset 1 does not exist');

        $blockCount = 1;
        $blockData = 'block data here';

        $idxFilePath = 'file.idx';
        $dataFilePath = 'file.dat';

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($idxFilePath);

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::once())
            ->method('read')
            ->with(4)
            ->willReturn(pack('L', $blockCount));

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($dataFilePath);

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $datFile
            ->expects(self::never())
            ->method('read');

        $storage = new FileStorage(
            $idxFile,
            $datFile,
            function (BlockInterface $block) { return $block->getData(); },
            function ($data) {}
        );

        $storage->getBlock(1);
    }

    public function testGetBlockReadsGenesisBlockFromDataFileReturnsAndCachesIt()
    {
        $blockCount = 1;
        $blockData = 'block data here';

        $idxFilePath = 'file.idx';
        $dataFilePath = 'file.dat';

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($idxFilePath);

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::exactly(2))
            ->method('read')
            ->withConsecutive([4], [4])
            ->willReturnOnConsecutiveCalls(pack('L', $blockCount), pack('L', 42));

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn($dataFilePath);

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $datFile
            ->expects(self::once())
            ->method('read')
            ->with(42)
            ->willReturn($blockData);

        $deserializedBlock = $this->getBlock();

        $storage = new FileStorage(
            $idxFile,
            $datFile,
            function (BlockInterface $block) { return $block->getData(); },
            function (string $data) use ($deserializedBlock) {

                $deserializedBlock
                    ->expects(self::any())
                    ->method('getData')
                    ->willReturn($data);

                return $deserializedBlock;
            }
        );

        $cacheProperty = new \ReflectionProperty(FileStorage::class, 'blockCache');
        $cacheProperty->setAccessible(true);

        self::assertCount(0, $cacheProperty->getValue($storage));

        $block = $storage->getBlock(0);

        self::assertInstanceOf(BlockInterface::class, $block);
        self::assertEquals($blockData, $block->getData());

        self::assertCount(1, $cachedBlocks = $cacheProperty->getValue($storage));
        self::assertSame($block, $deserializedBlock);
    }

    public function testGetBlockReadsSecondBlockFromDataFileReturnsAndCachesIt()
    {
        $blockCount = 2;
        $blockData = 'block data here';

        $prevDataOffset = 12;
        $nextDataOffset = 42;

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.idx');

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::exactly(3))
            ->method('read')
            ->withConsecutive([4], [4], [4])
            ->willReturnOnConsecutiveCalls(
                pack('L', $blockCount),
                pack('L', $prevDataOffset),
                pack('L', $nextDataOffset)
            );

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.dat');

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $datFile
            ->expects(self::once())
            ->method('seek')
            ->with($prevDataOffset);

        $datFile
            ->expects(self::once())
            ->method('read')
            ->with($nextDataOffset - $prevDataOffset)
            ->willReturn($blockData);

        $deserializedBlock = $this->getBlock();

        $storage = new FileStorage(
            $idxFile,
            $datFile,
            function (BlockInterface $block) { return $block->getData(); },
            function (string $data) use ($deserializedBlock) {

                $deserializedBlock
                    ->expects(self::any())
                    ->method('getData')
                    ->willReturn($data);

                return $deserializedBlock;
            }
        );

        $cacheProperty = new \ReflectionProperty(FileStorage::class, 'blockCache');
        $cacheProperty->setAccessible(true);

        self::assertCount(0, $cacheProperty->getValue($storage));

        $block = $storage->getBlock(1);

        self::assertInstanceOf(BlockInterface::class, $block);
        self::assertEquals($blockData, $block->getData());

        self::assertCount(1, $cachedBlocks = $cacheProperty->getValue($storage));
        self::assertSame($block, $deserializedBlock);
    }

    public function testGetBlockServesBlocksFromCacheIfTheyAreCached()
    {
        $blockCount = 2;
        $blockData = 'block data here';

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.idx');

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::once())
            ->method('read')
            ->with(4)
            ->willReturn(pack('L', $blockCount));

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.dat');

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $datFile
            ->expects(self::never())
            ->method('read');

        $block = $this->getBlock();
        $block
            ->expects(self::any())
            ->method('getData')
            ->willReturn($blockData);

        $storage = new FileStorage(
            $idxFile,
            $datFile,
            function (BlockInterface $block) { self::fail('This test should not write anything'); },
            function (string $data) { self::fail('This test should not read anything'); }
        );

        $cacheProperty = new \ReflectionProperty(FileStorage::class, 'blockCache');
        $cacheProperty->setAccessible(true);
        $cacheProperty->setValue($storage, [1 => $block]);

        $block = $storage->getBlock(1);

        self::assertInstanceOf(BlockInterface::class, $block);
        self::assertEquals($blockData, $block->getData());
    }

    public function testAddBlockWritesDataToFileAndPushesIntoCache()
    {
        $blockCount = 0;
        $blockData = 'block data here';
        $newEofOffset = 42;

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.idx');

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::once())
            ->method('read')
            ->with(4)
            ->willReturn(pack('L', $blockCount));

        $idxFile
            ->expects(self::once())
            ->method('append')
            ->with(pack('L', $newEofOffset));

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.dat');

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $datFile
            ->expects(self::once())
            ->method('append')
            ->with($blockData);

        $datFile
            ->expects(self::never())
            ->method('read');

        $datFile
            ->expects(self::once())
            ->method('tell')
            ->willReturn($newEofOffset);

        $block = $this->getBlock();
        $block
            ->expects(self::any())
            ->method('getData')
            ->willReturn($blockData);

        $storage = new FileStorage(
            $idxFile,
            $datFile,
            function (BlockInterface $block) {
                return $block->getData();
            },
            function (string $data) { self::fail('This test should not read anything'); }
        );

        $cacheProperty = new \ReflectionProperty(FileStorage::class, 'blockCache');
        $cacheProperty->setAccessible(true);

        self::assertCount(0, $cacheProperty->getValue($storage));

        self::assertTrue($storage->addBlock($block));

        self::assertCount(1, $cachedBlocks = $cacheProperty->getValue($storage));
        self::assertSame($block, $cachedBlocks[0]);
    }

    public function testGetLatestBlockReturnsNewestBlockInstance()
    {
        $blockCount = 2;
        $blockData = 'block data here';

        $prevDataOffset = 12;
        $nextDataOffset = 42;

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.idx');

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::exactly(3))
            ->method('read')
            ->withConsecutive([4], [4], [4])
            ->willReturnOnConsecutiveCalls(
                pack('L', $blockCount),
                pack('L', $prevDataOffset),
                pack('L', $nextDataOffset)
            );

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.dat');

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $datFile
            ->expects(self::once())
            ->method('seek')
            ->with($prevDataOffset);

        $datFile
            ->expects(self::once())
            ->method('read')
            ->with($nextDataOffset - $prevDataOffset)
            ->willReturn($blockData);

        $deserializedBlock = $this->getBlock();

        $storage = new FileStorage(
            $idxFile,
            $datFile,
            function (BlockInterface $block) { return $block->getData(); },
            function (string $data) use ($deserializedBlock) {

                $deserializedBlock
                    ->expects(self::any())
                    ->method('getData')
                    ->willReturn($data);

                return $deserializedBlock;
            }
        );

        $block = $storage->getLatestBlock();

        self::assertInstanceOf(BlockInterface::class, $block);
        self::assertEquals($deserializedBlock, $block);
    }

    public function testBlockCacheGetsCutOffOnceItReachesMax()
    {
        $blockCount = 2;
        $blockData = 'block data here';

        $prevDataOffset = 12;
        $nextDataOffset = 42;

        $idxFile = $this->getFile();
        $idxFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.idx');

        $idxFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $idxFile
            ->expects(self::exactly(3))
            ->method('read')
            ->withConsecutive([4], [4], [4])
            ->willReturnOnConsecutiveCalls(
                pack('L', $blockCount),
                pack('L', $prevDataOffset),
                pack('L', $nextDataOffset)
            );

        $datFile = $this->getFile();
        $datFile
            ->expects(self::once())
            ->method('getPath')
            ->willReturn('file.dat');

        $datFile
            ->expects(self::once())
            ->method('open')
            ->willReturn(true);

        $datFile
            ->expects(self::once())
            ->method('seek')
            ->with($prevDataOffset);

        $datFile
            ->expects(self::once())
            ->method('read')
            ->with($nextDataOffset - $prevDataOffset)
            ->willReturn($blockData);

        $deserializedBlock = $this->getBlock();

        $storage = new FileStorage(
            $idxFile,
            $datFile,
            function (BlockInterface $block) { return $block->getData(); },
            function (string $data) use ($deserializedBlock) {

                $deserializedBlock
                    ->expects(self::any())
                    ->method('getData')
                    ->willReturn($data);

                return $deserializedBlock;
            }
        );

        $cacheProperty = new \ReflectionProperty(FileStorage::class, 'blockCache');
        $cacheProperty->setAccessible(true);

        $oldCache = [
            30 => 'bar',
            31 => 'foo',
            32 => 'bar',
            33 => 'foo',
            34 => 'bar',
            35 => 'foo',
            36 => 'bar',
            37 => 'baz',
            38 => 'foo',
            39 => 'bar',
        ];

        $cacheProperty->setValue($storage, $oldCache);

        self::assertCount(10, $cacheProperty->getValue($storage));

        $block = $storage->getBlock(1);

        self::assertInstanceOf(BlockInterface::class, $block);
        self::assertEquals($blockData, $block->getData());

        self::assertCount(10, $cachedBlocks = $cacheProperty->getValue($storage));

        $expectedCache = $oldCache;
        unset($expectedCache[30]);
        $expectedCache[1] = $block;

        self::assertSame($expectedCache, $cachedBlocks);
    }

    /**
     * @return FileInterface|MockObject
     */
    private function getFile(): FileInterface
    {
        $file = $this->getMockBuilder(FileInterface::class)
            ->setMethods(['getPath', 'open', 'read', 'tell', 'overwrite', 'append'])
            ->getMockForAbstractClass();

        return $file;
    }

    /**
     * @return BlockInterface|MockObject
     */
    private function getBlock(): BlockInterface
    {
        return $this->getMockBuilder(BlockInterface::class)
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
    }
}
