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
     * @var Socket
     */
    private $socket;

    /**
     * @var int
     */
    private $failures = 0;

    /**
     * @var string
     */
    private $buffer;

    private function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    public static function fromSocket(Socket $socket): self
    {
        return new self($socket);
    }

    public static function fromAddress(string $address): self
    {
        $socket = (new Factory())->createClient($address);

        return new self($socket);
    }

    /**
     * @return null|string The packet data if there is one else null
     */
    public function readPacketData(): ?string
    {
        try {
            $splittedBuffer = explode(Network::PACKET_SEPARATOR, $this->buffer .= $this->socket->read(4096));

            // Clear out empty blocks
            array_filter($splittedBuffer);

            $this->failures = 0;

            if (count($splittedBuffer) <= 1) {
                return null;
            }

            $packet = array_shift($splittedBuffer);
            $this->buffer = implode(Network::PACKET_SEPARATOR, $splittedBuffer);

            return $packet;
        } catch (Exception $e) {
            ++$this->failures;
        }

        return null;
    }

    /**
     * @param string $packetData
     * @return int
     */
    public function writePacketData(string $packetData): int
    {
        try {
            return $this->socket->write($packetData . Network::PACKET_SEPARATOR);
        } catch (Exception $e) {
            ++$this->failures;
        }
    }

    /**
     * Checks if this peer is still alive
     * @return bool
     */
    public function alive(): bool
    {
        try {
            $this->writePacketData('PING');

            var_dump($this->getAddress(), $this->readPacketData());

            return true;
        } catch (\Exception $e) {
            ++$this->failures;
            echo $e->getMessage();
            echo $e->getTraceAsString();
            return false;
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
     * Returns the (IP) address through which this peer is reachable
     * @return string
     */
    public function getAddress(): string
    {
        try {
            return $this->socket->getPeerName();
        } catch (Exception $e) {
            ++$this->failures;
        }
    }

    /**
     * Returns a list of peers this peer is connected to
     * @return array|PeerInterface[]
     */
    public function getPeers(): array
    {
        return [];
    }
}