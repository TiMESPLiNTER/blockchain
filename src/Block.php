<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
abstract class Block implements BlockInterface
{

    /**
     * @var string
     */
    private $hash;

    /**
     * @var null|string
     */
    private $previousHash;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @param mixed $data
     * @param \DateTime $timestamp
     */
    public function __construct($data, \DateTime $timestamp)
    {
        $this->data = $data;
        $this->timestamp = $timestamp;
        $this->updateHash();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return null|string
     */
    public function getPreviousHash(): ?string
    {
        return $this->previousHash;
    }

    /**
     * @param null|string $previousHash
     * @return void
     */
    public function setPreviousHash(?string $previousHash): void
    {
        $this->previousHash = $previousHash;
        $this->updateHash();
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function calculateHash(): string
    {
        return hash('sha256', (string) $this);
    }

    /**
     * @return void
     */
    public function updateHash()
    {
        $this->hash = $this->calculateHash();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return serialize($this->data) . $this->timestamp->format('c') . $this->previousHash;
    }
}
