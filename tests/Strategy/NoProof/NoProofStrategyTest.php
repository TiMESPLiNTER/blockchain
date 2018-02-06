<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Strategy\NoProof;

use PHPUnit\Framework\TestCase;
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

    public function testSupportsNoProofBlockType()
    {
        $block = $this->getBlock();

        $strategy = new NoProofStrategy();

        self::assertTrue($strategy->supports($block));
    }

    public function testMineReturnsTrue()
    {
        $block = $this->getBlock();

        $strategy = new NoProofStrategy();

        self::assertTrue($strategy->mine($block));
    }

    private function getBlock(): NoProofBlock
    {
        return new NoProofBlock('test', new \DateTime());
    }
}
