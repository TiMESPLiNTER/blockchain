<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

class PeerAddress
{

    /**
     * @var string
     */
    private $address;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $address
     * @param int    $port
     */
    public function __construct(string $address, int $port)
    {
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * Parses an address string (e.g. 192.168.0.5:4444)
     * @param string $address
     * @return PeerAddress
     */
    public static function fromString(string $address): self
    {
        if (0 === preg_match('/^(?P<address>[0-9.:a-f]+?)(?:\:(?P<port>\d{1,5}))?$/', $address, $matches)) {
            throw new \InvalidArgumentException(sprintf('Address "%s" is not in a valid format', $address));
        }

        return new self(
            $matches['address'],
            isset($matches['port']) ? (int) $matches['port'] : Network::NETWORK_DEFAULT_PORT
        );
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    public function __toString()
    {
        return $this->address . ':' . $this->port;
    }
}
