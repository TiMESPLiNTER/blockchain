<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Strategy\NoProof;

use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Block;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofBlock;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofStrategy;
use Timesplinter\Blockchain\StrategyInterface;

/**
 * @covers \Timesplinter\Blockchain\Strategy\NoProof\NoProofStrategy
 */
final class NoProofStrategyTest extends TestCase
{
    public function testImplementsStrategyInterface()
    {
        $strategy = new NoProofStrategy();

        self::assertInstanceOf(StrategyInterface::class, $strategy);
    }

    public function testMineReturnsTrue()
    {
        $block = $this->getBlock();

        $strategy = new NoProofStrategy();

        self::assertTrue($strategy->mine($block));
    }

    private function getBlock(): BlockInterface
    {
        return new Block('test');
    }
}
