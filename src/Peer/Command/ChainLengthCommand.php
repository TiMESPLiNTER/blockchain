<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer\Command;

use Psr\Log\LoggerInterface;
use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;
use Timesplinter\Blockchain\BlockchainInterface;
use Timesplinter\Blockchain\Peer\BlockchainSynchronizer;
use Timesplinter\Blockchain\Peer\Node;
use Timesplinter\Blockchain\Peer\Peer;
use Timesplinter\Blockchain\Peer\PeerAddress;
use Timesplinter\Blockchain\Peer\Request;

class ChainLengthCommand implements CommandInterface
{

    /**
     * @var BlockchainInterface
     */
    private $blockchain;

    /**
     * @var BlockchainSynchronizer
     */
    private $blockchainSynchronizer;

    /**
     * @param BlockchainInterface    $blockchain
     * @param BlockchainSynchronizer $blockchainSynchronizer
     */
    public function __construct(BlockchainInterface $blockchain, BlockchainSynchronizer $blockchainSynchronizer)
    {
        $this->blockchain = $blockchain;
        $this->blockchainSynchronizer = $blockchainSynchronizer;
    }

    /**
     * @param array $requestData The original request data
     * @return array The response data as an array
     */
    public function handleRequest(array $requestData): array
    {
        return ['length' => $this->blockchain->count()];
    }

    /**
     * @param Peer    $peer
     * @param Request $request
     * @param array   $responseData
     */
    public function handleResponse(Peer $peer, Request $request, array $responseData): void
    {
        $blockchainLength = $responseData['data']['length'];

        $this->blockchainSynchronizer->setChainLength($blockchainLength);
    }
}
