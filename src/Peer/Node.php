<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

use Psr\Log\LoggerInterface;
use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;
use Timesplinter\Blockchain\Peer\Command\CommandInterface;
use Timesplinter\Blockchain\Peer\Command\GetPeersCommand;
use Timesplinter\Blockchain\Peer\Command\IntroduceCommand;

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
     * @var CommandInterface[]
     */
    private $commands;

    /**
     * @param null|string     $address
     * @param int             $port
     * @param array           $initialPeerAddresses
     * @param LoggerInterface $logger
     */
    public function __construct(?string $address, int $port, array $initialPeerAddresses, LoggerInterface $logger)
    {
        pcntl_signal(SIGHUP, [$this, 'stop']);
        pcntl_signal(SIGINT, [$this, 'stop']);
        pcntl_signal(SIGTERM, [$this, 'stop']);

        $this->ip = (null === $address) ? getHostByName(getHostName()) : $address;
        $this->port = $port;

        $this->socketFactory = new Factory();

        $this->logger = $logger;
        $this->serverSocket = $this->socketFactory->createServer($this->ip . ':' . $this->port);
        $this->serverSocket->setOption(SOL_SOCKET, SO_REUSEADDR, 1);
        $this->serverSocket->setBlocking(false);

        $this->commands = [
            'INTRODUCE' => new IntroduceCommand($this),
            'GET_PEERS' => new GetPeersCommand($this, $this->socketFactory, $this->logger),
        ];

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
        $uptimeStr = $dtF->diff($dtT)->format('%a:%H:%I:%S');

        return sprintf(
            $deleteCurrentLine .'listen on: %s / peers: %s / uptime: %s',
            $this->serverSocket->getSockName(),
            count($this->peers),
            $uptimeStr
        );
    }

    /**
     * @param array $requestData
     * @return Response
     */
    public function handleRequest(array $requestData): Response
    {

        if (isset($this->commands[$requestData['data']])) {
            $responseData = $this->commands[$requestData['data']]->handleRequest($requestData);
        } else {
            $responseData = ['error' => 'Unknown request'];
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

        if (false === isset($this->commands[$request->getData()])) {
            var_dump($request, $responseData);
            return;
        }

        $this->commands[$request->getData()]->handleResponse($peer, $request, $responseData);
    }

    /**
     * Returns the peer address of this node
     * @return PeerAddress
     */
    public function getPeerAddress(): PeerAddress
    {
        return new PeerAddress($this->ip, $this->port);
    }

    /**
     * Returns all the peers this node is currently connected to
     * @return array|Peer[]
     */
    public function getPeers(): array
    {
        return $this->peers;
    }

    /**
     * Adds a new peer to the list
     * @param Peer $peer
     */
    public function addPeer(Peer $peer): void
    {
        $this->peers[] = $peer;
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
