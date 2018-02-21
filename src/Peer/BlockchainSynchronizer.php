<?php

declare(strict_types=1);


namespace Timesplinter\Blockchain\Peer;


use Timesplinter\Blockchain\BlockchainInterface;

class BlockchainSynchronizer
{

    /**
     * @var int
     */
    private $longestChainLength = 0;

    /**
     * @var BlockchainInterface
     */
    private $blockchain;

    /**
     * @param BlockchainInterface $blockchain
     */
    public function __construct(BlockchainInterface $blockchain)
    {
        $this->blockchain = $blockchain;
    }

    /**
     * @param int $chainLength
     */
    public function setChainLength(int $chainLength): void
    {
        if ($chainLength > $this->longestChainLength) {
            $this->longestChainLength = $chainLength;
        }
    }
}
