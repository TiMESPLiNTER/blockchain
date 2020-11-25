<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

final class BlockFactory implements BlockFactoryInterface
{
    public function create($data, \DateTime $timestamp): BlockInterface
    {
        $block = new Block($data);

        $block->setHeader(BlockInterface::HEADER_TIMESTAMP, $timestamp->format('c'));
        $block->updateHash();

        return $block;
    }
}
