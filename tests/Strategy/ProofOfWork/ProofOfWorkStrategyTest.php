<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Strategy\ProofOfWork;

use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkBlock;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkStrategy;
use Timesplinter\Blockchain\StrategyInterface;

/**
 * @covers \Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkStrategy
 */
final class ProofOfWorkStrategyTest extends TestCase
{
    public function testImplementsStrategyInterface()
    {
        $strategy = new ProofOfWorkStrategy(0);

        self::assertInstanceOf(StrategyInterface::class, $strategy);
    }

    public function testSupportsProofOfWorkBlockType()
    {
        $block = $this->getBlock();

        $strategy = new ProofOfWorkStrategy(0);

        self::assertTrue($strategy->supports($block));
    }

    public function testMineReturnsTrue()
    {
        $block = $this->getBlock();

        $strategy = new ProofOfWorkStrategy(0);

        self::assertTrue($strategy->mine($block));
    }

    public function testMineRespectsDifficulty()
    {
        $block = $this->getBlock();

        self::assertStringStartsNotWith('0', $block->getHash());
        self::assertNull($block->getNonce());

        $strategy = new ProofOfWorkStrategy(1);

        self::assertTrue($strategy->mine($block));
        self::assertStringStartsWith('0', $block->getHash());
        self::assertEquals(11, $block->getNonce());
    }

    private function getBlock(): ProofOfWorkBlock
    {
        return new ProofOfWorkBlock('test', new \DateTime('2017-01-01'));
    }
}
