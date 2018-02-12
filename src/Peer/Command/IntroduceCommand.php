<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer\Command;

use Timesplinter\Blockchain\Peer\Peer;
use Timesplinter\Blockchain\Peer\PeerAddress;
use Timesplinter\Blockchain\Peer\Request;

class IntroduceCommand extends NodeCommand
{

    /**
     * @param array $requestData The original request data
     * @return array The response data as an array
     */
    public function handleRequest(array $requestData): array
    {
        $peerAddress = $this->node->getPeerAddress();

        return ['ip' => $peerAddress->getAddress(), 'port' => $peerAddress->getPort()];
    }

    /**
     * @param Peer    $peer
     * @param Request $request
     * @param array   $responseData
     */
    public function handleResponse(Peer $peer, Request $request, array $responseData): void
    {
        $peer->setConnectionDetails(new PeerAddress($responseData['data']['ip'], $responseData['data']['port']));
        //$this->logger->info('Peer introduced itself as: ' . (string) $peer->getConnectionDetails());
    }
}
