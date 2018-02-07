<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface PeerInterface
{

    /**
     * Returns the (IP) address through which this peer is reachable
     * @return string
     */
    public function getAddress(): string;

    /**
     * Checks if this peer is still alive
     * @return bool
     */
    public function alive(): bool;

    /**
     * Returns a list of peers this peer is connected to
     * @return array|PeerInterface[]
     */
    public function getPeers(): array;
}
