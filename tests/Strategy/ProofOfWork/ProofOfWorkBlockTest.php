<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests\Strategy\ProofOfWork;

use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Block;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkBlock;

/**
 * @covers \Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkBlock
 */
class ProofOfWorkBlockTest extends TestCase
{

    public function testIsAnInstanceOfBlock()
    {
        $block = new ProofOfWorkBlock('test', new \DateTime());

        self::assertInstanceOf(Block::class, $block);
    }

    public function testGettersAndSetters()
    {
        $block = new ProofOfWorkBlock('test', new \DateTime());

        self::assertNull($block->getNonce());

        $nonceValue = 42;

        $block->setNonce($nonceValue);

        self::assertEquals($nonceValue, $block->getNonce());
    }
}
