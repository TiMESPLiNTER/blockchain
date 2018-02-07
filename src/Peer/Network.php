<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Network
{

    const PEERS_MAX = 1000;

    /**
     * @var array|Peer[]
     */
    private $peers = [];

    /**
     * @var PeerInterface
     */
    private $ownPeer;

    /**
     * Discovers new peers
     */
    public function discover()
    {
        $this->recursiveDiscover($this->peers);
    }

    /**
     * Starts listening for peers to call it
     */
    public function bind()
    {

    }

    /**
     * @return array|PeerInterface[]
     */
    public function getPeers(): array
    {
        return $this->peers;
    }

    /**
     * Removes known peers which aren't alive anymore and adds new peers of alive peers
     * @param array|PeerInterface[] $peers The peers to be discovered
     */
    private function recursiveDiscover(array $peers)
    {
        foreach ($peers as $i => $peer) {
            $known = in_array($peer, $this->peers, true);
            $alive = $peer->alive();

            if (true === $known && false === $alive) {
                // Known peer but it's dead -> remove it
                unset($this->peers[$i]);
                continue;
            }

            if (true === $alive) {
                if (false === $known) {
                    // Alive peer and unknown -> add it
                    $this->peers[] = $peer;
                }

                // Discover all peers of that new peer
                $this->recursiveDiscover($peer->getPeers());
            }
        }
    }
}