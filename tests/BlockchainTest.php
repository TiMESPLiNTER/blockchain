<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofBlock as Block;
use Timesplinter\Blockchain\StrategyInterface;

/**
 * @covers \Timesplinter\Blockchain\Blockchain
 */
final class BlockchainTest extends TestCase
{
    public function testInitialBlockchainLengthIsOneAndContainsTheGenesisBlock()
    {
        $genesisBlock = $this->getBlock();
        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::once())
            ->method('supports')
            ->with($genesisBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $genesisBlock);

        self::assertCount(1, $blockchain);
        self::assertSame($genesisBlock, $this->getBlocksOfBlockchain($blockchain)[0]);
    }

    public function testConstructorThrowsExceptionIfInvalidGenesisBlockHasBeenProvided()
    {
        $genesisBlock = $this->getBlock();

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf('Genesis block of type "%s" is not a valid type one for this chain', get_class($genesisBlock))
        );

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::once())
            ->method('supports')
            ->with($genesisBlock)
            ->willReturn(false);

        new Blockchain($strategy, $genesisBlock);
    }

    public function testAddBlockThrowsExceptionIfBlockIsNotSupportedByStrategy()
    {
        $genesisBlock = $this->getBlock();
        $secondBlock = $this->getBlock();

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf('Block of type "%s" is not supported by this strategy', get_class($secondBlock))
        );

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::exactly(2))
            ->method('supports')
            ->withConsecutive($genesisBlock, $secondBlock)
            ->willReturnOnConsecutiveCalls(true, false);

        $blockchain = new Blockchain($strategy, $genesisBlock);
        $blockchain->addBlock($secondBlock);
    }

    public function testAddBlockThrowsExceptionIfMiningBlockFails()
    {
        $genesisBlock = $this->getBlock();
        $secondBlock = $this->getBlock();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage(
            sprintf('Could not mine block with hash "%s"', $secondBlock->getHash())
        );

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::exactly(2))
            ->method('supports')
            ->withConsecutive($genesisBlock, $secondBlock)
            ->willReturn(true);

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(false);

        $blockchain = new Blockchain($strategy, $genesisBlock);
        $blockchain->addBlock($secondBlock);
    }

    public function testIsValidReturnsTrueForInitialBlockchain()
    {
        $genesisBlock = $this->getBlock();
        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::once())
            ->method('supports')
            ->with($genesisBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $genesisBlock);

        self::assertTrue($blockchain->isValid());
    }

    public function testIsValidReturnsFalseIfLatestBlockHasBeenTempered()
    {
        $genesisBlock = new Block('This is genesis', new \DateTime());

        $secondBlock = new Block('Second block', new \DateTime());
        $secondBlock->setPreviousHash($genesisBlock->getHash());

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::exactly(2))
            ->method('supports')
            ->withConsecutive($genesisBlock, $secondBlock)
            ->willReturn(true);

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $genesisBlock);
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
        $genesisBlock = new Block('This is genesis', new \DateTime());

        $secondBlock = new Block('Second block', new \DateTime());
        $secondBlock->setPreviousHash($genesisBlock->getHash());

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::exactly(2))
            ->method('supports')
            ->withConsecutive($genesisBlock, $secondBlock)
            ->willReturn(true);

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $genesisBlock);
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
        $genesisBlock = new Block('This is genesis', new \DateTime());

        $secondBlock = new Block('Second block', new \DateTime());
        $secondBlock->setPreviousHash($genesisBlock->getHash());

        $strategy = $this->getStrategy();

        $strategy
            ->expects(self::exactly(2))
            ->method('supports')
            ->withConsecutive($genesisBlock, $secondBlock)
            ->willReturn(true);

        $strategy
            ->expects(self::once())
            ->method('mine')
            ->with($secondBlock)
            ->willReturn(true);

        $blockchain = new Blockchain($strategy, $genesisBlock);
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
        $strategy = $this->getMockBuilder(StrategyInterface::class)
            ->setMethods(['supports', 'mine'])
            ->getMockForAbstractClass();

        return $strategy;
    }

    /**
     * @return BlockInterface|MockObject
     */
    private function getBlock(): BlockInterface
    {
        $block = $this->getMockBuilder(BlockInterface::class)
            ->getMockForAbstractClass();

        return $block;
    }

    /**
     * @param Blockchain $blockchain
     * @return array|BlockInterface[]
     */
    private function getBlocksOfBlockchain(Blockchain $blockchain): array
    {
        $blocks = [];

        foreach ($blockchain as $block) {
            $blocks[] = $block;
        }

        return $blocks;
    }
}
