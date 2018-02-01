<?php

namespace Timesplinter\Blockchain;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface BlockInterface
{
    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @return null|string
     */
    public function getPreviousHash(): ?string;
}
