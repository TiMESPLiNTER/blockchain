<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Strategy\ProofOfWork;

use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Block;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkBlockInterface;
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
        self::assertNull($block->getHeader(ProofOfWorkBlockInterface::HEADER_NONCE));

        $strategy = new ProofOfWorkStrategy(1);

        self::assertTrue($strategy->mine($block));
        self::assertStringStartsWith('0', $block->getHash());
        self::assertEquals(9, $block->getHeader(ProofOfWorkBlockInterface::HEADER_NONCE));
    }

    private function getBlock(): BlockInterface
    {
        return new Block('test');
    }
}
