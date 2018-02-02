<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Strategy\ProofOfWork;

use Timesplinter\Blockchain\BlockInterface;
use Timesplinter\Blockchain\StrategyInterface;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
final class ProofOfWorkStrategy implements StrategyInterface
{

    /**
     * @var int
     */
    private $difficulty;

    /**
     * @param int $difficulty
     */
    public function __construct(int $difficulty)
    {
        $this->difficulty = $difficulty;
    }

    /**
     * @param BlockInterface|ProofOfWorkBlockInterface $block
     * @return bool
     */
    public function mine(BlockInterface $block): bool
    {
        $prefix = str_repeat('0', $this->difficulty);

        while(substr($block->getHash(), 0, $this->difficulty) !== $prefix) {
            $block->setNonce($block->getNonce() + 1);
        }

        return true;
    }

    /**
     * @param BlockInterface $block
     * @return bool
     */
    public function supports(BlockInterface $block): bool
    {
        return $block instanceof ProofOfWorkBlockInterface;
    }
}
