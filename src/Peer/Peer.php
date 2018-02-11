<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

use Psr\Log\LoggerInterface;
use Socket\Raw\Exception;
use Socket\Raw\Socket;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Peer
{

    const PACKET_SEPARATOR = "\0";

    /**
     * @var Socket
     */
    private $socket;

    /**
     * @var PeerAddress|null
     */
    private $connectionDetails = null;

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * Pending requests from this node to the client
     * @var array|Request[]
     */
    private $nodeRequestStack = [];

    /**
     * Pending responses from this node to the client
     * @var array|Response[]
     */
    private $nodeResponseStack = [];

    /**
     * @var int
     */
    private $failures = 0;

    /**
     * @var Node
     */
    private $node;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BufferedSocket constructor.
     * @param Socket $socket
     * @param Node $node
     * @param LoggerInterface $logger
     */
    public function __construct(Socket $socket, Node $node, LoggerInterface $logger)
    {
        $this->node = $node;
        $this->logger = $logger;
        $this->socket = $socket;
        $this->socket->setBlocking(false);
    }

    private function getNextPacket(string $separator): ?string
    {
        // If buffer still contains *whole* packets don't read again but deliver next packet here
        // we have to do this because else we run into an endless loop cause the socket will
        // never give us something to read again or the other end waits forever (maybe...)
        if (null !== $data = $this->readPacketFromBuffer($separator)) {
            return $data;
        }

        if (false === $this->socket->selectRead()) {
            //echo 'Blocked for read. buffer: ' , $this->buffer , PHP_EOL;
            return null;
        }

        $this->buffer .= $this->socket->read(1024);

        return $this->readPacketFromBuffer($separator);
    }

    private function readPacketFromBuffer($separator): ?string
    {
        if (false !== ($pos = strpos($this->buffer, $separator))) {
            $data = substr($this->buffer, 0, $pos);

            $this->buffer = substr($this->buffer, $pos+1);

            return $data;
        }

        return null;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->nodeRequestStack + $this->nodeResponseStack as $request) {
            if (false === $request instanceof \JsonSerializable) {
                throw new \RuntimeException('Requests and responses have to implement the JsonSerializable interface');
            }

            try {
                if (false === $this->socket->selectWrite()) {
                    continue;
                }

                if ($request instanceof Request && false === $request->isSent()) {
                    $this->socket->write(json_encode($request) . self::PACKET_SEPARATOR);
                    // Request has been sent to the client, mark it as sent
                    $request->setSent(true);
                    $this->logger->debug('Request with id ' . $request->getId() . ' sent...');
                } elseif ($request instanceof Response && isset($this->nodeResponseStack[$request->getRequestId()])) {
                    $this->socket->write(json_encode($request) . self::PACKET_SEPARATOR);
                    // Response has been sent to the client, remove it from pending responses
                    $this->logger->debug('Response for request with id ' . $request->getRequestId() . ' sent...');
                    unset($this->nodeResponseStack[$request->getRequestId()]);
                }
            } catch (Exception $e) {
                if($e->getCode() === SOCKET_EAGAIN || $e->getCode() === SOCKET_EWOULDBLOCK) {
                    continue;
                }

                ++$this->failures;
                throw $e;
            }
        }

        if (null === $packetData = $this->getNextPacket(self::PACKET_SEPARATOR)) {
            return;
        }

        if (null === $packetData = json_decode($packetData, true)) {
            throw new \RuntimeException('Invalid response for request');
        }

        if ($packetData['type'] === 'response') {
            // Handle outgoing responses from client
            $this->handleResponse($packetData['data']);
        } elseif ($packetData['type'] === 'request') {
            // Handle incoming requests from client
            $this->handleRequest($packetData['data']);
        }
    }

    /**
     * Handles responses from the client
     * @param array $responseData
     */
    private function handleResponse(array $responseData)
    {
        $requestId = $responseData['id'];

        if (false === isset($this->nodeRequestStack[$requestId])) {
            throw new \RuntimeException(sprintf(
                'Request with id %s is not pending (pending: %s)',
                $requestId,
                implode(', ', array_keys($this->nodeRequestStack))
            ));
        }

        $this->node->handleResponse($this, $this->nodeRequestStack[$requestId], $responseData);

        unset($this->nodeRequestStack[$requestId]);
    }

    /**
     * Handles requests from the client. The request will be handled imediatly and a response will be queued to be
     * send to the client as soon as it's not blocking
     * @param array $requestData
     */
    private function handleRequest(array $requestData)
    {
        $requestId = $requestData['id'];

        $this->nodeResponseStack[$requestId] = $this->node->handleRequest($requestData);
    }

    public function request(Request $request)
    {
        $this->nodeRequestStack[$request->getId()] = $request;
    }

    /**
     * @return Socket
     */
    public function getSocket(): Socket
    {
        return $this->socket;
    }

    /**
     * @return void
     */
    public function disconnect(): void
    {
        try {
            $this->socket->shutdown()->close();
        } catch (Exception $e) {
            if ($e->getCode() !== SOCKET_ENOTCONN) {
                throw $e;
            }
        }
    }

    /**
     * @return int
     */
    public function getFailures(): int
    {
        return $this->failures;
    }

    /**
     * @return PeerAddress|null
     */
    public function getConnectionDetails(): ?PeerAddress
    {
        return $this->connectionDetails;
    }

    /**
     * @param PeerAddress $connectionDetails
     */
    public function setConnectionDetails(PeerAddress $connectionDetails): void
    {
        $this->connectionDetails = $connectionDetails;
    }
}