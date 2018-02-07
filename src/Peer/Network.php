<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

use Psr\Log\LoggerInterface;
use Socket\Raw\Factory;
use Socket\Raw\Socket;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Network
{

    const NETWORK_DEFAULT_PORT = 4771;

    const PEERS_MAX = 1000;

    const PACKET_SEPARATOR = "\0";

    /**
     * @var array|Peer[]
     */
    private $peers = [];

    /**
     * @var PeerInterface
     */
    private $ownPeer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Socket
     */
    private $sock;

    /**
     * @var array|Client[]
     */
    private $clients = [];

    /**
     * @param int             $port
     * @param array           $initialPeerAddresses
     * @param LoggerInterface $logger
     */
    public function __construct(int $port, array $initialPeerAddresses, LoggerInterface $logger)
    {
        $ownIpAddress = getHostByName(getHostName());
        $ownIpAddress = '127.0.0.1';

        $this->logger = $logger;
        $this->ownPeer = new Peer(new PeerAddress($ownIpAddress, $port));

        foreach ($initialPeerAddresses as $initialPeerAddress) {
            $initialPeer = new Peer(PeerAddress::fromString($initialPeerAddress));

            $this->peers[] = $initialPeer;
        }
    }

    public function run()
    {
        if (false === $this->start()) {
            $this->logger->critical('Could not start network.');
            return;
        }

        while (true) {
            // Discovering
            $this->logger->notice('Start discovering...');
            $this->discover();
            $this->logger->notice('Finished discovering.');
            $this->logger->notice('Connected peers: ' . count($this->peers));

            // Handling requests
            $this->collectNewClients();

            $this->handleClients();

            usleep(5000);
        }
    }

    /**
     * Discovers new peers and remove dead ones
     */
    private function discover()
    {
        foreach ($this->peers as $i => $peer) {
            if (false === $peer->alive()) {
                unset($this->peers[$i]);
            }
        }

        $this->recursiveDiscover($this->peers);
    }

    /**
     * Handles requests from connected peers
     */
    private function collectNewClients()
    {
        if (false === $this->sock->selectRead(1)) {
            // No client ready
            return;
        }

        $clientSocket = $this->sock->accept();

        $this->clients[] = Client::fromSocket($clientSocket);
    }

    private function handleClients()
    {
        foreach ($this->clients as $client) {
            $this->logger->debug('Start reading from client: ' . $client->getSocket()->getPeerName());

            if (null === $packetData = $client->readPacketData(self::PACKET_SEPARATOR)) {
                continue;
            }

            $this->logger->info($client->getSocket()->getPeerName() . ': ' . $packetData);

            if ('PING' === $packetData) {
                $client->getSocket()->write('PONG' . self::PACKET_SEPARATOR);
            } else {
                $client->getSocket()->write('UNKNOWN' . self::PACKET_SEPARATOR);
            }

            $this->logger->debug('End reading from client: ' . $client->getSocket()->getPeerName());
        }
    }

    /**
     * Starts listening for peers to call it
     */
    private function start(): bool
    {
        $factory = new Factory();

        try {
            // create a TCP/IP stream connection socket server on port 1337
            $this->sock = $factory->createServer('tcp://' . (string) $this->ownPeer->getAddress());
            $this->sock->setBlocking(false);

            $this->logger->info(sprintf('Listening on %s', (string) $this->ownPeer->getAddress()));

            return true;
        } catch (\Exception $e) {
            $this->logger->critical(
                sprintf('Could not start listening on "%s": %s', (string) $this->ownPeer->getAddress(), $e->getMessage())
            );
        }

        return false;
    }

    /**
     * Stops listening for peers
     */
    public function stop()
    {
        if (false === is_resource($this->sock)) {
            return;
        }

        $this->sock->close();
        $this->logger->info(sprintf('Stop listening on %s', (string) $this->ownPeer->getAddress()));
    }

    /**
     * @return array|PeerInterface[]
     */
    public function getPeers(): array
    {
        return $this->peers;
    }

    public function __destruct()
    {
        $this->stop();
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
var_dump($peer->getAddress(), $alive);
            if (true === $known && false === $alive) {
                // Known peer but it's dead -> remove it
                unset($this->peers[$i]);
                $this->logger->notice(sprintf('Remove existing peer (%s): dead', (string) $peer->getAddress()));
                continue;
            }

            if (true === $alive) {
                if (false === $known) {
                    // Alive peer and unknown -> add it
                    $this->peers[] = $peer;
                    $this->logger->notice(sprintf('New peer added (%s)', (string) $peer->getAddress()));

                    if (count($this->peers) >= self::PEERS_MAX) {
                        // Stop looking for more peers as we reached max
                        break;
                    }
                }

                // Discover all peers of that new peer
                $this->logger->debug(sprintf('Discovering peers of %s', (string) $peer->getAddress()));
                $this->recursiveDiscover($peer->getPeers());
            }
        }
    }
}
