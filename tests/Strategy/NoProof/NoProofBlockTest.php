<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Strategy\NoProof;

use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Block;
use Timesplinter\Blockchain\Strategy\NoProof\NoProofBlock;

/**
 * @covers \Timesplinter\Blockchain\Strategy\NoProof\NoProofBlock
 */
final class NoProofBlockTest extends TestCase
{
    public function testIsAnInstanceOfBlock()
    {
        $block = new NoProofBlock('test', new \DateTime());

        self::assertInstanceOf(Block::class, $block);
    }
}
