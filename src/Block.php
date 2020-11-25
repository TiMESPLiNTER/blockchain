<?php

declare(strict_types=1);

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
     * @var null|string
     */
    private $previousHash;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array<string, string|int|float|boolean>
     */
    private $headers = [];

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->updateHash();
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getPreviousHash(): ?string
    {
        return $this->previousHash;
    }

    public function setPreviousHash(?string $previousHash): void
    {
        $this->previousHash = $previousHash;
        $this->updateHash();
    }

    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeader(string $name, $value): void
    {
        $this->headers[$name] = $value;
        $this->updateHash();
    }

    public function calculateHash(): string
    {
        return hash('sha256', (string) $this);
    }

    public function updateHash(): void
    {
        $this->hash = $this->calculateHash();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return serialize($this->data) . serialize($this->headers) . $this->previousHash;
    }
}
