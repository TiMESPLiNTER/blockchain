<?php

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface BlockInterface
{
    /**
     * Returns the current hash of this block
     * @return string The hash of the block
     */
    public function getHash(): string;

    /**
     * Returns the timestamp of the block
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime;

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
