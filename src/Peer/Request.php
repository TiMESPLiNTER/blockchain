<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

use Ramsey\Uuid\Uuid;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Request implements \JsonSerializable
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $data;

    /**
     * @var bool
     */
    private $sent = false;

    /**
     * Request constructor.
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->id = (string) Uuid::uuid4();
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * @param bool $sent
     */
    public function setSent(bool $sent)
    {
        $this->sent = $sent;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'type' => 'request',
            'data' => ['id' => $this->id, 'data' => $this->data]
        ];
    }
}
