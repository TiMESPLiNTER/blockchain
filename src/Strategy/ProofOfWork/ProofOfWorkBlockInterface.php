<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Strategy\ProofOfWork;

use Timesplinter\Blockchain\BlockInterface;

interface ProofOfWorkBlockInterface extends BlockInterface
{

    /**
     * @return null|int
     */
    public function getNonce(): ?int;

    /**
     * @param int $nonce
     */
    public function setNonce(int $nonce): void;
}
