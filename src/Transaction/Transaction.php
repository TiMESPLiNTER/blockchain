<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Transaction;

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
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return ['from' => $this->from, 'to' => $this->to, 'amount' => $this->amount];
    }
}
