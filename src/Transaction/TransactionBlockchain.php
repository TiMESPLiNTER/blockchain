<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Transaction;

use Timesplinter\Blockchain\BlockchainInterface;
use Timesplinter\Blockchain\BlockchainIterator;
use Timesplinter\Blockchain\BlockInterface;

final class TransactionBlockchain implements BlockchainInterface
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
     * @throws TransactionSignatureException
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
     * @throws TransactionSignatureException
     */
    public function isTransactionValid(Transaction $transaction): bool
    {
        if (false === $transaction->isSignatureValid()) {
            return false;
        }

        $balanceOfSender = $this->getBalanceForAddress($transaction->getFrom());

        return $balanceOfSender >= $transaction->getAmount();
    }

    /**
     * @param string $address
     * @return float
     */
    public function getBalanceForAddress(string $address): float
    {
        $balance = 0.0;

        foreach ($this->blockchain as $block) {
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

    /**
     * Adds new block to the chain
     * @param BlockInterface $block
     * @return void
     */
    public function addBlock(BlockInterface $block): void
    {
        if (false === $this->isBlockDataValid($block)) {
            throw new \RuntimeException(
                sprintf('Data of block "%s" is not valid', $block->getHash())
            );
        }

        $this->blockchain->addBlock($block);
    }

    /**
     * Returns latest block of the chain
     * @return BlockInterface
     */
    public function getLatestBlock(): BlockInterface
    {
        return $this->blockchain->getLatestBlock();
    }

    /**
     * Checks if the blockchain is in a valid state
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->blockchain->isValid();
    }

    /**
     * Checks if the block only contains valid transaction objects
     * @param BlockInterface $block
     * @return bool
     */
    private function isBlockDataValid(BlockInterface $block): bool
    {
        if (false === is_array($block->getData())) {
            return false;
        }

        foreach ($block->getData() as $transaction) {
            if (false === $transaction instanceof Transaction) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return BlockchainIterator
     */
    public function getIterator(): BlockchainIterator
    {
        return $this->blockchain->getIterator();
    }
}
