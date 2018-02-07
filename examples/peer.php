<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Examples;

use Timesplinter\Blockchain\Peer\Network;

require __DIR__ .'/../vendor/autoload.php';

$network = new Network();
$network->bind();

while (true) {
    echo 'Start discovering...' , PHP_EOL;

    // Clean up dead and check for new peers
    $network->discover();

    echo 'Finished discovering.' , PHP_EOL;
    echo 'Peers: ' , count($network->getPeers()) , PHP_EOL;

    foreach ($network->getPeers() as $peer) {
        echo $peer->getAddress() , PHP_EOL;
    }

    echo '---' , PHP_EOL;
}