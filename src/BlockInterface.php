<?php

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface BlockInterface
{
    public const HEADER_TIMESTAMP = 'timestamp';

    /**
     * Returns the current hash of this block
     * @return string The hash of the block
     */
    public function getHash(): string;

    /**
     * Hash of the previous block this block is linked to
     * @return null|string
     */
    public function getPreviousHash(): ?string;

    /**
     * Returns the data stored within this block
     * @return mixed
     */
    public function getData();

    /**
     * Sets the hash of the previous block and therefor links this block to it
     * @param string $previousHash
     * @return void
     */
    public function setPreviousHash(string $previousHash): void;

    /**
     * Sets a value for a header name
     * @param string $name
     * @param string|int|float|boolean $value
     */
    public function setHeader(string $name, $value): void;

    /**
     * Returns the value for a header name. If the header does not exist null is returned
     * @param string $name
     * @return string|int|float|boolean|null
     */
    public function getHeader(string $name);

    /**
     * Returns all headers of this block
     * @return array<string, string|int|float|boolean>
     */
    public function getHeaders(): array;

    /**
     * Updates the hash of this block
     * @return void
     */
    public function updateHash();

    /**
     * Calculates the hash of this block
     * @return string The calculated hash for this block
     */
    public function calculateHash(): string;

    /**
     * A string representation of the block which will be used for calculating its hash
     * @return string
     */
    public function __toString(): string;
}
