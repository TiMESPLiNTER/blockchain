<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

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
     * BufferedSocket constructor.
     * @param Socket $socket
     * @param Node $node
     */
    public function __construct(Socket $socket, Node $node)
    {
        $this->node = $node;
        $this->socket = $socket;
        $this->socket->setBlocking(false);
    }

    private function getNextPacket(string $separator): ?string
    {
        // @todo if buffer still contains *whole* packets don't read again but deliver next packet here
        // we have to do this because else we run into an endless loop cause the socket will
        // never give us something to read again or the other end waits forever (maybe...)
        if (false !== ($pos = strpos($this->buffer, $separator))) {
            $data = substr($this->buffer, 0, $pos);

            $this->buffer = substr($this->buffer, $pos+1);

            return $data;
        }

        if (false === $this->socket->selectRead()) {
            //echo 'Blocked for read. buffer: ' , $this->buffer , PHP_EOL;
            return null;
        }

        $this->buffer .= $this->socket->read(1024);

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
                    echo 'Request with id ' . $request->getId() . ' sent...' , PHP_EOL;
                } elseif ($request instanceof Response && isset($this->nodeResponseStack[$request->getRequestId()])) {
                    $this->socket->write(json_encode($request) . self::PACKET_SEPARATOR);
                    // Response has been sent to the client, remove it from pending responses
                    echo 'Response for request with id ' , $request->getRequestId() , ' sent...' , PHP_EOL;
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
            //echo 'No complete packet. buffer: ' , $this->buffer , PHP_EOL;
            return;
        }

        // WE CAN GET BOTH - REQUESTS AND RESPONSES - HERE AS IT IS A TWO-WAY CONNECTION!

        if (null === $packetData = json_decode($packetData, true)) {
            throw new \RuntimeException('Invalid response for request');
        }

        if ($packetData['type'] === 'response') {
            $this->handleResponse($packetData['data']);
        } elseif ($packetData['type'] === 'request') {
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