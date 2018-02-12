<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Peer\Command;

use Timesplinter\Blockchain\Peer\Node;

abstract class NodeCommand implements CommandInterface
{

    /**
     * @var Node
     */
    protected $node;

    /**
     * @param Node $node
     */
    public function __construct($node)
    {
        $this->node = $node;
    }
}
