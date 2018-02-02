<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests;

use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Block;

/**
 * @covers \Timesplinter\Blockchain\Block
 */
final class BlockTest extends TestCase
{
    public function testHashIsCorrectAfterBlockInstantiation()
    {
        $data = 'foo';
        $timestamp = new \DateTime('1970-01-01');

        $block = $this->getBlock($data, $timestamp);

        $expectedHash = hash('sha256', serialize($data) . $timestamp->format('c'));
        self::assertEquals($expectedHash, $block->getHash());
    }

    public function testHashIncludesPreviousHashIfSet()
    {
        $data = 'foo';
        $timestamp = new \DateTime('1970-01-01');
        $previousHash = 'prev-hash';

        $block = $this->getBlock($data, $timestamp);
        $block->setPreviousHash($previousHash);

        $expectedHash = hash('sha256', serialize($data) . $timestamp->format('c') . $previousHash);
        self::assertEquals($expectedHash, $block->getHash());
    }

    public function testGettersAndSettersAndHashGetsUpdated()
    {
        $data = 'foo';
        $timestamp = new \DateTime('1970-01-01');
        $previousHash = 'prev-hash';

        $block = $this->getBlock($data, $timestamp);

        self::assertNull($block->getPreviousHash());
        self::assertEquals($data, $block->getData());
        self::assertEquals($timestamp, $block->getTimestamp());
        self::assertNotNull($oldHash = $block->getHash());

        $block->setPreviousHash($previousHash);

        self::assertEquals($previousHash, $block->getPreviousHash());
        self::assertNotEquals($oldHash, $block->getHash());
    }

    /**
     * @param mixed $data
     * @param \DateTime $timestamp
     * @return Block
     */
    private function getBlock($data, \DateTime $timestamp): Block
    {
        /** @var Block $block */
        $block = $this->getMockBuilder(Block::class)
            ->setConstructorArgs([$data, $timestamp])
            ->getMockForAbstractClass();

        return $block;
    }
}
