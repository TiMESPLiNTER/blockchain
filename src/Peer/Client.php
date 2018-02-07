<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

use Socket\Raw\Exception;
use Socket\Raw\Socket;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Client
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

    /**
     * @param string $separator
     * @return null|string The packet data if there is one else null
     */
    public function readPacketData(string $separator): ?string
    {
        try {
            $splittedBuffer = explode($separator, $this->buffer .= $this->socket->read(4096));

            // Clear out empty blocks
            array_filter($splittedBuffer);

            $this->failures = 0;

            if (count($splittedBuffer) <= 1) {
                return null;
            }

            $packet = array_shift($splittedBuffer);
            $this->buffer = implode($separator, $splittedBuffer);

            return $packet;
        } catch (Exception $e) {
            ++$this->failures;
        }

        return null;
    }

    /**
     * @return int
     */
    public function getFailures(): int
    {
        return $this->failures;
    }

    /**
     * @return Socket
     */
    public function getSocket(): Socket
    {
        return $this->socket;
    }
}