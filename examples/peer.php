<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Examples;

use Psr\Log\LoggerInterface;
use Timesplinter\Blockchain\Blockchain;
use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\Peer\BlockchainSynchronizer;
use Timesplinter\Blockchain\Peer\Node;
use Timesplinter\Blockchain\Storage\File\FileStorage;
use Timesplinter\Blockchain\Storage\File\LocalFile;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkBlock as Block;
use Timesplinter\Blockchain\Strategy\ProofOfWork\ProofOfWorkStrategy;

require __DIR__ .'/../vendor/autoload.php';

$showLevels = ['emergency', 'alert', 'error', 'critical', 'warning', 'info'];

$logger = new class($showLevels) implements LoggerInterface {

    private $levels;

    public function __construct(array $levels)
    {
        $this->levels = $levels;
    }

    public function emergency($message, array $context = [])
    {
        $this->log('emergency', $message);
    }

    public function alert($message, array $context = [])
    {
        $this->log('alert', $message);
    }

    public function critical($message, array $context = [])
    {
        $this->log('critical', $message);
    }

    public function error($message, array $context = [])
    {
        $this->log('error', $message);
    }

    public function warning($message, array $context = [])
    {
        $this->log('warning', $message);
    }

    public function notice($message, array $context = [])
    {
        $this->log('notice', $message);
    }

    public function info($message, array $context = [])
    {
        $this->log('info', $message);
    }

    public function debug($message, array $context = [])
    {
        $this->log('debug', $message);
    }

    public function log($level, $message, array $context = [])
    {
        if (false === in_array($level, $this->levels, true)) {
            return;
        }

        //printf("\r" . date('Y-m-d H:i:s') . ' [%s] %s' . PHP_EOL, $level, $message);
    }
};

if (false === isset($argv[1])) {
    echo 'Please provide a port (and optional an address [ip:]port) as a first argument to run this node.' , PHP_EOL;
    exit;
} else {
    $bindAddressPort = explode(':' , $argv[1]);

    if (count($bindAddressPort) === 1) {
        $binAddress = null;
        $bindPort = (int) $bindAddressPort[0];
    } else {
        $binAddress = $bindAddressPort[0];
        $bindPort = (int) $bindAddressPort[1];
    }
}

$initialPeers = [];

if (true === isset($argv[2])) {
    $initialPeers = array_map(
        function ($peerAddress) {
            return trim($peerAddress);
        },
        explode(',', $argv[2])
    );
}

ob_implicit_flush();

$serializeBlock = function(BlockInterface $block) {
    return gzcompress(serialize($block));
};

$deserializeBlock = function(string $blockData) {
    return unserialize(gzuncompress($blockData));
};

$blockchain = new Blockchain(
    new ProofOfWorkStrategy(2),
    new FileStorage(
        new LocalFile(__DIR__ . '/blockchain.idx'),
        new LocalFile(__DIR__ . '/blockchain.dat'),
        $serializeBlock,
        $deserializeBlock
    ),
    new Block('This is the genesis block!', new \DateTime('1970-01-01'))
);

$blockchainSynchronizer = new BlockchainSynchronizer($blockchain);

$node = new Node($blockchain, $blockchainSynchronizer, $binAddress, $bindPort, $initialPeers, $logger);
$node->run();
