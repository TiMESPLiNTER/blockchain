<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Tests;

use PHPUnit\Framework\TestCase;
use Timesplinter\Blockchain\Block;
use Timesplinter\Blockchain\BlockInterface;

/**
 * @covers \Timesplinter\Blockchain\Block
 */
final class BlockTest extends TestCase
{
    public function testHashIsCorrectAfterBlockInstantiation()
    {
        $data = 'foo';
        $timestamp = new \DateTime('1970-01-01');

        $block = new Block($data);
        $block->setHeader(BlockInterface::HEADER_TIMESTAMP, $timestamp->format('c'));

        $expectedHash = hash(
            'sha256',
            serialize($data) . serialize([BlockInterface::HEADER_TIMESTAMP => $timestamp->format('c')])
        );
        self::assertEquals($expectedHash, $block->getHash());
    }

    public function testHashIncludesPreviousHashIfSet()
    {
        $data = 'foo';
        $timestamp = new \DateTime('1970-01-01');
        $previousHash = 'prev-hash';

        $block = new Block($data);
        $block->setHeader(BlockInterface::HEADER_TIMESTAMP, $timestamp->format('c'));
        $block->setPreviousHash($previousHash);

        $expectedHash = hash(
            'sha256',
            serialize($data) . serialize([BlockInterface::HEADER_TIMESTAMP => $timestamp->format('c')]) . $previousHash
        );
        self::assertEquals($expectedHash, $block->getHash());
    }

    public function testGettersAndSettersAndHashGetsUpdated()
    {
        $data = 'foo';
        $previousHash = 'prev-hash';

        $block = new Block($data);

        self::assertNull($block->getPreviousHash());
        self::assertEquals($data, $block->getData());
        self::assertNotNull($oldHash = $block->getHash());

        $block->setPreviousHash($previousHash);

        self::assertEquals($previousHash, $block->getPreviousHash());
        self::assertNotEquals($oldHash, $block->getHash());
    }
}
