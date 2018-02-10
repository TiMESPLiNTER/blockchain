<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
class Response implements \JsonSerializable
{

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @param string $requestId The request id this response belongs to
     * @param mixed $data The response data
     */
    public function __construct(string $requestId, $data)
    {
        $this->requestId = $requestId;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
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
            'type' => 'response',
            'data' => ['id' => $this->requestId, 'data' => $this->data]
        ];
    }
}
