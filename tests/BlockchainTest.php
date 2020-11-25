<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Block;
use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Storage\StorageInterface;
use Timesplinter\Blockchain\StrategyInterface;

/**
 * @covers \Timesplinter\Blockchain\Blockchain
 */
final class BlockchainTest extends TestCase
{
    public function testInitialBlockchainStoresTheGenesisBlock()
    {
        $genesisBlock = $this->getBlock();
        $storage = $this->getStorage();

        $storage
            ->expects(self::once())
            ->method('addBlock')
            ->with($genesisBlock)
            ->willReturn(true);

        $strategy = $this->getStrategy();

        new Blockchain($strategy, $storage, $genesisBlock);
    }

    public function testGetIteratorReturnsStorageAdapter()
    {
        $genesisBlock = $this->getBlock();
        $storage = $this->getStorage();

        $strategy = $this->getStrategy();

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);

        self::assertSame($storage, $blockchain->getIterator());
    }

    public function testCountDelegatesCallToStorageCount()
    {
        $count = 1;

        $genesisBlock = $this->getBlock();
        $storage = $this->getStorage();
        $storage
            ->expects(self::once())
            ->method('count')
            ->willReturn($count);

        $strategy = $this->getStrategy();

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);

        self::assertCount($count, $blockchain);
    }

    public function testGetBlockDelegatesCallToStorageGetBlock()
    {
        $position = 1;

        $genesisBlock = $this->getBlock();
        $storage = $this->getStorage();
        $storage
            ->expects(self::once())
            ->method('getBlock')
            ->with($position)
            ->willReturn($genesisBlock);

        $strategy = $this->getStrategy();

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);

        self::assertSame($genesisBlock, $blockchain->getBlock($position));
    }

    public function testAddBlockThrowsExceptionIfMiningBlockFails()
    {
        $genesisBlock = $this->getBlock();
        $secondBlock = $this->getBlock();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage(
            sprintf('Could not mine block with hash "%s"', $secondBlock->getHash())
        );

        $storage = $this->getStorage();
        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(false);

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);
        $blockchain->addBlock($secondBlock);
    }

    public function testIsValidReturnsTrueForInitialBlockchain()
    {
        $genesisBlock = $this->getBlock();
        $storage = $this->getStorage();
        $strategy = $this->getStrategy();

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);

        self::assertTrue($blockchain->isValid());
    }

    public function testIsValidReturnsFalseIfLatestBlockHasBeenTempered()
    {
        $genesisBlock = new Block('This is genesis');
        $secondBlock = new Block('Second block');

        $storage = $this->getStorage();

        $storage
            ->expects(self::exactly(2))
            ->method('addBlock')
            ->withConsecutive([$genesisBlock], [$secondBlock])
            ->willReturn(true);

        $storage
            ->expects(self::exactly(2))
            ->method('count')
            ->willReturn(2);

        $storage
            ->expects(self::exactly(5))
            ->method('getBlock')
            ->withConsecutive([0], [1], [0], [0], [1])
            ->willReturnOnConsecutiveCalls($genesisBlock, $secondBlock, $genesisBlock, $genesisBlock, $secondBlock);

        $storage
            ->expects(self::exactly(1))
            ->method('getLatestBlock')
            ->willReturn($genesisBlock);

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);
        $blockchain->addBlock($secondBlock);

        self::assertTrue($blockchain->isValid());

        // Temper block
        $dataProperty = new \ReflectionProperty(Block::class, 'data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($secondBlock, 'tempered!');

        self::assertFalse($blockchain->isValid());
    }

    public function testIsValidReturnsFalseIfGenesisBlockHasBeenTempered()
    {
        $genesisBlock = new Block('This is genesis');
        $secondBlock = new Block('Second block');

        $storage = $this->getStorage();

        $storage
            ->expects(self::exactly(2))
            ->method('addBlock')
            ->withConsecutive([$genesisBlock], [$secondBlock])
            ->willReturn(true);

        $storage
            ->expects(self::exactly(2))
            ->method('count')
            ->willReturn(2);

        $storage
            ->expects(self::exactly(4))
            ->method('getBlock')
            ->withConsecutive([0], [1], [0], [0])
            ->willReturnOnConsecutiveCalls($genesisBlock, $secondBlock, $genesisBlock, $genesisBlock);

        $storage
            ->expects(self::exactly(1))
            ->method('getLatestBlock')
            ->willReturn($genesisBlock);

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);
        $blockchain->addBlock($secondBlock);

        self::assertTrue($blockchain->isValid());

        // Temper block
        $dataProperty = new \ReflectionProperty(Block::class, 'data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($genesisBlock, 'tempered!');

        self::assertFalse($blockchain->isValid());
    }

    public function testIsValidReturnsFalseIfLatestBlockIsNotConnectedToPreviousBlock()
    {
        $genesisBlock = new Block('This is genesis');
        $secondBlock = new Block('Second block');

        $storage = $this->getStorage();

        $storage
            ->expects(self::exactly(2))
            ->method('addBlock')
            ->withConsecutive([$genesisBlock], [$secondBlock])
            ->willReturn(true);

        $storage
            ->expects(self::exactly(2))
            ->method('count')
            ->willReturn(2);

        $storage
            ->expects(self::exactly(6))
            ->method('getBlock')
            ->withConsecutive([0], [1], [0], [0], [1], [0])
            ->willReturnOnConsecutiveCalls(
                $genesisBlock, $secondBlock, $genesisBlock, $genesisBlock, $secondBlock, $genesisBlock
            );

        $storage
            ->expects(self::exactly(1))
            ->method('getLatestBlock')
            ->willReturn($genesisBlock);

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $storage, $genesisBlock);
        $blockchain->addBlock($secondBlock);

        self::assertTrue($blockchain->isValid());

        // Temper block
        $secondBlock->setPreviousHash('foo');

        self::assertFalse($blockchain->isValid());
    }

    /**
     * @return StrategyInterface|MockObject
     */
    private function getStrategy(): StrategyInterface
    {
        return $this->getMockBuilder(StrategyInterface::class)
            ->setMethodsExcept()
            ->getMockForAbstractClass();
    }

    /**
     * @return BlockInterface|MockObject
     */
    private function getBlock(): BlockInterface
    {
        return $this->getMockBuilder(BlockInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @return StorageInterface|MockObject
     */
    private function getStorage(): StorageInterface
    {
        return $this->getMockBuilder(StorageInterface::class)
            ->setMethodsExcept()
            ->getMockForAbstractClass();
    }
}
