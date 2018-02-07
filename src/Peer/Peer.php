<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Peer implements PeerInterface
{

    /**
     * Returns the (IP) address through which this peer is reachable
     * @return string
     */
    public function getAddress(): string
    {
        // TODO: Implement getAddress() method.
    }

    /**
     * Returns a list of peers this peer is connected to
     * @return array|PeerInterface[]
     */
    public function getPeers(): array
    {
        // TODO: Implement getPeers() method.
    }

    /**
     * Checks if this peer is still alive
     * @return bool
     */
    public function alive(): bool
    {
        // TODO: Implement alive() method.
    }
}