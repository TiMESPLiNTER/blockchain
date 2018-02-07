<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;
use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Peer implements PeerInterface
{

    /**
     * @var PeerAddress
     */
    private $address;

    /**
     * @var Socket
     */
    private $socket;

    /**
     * @param PeerAddress $address
     */
    public function __construct(PeerAddress $address)
    {
        $this->address = $address;
    }

    /**
     * Returns the (IP) address through which this peer is reachable
     * @return PeerAddress
     */
    public function getAddress(): PeerAddress
    {
        return $this->address;
    }

    /**
     * Returns a list of peers this peer is connected to
     * @return array|PeerInterface[]
     */
    public function getPeers(): array
    {
        return [];
    }

    /**
     * Checks if this peer is still alive
     * @return bool
     */
    public function alive(): bool
    {
        echo 'alive called' , PHP_EOL;
        try {
            $this->write('PING');

            var_dump($this->getAddress(), $this->read(1024));

            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
            echo $e->getTraceAsString();
            exit;
            return false;
        }
    }

    private function write(string $command): int
    {
        $retries = 0;

        while (true) {
            try {
                $socket = $this->getSocket();

                return $socket->write($command . "\n");
            } catch (Exception $e) {
                if ($retries < 10 && ($e->getCode() === SOCKET_EAGAIN || $e->getCode() === SOCKET_EWOULDBLOCK)) {
                    ++$retries;
                    usleep(1000);
                    continue;
                }

                throw $e;
            }
        }
    }

    private function read(int $length): string
    {
        $retries = 0;

        while (true) {
            try {
                $socket = $this->getSocket();

                return $socket->read($length);
            } catch (Exception $e) {
                if ($retries < 10 && ($e->getCode() === SOCKET_EAGAIN || $e->getCode() === SOCKET_EWOULDBLOCK)) {
                    ++$retries;
                    usleep(1000);
                    continue;
                }

                throw $e;
            }
        }
    }

    /**
     * @return Socket
     */
    public function getSocket(): Socket
    {
        if (null === $this->socket) {
            $factory = new Factory();

            try {
                $this->socket = $factory->createClient('tcp://' . (string) $this->address);
            } catch (\Exception $e) {
                echo 'cant connect client socket: ' , $e->getMessage() , ' -> ' , (string) $this->address;
                exit;
            }
        }

        return $this->socket;
    }

    public function __destruct()
    {
        if (false === is_resource($this->socket)) {
            return;
        }

        $this->socket->close();
    }
}
