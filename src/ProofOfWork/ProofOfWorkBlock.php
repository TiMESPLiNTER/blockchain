<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\ProofOfWork;

use Timesplinter\Blockchain\Block;

final class ProofOfWorkBlock extends Block implements ProofOfWorkBlockInterface
{

    /**
     * @var int
     */
    private $nonce;

    /**
     * @param string    $data
     * @param \DateTime $timestamp
     */
    public function __construct(string $data, \DateTime $timestamp)
    {
        parent::__construct($data, $timestamp);

        $this->nonce = null;
    }

    /**
     * @return null|int
     */
    public function getNonce(): ?int
    {
        return $this->nonce;
    }

    /**
     * @param int $nonce
     */
    public function setNonce(int $nonce): void
    {
        $this->nonce = $nonce;
        $this->updateHash();
    }

    public function __toString(): string
    {
        return parent::__toString() . $this->nonce;
    }
}
