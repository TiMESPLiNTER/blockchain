<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Transaction;

use Phactor\Signature;

class Transaction implements \JsonSerializable
{

    /**
     * @var null|string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @var string
     */
    private $signature;

    /**
     * @param string $from
     * @param string $to
     * @param float  $amount
     */
    public function __construct(?string $from, string $to, float $amount)
    {
        $this->from   = $from;
        $this->to     = $to;
        $this->amount = $amount;
        $this->timestamp = new \DateTime();
    }

    /**
     * @param string $privateKey
     * @return void
     * @throws \Exception
     */
    public function sign(string $privateKey): void
    {
        $this->signature = (new Signature())->Generate(json_encode($this), $privateKey);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isSignatureValid(): bool
    {
        if (null === $this->signature) {
            return false;
        }

        return (new Signature())->Verify($this->signature, json_encode($this), $this->getFrom());
    }

    /**
     * @return null|string
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @return null|string
     */
    public function getSignature(): ?string
    {
        return $this->signature;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return ['from' => $this->from, 'to' => $this->to, 'amount' => $this->amount];
    }
}
