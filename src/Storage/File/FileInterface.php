<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Storage\File;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
interface FileInterface
{

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $mode
     * @param bool $use_include_path
     * @return bool
     */
    public function open(string $mode, bool $use_include_path = false): bool;

    /**
     * @return bool
     */
    public function close(): bool;

    /**
     * @param int $bytes
     * @return string
     */
    public function read(int $bytes): string;

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int;

    /**
     * @return int
     */
    public function tell(): int;

    /**
     * @param string $string
     * @param int|null $length
     * @return int
     */
    public function write(string $string, int $length = null): int;

    /**
     * Appends data to the end of the file
     * @param string $data The data to append
     * @return int The amount of written bytes
     */
    public function append(string $data): int;

    /**
     * @param string $data
     * @param int $position
     * @return int
     */
    public function overwrite(string $data, int $position): int;
}
