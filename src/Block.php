<?php

namespace Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Block
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
     * @var int
     */
    private $nonce = 0;

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

    public function refreshHash()
    {
        $this->hash = $this->calculateHash();
    }

    public function mine(int $difficulty): bool
    {
        while(substr($this->hash, 0, $difficulty) !== str_repeat('0', $difficulty)) {
            ++$this->nonce;
            $this->refreshHash();
        }

        return true;
    }

    public function calculateHash()
    {
        return hash('sha256', $this->data . $this->timestamp->format('c') . $this->previousHash . $this->nonce);
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
        $this->refreshHash();
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
}
