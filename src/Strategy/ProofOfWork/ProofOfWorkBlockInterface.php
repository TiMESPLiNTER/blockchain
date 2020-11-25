<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Strategy\ProofOfWork;

use Timesplinter\Blockchain\BlockInterface;

interface ProofOfWorkBlockInterface extends BlockInterface
{
    public const HEADER_NONCE = 'nonce';
}
