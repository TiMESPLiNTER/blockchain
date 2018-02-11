<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Examples;

use Psr\Log\LoggerInterface;
use Timesplinter\Blockchain\Peer\Node;

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

        printf("\r" . date('Y-m-d H:i:s') . ' [%s] %s' . PHP_EOL, $level, $message);
    }
};

if (false === isset($argv[1])) {
    echo 'Please provide a port to run.' , PHP_EOL;
    exit;
} else {
    $port = (int) $argv[1];
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

$node = new Node($port, $initialPeers, $logger);
$node->run();
