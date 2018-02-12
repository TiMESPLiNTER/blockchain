<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;
use Psr\Log\LoggerInterface;
use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Node
{

    /**
     * @var Socket
     */
    private $serverSocket;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|Peer[]
     */
    private $peers = [];

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $port;

    /**
     * @var bool
     */
    private $running = true;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var Factory
     */
    private $socketFactory;

    /**
     * @param int             $port
     * @param array           $initialPeerAddresses
     * @param LoggerInterface $logger
     */
    public function __construct(int $port, array $initialPeerAddresses, LoggerInterface $logger)
    {
        pcntl_signal(SIGHUP, [$this, 'stop']);
        pcntl_signal(SIGINT, [$this, 'stop']);
        pcntl_signal(SIGTERM, [$this, 'stop']);

        $ownIpAddress = getHostByName(getHostName());
        $this->ip = '127.0.0.1';
        $this->port = $port;

        $this->socketFactory = new Factory();

        $this->logger = $logger;
        $this->serverSocket = $this->socketFactory->createServer($this->ip . ':' . $this->port);
        $this->serverSocket->setOption(SOL_SOCKET, SO_REUSEADDR, 1);
        $this->serverSocket->setBlocking(false);

        $this->logger->info('Listening for incoming connections: ' . $this->serverSocket->getSockName());

        foreach ($initialPeerAddresses as $peerAddress) {
            $this->peers[] = $peer = $this->createPeer($this->socketFactory->createClient($peerAddress));
            $peer->setConnectionDetails(PeerAddress::fromString($peerAddress));
        }
    }

    public function run()
    {
        $this->startTime = microtime(true);

        while ($this->running) {
            pcntl_signal_dispatch();

            if (false === $this->running) {
                break;
            }

            echo $this->printStatus();

            if (true === $this->serverSocket->selectRead()) {
                $this->peers[] = $peer = $this->createPeer($this->serverSocket->accept());
                $this->logger->info('New peer connected: ' . $peer->getSocket()->getSockName());
                $this->logger->info('Currently connected peers: ' . count($this->peers));

                $peer->request(new Request('INTRODUCE'));
            }

            foreach ($this->peers as $i => $peer) {
                $peer->request(new Request('GET_PEERS'));

                try {
                    $peer->handle();

                    if ($peer->getFailures() > 100) {
                        $peer->disconnect();
                        unset($this->peers[$i]);
                        $this->logger->info('Kicked peer with id: ' . $i);
                        $this->logger->info('Currently connected peers: ' . count($this->peers));
                    }
                } catch (Exception $e) {
                    if ($e->getCode() === SOCKET_ECONNRESET || $e->getCode() === SOCKET_EPIPE) {
                        // Lost connection to peer -> remove it from list
                        $peer->disconnect();
                        unset($this->peers[$i]);
                        $this->logger->info('Peer gone away.');
                        $this->logger->info('Currently connected peers: ' . count($this->peers));
                        continue;
                    }

                    throw $e;
                }
            }

            usleep(1000);
        }
    }

    public function stop()
    {
        $this->logger->info('Shutting down node...');

        $this->running = false;

        foreach ($this->peers as $peer) {
            $peer->disconnect();
        }

        try {
            $this->serverSocket
                ->shutdown()
                ->close();
        } catch (Exception $e) {
            // If socket is already disconnected - fine... else throw the exception
            if ($e->getCode() !== SOCKET_ENOTCONN) {
                throw $e;
            }
        }

        // Reset the signal handlers
        pcntl_signal(SIGHUP,  SIG_DFL);
        pcntl_signal(SIGINT,  SIG_DFL);
        pcntl_signal(SIGTERM, SIG_DFL);
    }

    private function printStatus(): string
    {
        $deleteCurrentLine = "\r\033[2K";

        $uptimeInSeconds = round(microtime(true) - $this->startTime);

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$uptimeInSeconds");
        $uptimeStr = $dtF->diff($dtT)->format('%a d, %h h, %i m and %s s');

        return $deleteCurrentLine . sprintf('peers: %s / uptime: %s', count($this->peers), $uptimeStr);
    }

    /**
     * @param array $requestData
     * @return Response
     */
    public function handleRequest(array $requestData): Response
    {
        $responseData = ['error' => 'Unknown request'];

        if ($requestData['data'] === 'INTRODUCE') {
            $responseData = ['ip' => $this->ip, 'port' => $this->port];
        } elseif ($requestData['data'] === 'GET_PEERS') {
            $peers = [];

            foreach ($this->peers as $peer) {
                if (null === $connectionDetails = $peer->getConnectionDetails()) {
                    continue;
                }

                $peers[] = [
                    'address' => $connectionDetails->getAddress(),
                    'port' => $connectionDetails->getPort()
                ];
            }

            $responseData = $peers;
        }

        return new Response($requestData['id'], $responseData);
    }

    /**
     * @param Peer $peer
     * @param Request $request The original request
     * @param array $responseData The response data for this request
     */
    public function handleResponse(Peer $peer, Request $request, array $responseData): void
    {

        if ($request->getData() === 'INTRODUCE') {
            $peer->setConnectionDetails(new PeerAddress($responseData['data']['ip'], $responseData['data']['port']));
            $this->logger->info('Peer introduced itself as: ' . (string) $peer->getConnectionDetails());
            return;
        }

        if ($request->getData() === 'GET_PEERS') {
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
                    $this->peers[] = $peer = $this->createPeer($this->socketFactory->createClient((string)$newPeerAddress));
                    $peer->setConnectionDetails($newPeerAddress);
                } catch (Exception $e) {
                    continue;
                }
            }

            $this->logger->info('Received '.$peerListCount.' new peers from ' . (string) $peer->getConnectionDetails());

            return;
        }

        var_dump($request, $responseData);
    }

    private function getPeerList(): array
    {
        $peers = [$this->ip . ':' . $this->port => true];

        foreach ($this->peers as $peer) {
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
        return new Peer($socket, $this, $this->logger);
    }
}