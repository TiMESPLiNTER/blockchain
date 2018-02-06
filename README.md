# Blockchain

[![Build Status](https://scrutinizer-ci.com/g/TiMESPLiNTER/blockchain/badges/build.png?b=master)](https://scrutinizer-ci.com/g/TiMESPLiNTER/blockchain/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/TiMESPLiNTER/blockchain/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/TiMESPLiNTER/blockchain/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TiMESPLiNTER/blockchain/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TiMESPLiNTER/blockchain/?branch=master)

## Strategies

### No proof

This strategy enables a client to push new blocks to the blockchain effortlessly.

### Proof of Work (PoW)

This strategy requires solving a mathematical problem before a block can be pushed to the blockchain.

## Storage

### InMemory

Blocks are stored in a PHP array and are gone after script execution ends.

### File

Blocks are stored permanently in a file and are available even after script execution ends.
