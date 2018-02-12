<?php

declare(strict_types=1);


namespace Timesplinter\Blockchain\Peer\Command;


use Timesplinter\Blockchain\Peer\Peer;
use Timesplinter\Blockchain\Peer\Request;

interface CommandInterface
{

    /**
     * @param array $requestData The original request data
     * @return array The response data as an array
     */
    public function handleRequest(array $requestData): array;

    /**
     * @param Peer    $peer
     * @param Request $request
     * @param array   $responseData
     */
    public function handleResponse(Peer $peer, Request $request, array $responseData): void;
}
