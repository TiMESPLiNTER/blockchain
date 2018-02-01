<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Transaction;

use Timesplinter\Blockchain\BlockchainInterface;

class TransactionPool
{

    /**
     * @var BlockchainInterface
     */
    private $blockchain;

    /**
     * @var array
     */
    private $pool = [];

    /**
     * @param BlockchainInterface $blockchain
     */
    public function __construct(BlockchainInterface $blockchain)
    {
        $this->blockchain = $blockchain;
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public function addTransaction(Transaction $transaction): bool
    {
        if (false === $this->isTransactionValid($transaction)) {
            return false;
        }

        $this->pool[] = $transaction;

        return true;
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public function isTransactionValid(Transaction $transaction): bool
    {
        $balanceOfSender = $this->getBalanceForAddress($transaction->getFrom());

        return $balanceOfSender >= $transaction->getAmount();
    }

    public function getBalanceForAddress(string $address): float
    {
        $balance = 0.0;

        foreach ($this->blockchain->getChain() as $block) {
            if (false === is_array($block->getData())) {
                continue;
            }

            foreach ($block->getData() as $transaction) {
                if (false === $transaction instanceof Transaction) {
                    continue;
                }

                /** @var Transaction $transaction */

                if ($address === $transaction->getFrom()) {
                    $balance -= $transaction->getAmount();
                } elseif ($address === $transaction->getTo()) {
                    $balance += $transaction->getAmount();
                }
            }
        }

        return $balance;
    }
}
