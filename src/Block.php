<?php

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
final class Block implements BlockInterface
{

    /**
     * @var string
     */
    private $hash;

    /**
     * @var string|null
     */
    private $previousHash;

    /**
     * @var string
     */
    private $data;

    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @param string $data
     * @param \DateTime $timestamp
     */
    public function __construct(string $data, \DateTime $timestamp)
    {
        $this->data = $data;
        $this->timestamp = $timestamp;
        $this->hash = $this->calculateHash();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
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
     */
    public function setPreviousHash(?string $previousHash)
    {
        $this->previousHash = $previousHash;
        $this->hash = $this->calculateHash();
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    private function calculateHash()
    {
        return hash('sha256', $this->data . $this->timestamp->format('c') . $this->previousHash);
    }
}
