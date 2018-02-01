<?php

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface BlockInterface
{
    /**
     * Hash of the block
     * @return string
     */
    public function getHash(): string;

    /**
     * Hash of the previous block this block is linked to
     * @return null|string
     */
    public function getPreviousHash(): ?string;

    /**
     * @param string $previousHash
     * @return void
     */
    public function setPreviousHash(string $previousHash): void;

    /**
     * A string representation of the block which will be used for calculating its hash
     * @return string
     */
    public function __toString(): string;
}
