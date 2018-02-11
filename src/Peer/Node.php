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

        $factory = new Factory();

        $this->logger = $logger;
        $this->serverSocket = $factory->createServer($this->ip . ':' . $this->port);
        $this->serverSocket->setOption(SOL_SOCKET, SO_REUSEADDR, 1);
        $this->serverSocket->setBlocking(false);

        $this->logger->info('Listening for incoming connections: ' . $this->serverSocket->getSockName());

        foreach ($initialPeerAddresses as $peerAddress) {
            $this->peers[] = $peer = $this->createPeer($factory->createClient($peerAddress));
            //$peer->request(new Request('GET_PEERS'));
        }
    }

    public function run()
    {
        while ($this->running) {
            pcntl_signal_dispatch();

            if (false === $this->running) {
                break;
            }

            if (true === $this->serverSocket->selectRead()) {
                $this->peers[] = $peer = $this->createPeer($this->serverSocket->accept());
                $this->logger->info('New peer connected: ' . $peer->getSocket()->getSockName());
                $this->logger->info('Currently connected peers: ' . count($this->peers));

                $peer->request(new Request('INTRODUCE'));
                $peer->request(new Request('GET_PEERS'));
            }

            foreach ($this->peers as $i => $peer) {
                try {
                    $peer->handle();

                    if ($peer->getFailures() > 100) {
                        unset($this->peers[$i]);
                        $this->logger->info('Kicked peer with id: ' . $i);
                        $this->logger->info('Currently connected peers: ' . count($this->peers));
                    }
                } catch (Exception $e) {
                    if ($e->getCode() === SOCKET_ECONNRESET) {
                        unset($this->peers[$i]);
                        $this->logger->info('Peer gone away.');
                        $this->logger->info('Currently connected peers: ' . count($this->peers));
                    }
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
            $this->logger->info('Received peer list ('.count($responseData['data']).' peers) from: ' . (string) $peer->getConnectionDetails());
            return;
        }

        var_dump($request, $responseData);
    }

    /**
     * @param Socket $socket
     * @return Peer
     */
    private function createPeer(Socket $socket): Peer
    {
        return new Peer($socket, $this);
    }
}