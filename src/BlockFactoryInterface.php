<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

interface BlockFactoryInterface
{
    public function create($data, \DateTime $timestamp): BlockInterface;
}
