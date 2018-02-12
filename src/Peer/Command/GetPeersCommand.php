<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer\Command;

use Psr\Log\LoggerInterface;
use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;
use Timesplinter\Blockchain\Peer\Node;
use Timesplinter\Blockchain\Peer\Peer;
use Timesplinter\Blockchain\Peer\PeerAddress;
use Timesplinter\Blockchain\Peer\Request;

class GetPeersCommand extends NodeCommand
{

    /**
     * @var Socket
     */
    private $socketFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Node            $node
     * @param Factory         $socketFactory
     * @param LoggerInterface $logger
     */
    public function __construct(Node $node, Factory $socketFactory, LoggerInterface $logger)
    {
        parent::__construct($node);

        $this->socketFactory = $socketFactory;
        $this->logger = $logger;
    }

    /**
     * @param array $requestData The original request data
     * @return array The response data as an array
     */
    public function handleRequest(array $requestData): array
    {
        $peers = [];

        foreach ($this->node->getPeers() as $peer) {
            if (null === $connectionDetails = $peer->getConnectionDetails()) {
                continue;
            }

            $peers[] = [
                'address' => $connectionDetails->getAddress(),
                'port' => $connectionDetails->getPort()
            ];
        }

        return $peers;
    }

    /**
     * @param Peer    $peer
     * @param Request $request
     * @param array   $responseData
     */
    public function handleResponse(Peer $peer, Request $request, array $responseData): void
    {
        $peerListCount = count($responseData['data']);

        if ($peerListCount === 0) {
            return;
        }

        $peerList = $this->getPeerList();

        foreach ($responseData['data'] as $newPeer) {
            $newPeerAddress = new PeerAddress($newPeer['address'], $newPeer['port']);

            if (isset($peerList[(string) $newPeerAddress])) {
                continue;
            }

            try {
                $peer = $this->createPeer($this->socketFactory->createClient((string)$newPeerAddress));
                $peer->setConnectionDetails($newPeerAddress);

                $this->node->addPeer($peer);
            } catch (Exception $e) {
                continue;
            }
        }

        //$this->logger->info('Received '.$peerListCount.' new peers from ' . (string) $peer->getConnectionDetails());
    }

    private function getPeerList(): array
    {
        $peers = [(string) $this->node->getPeerAddress() => true];

        foreach ($this->node->getPeers() as $peer) {
            if (null === $peer->getConnectionDetails()) {
                continue;
            }

            $peers[(string) $peer->getConnectionDetails()] = true;
        }

        return $peers;
    }


    /**
     * @param Socket $socket
     * @return Peer
     */
    private function createPeer(Socket $socket): Peer
    {
        return new Peer($socket, $this->node, $this->logger);
    }
}
