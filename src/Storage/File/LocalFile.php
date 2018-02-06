<?php

declare(strict_types=1);

namespace Timesplinter\Blockchain\Storage\File;

/**
 * @author Pascal Muenst <pascal@timesplinter.ch>
 */
final class LocalFile implements FileInterface
{

    /**
     * @var resource
     */
    private $fp;

    /**
     * @var string
     */
    private $filePath;

    /**
     * LocalFile constructor.
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $path = dirname($filePath);

        if (false === $normalizedPath = realpath($path)) {
            throw new \RuntimeException(
                sprintf('Path does not exist: %s', $path)
            );
        }

        $this->filePath = $normalizedPath . DIRECTORY_SEPARATOR . basename($filePath);
    }

    /**
     * @param string $mode
     * @param bool $use_include_path
     * @return bool
     */
    public function open(string $mode, bool $use_include_path = false): bool
    {
        return false !== $this->fp = fopen($this->filePath, $mode, $use_include_path);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return fclose($this->fp);
    }

    /**
     * @param int $bytes
     * @return string
     */
    public function read(int $bytes): string
    {
        return fread($this->fp, $bytes);
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return fseek($this->fp, $offset, $whence);
    }

    /**
     * @return int
     */
    public function tell(): int
    {
        return ftell($this->fp);
    }

    /**
     * @param string $string
     * @param int|null $length
     * @return int
     */
    public function write(string $string, int $length = null): int
    {
        if ($length !== null) {
            return fwrite($this->fp, $string, $length);
        } else {
            return fwrite($this->fp, $string);
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->filePath;
    }

    /**
     * Appends data to the end of the file
     * @param string $data The data to append
     * @return int The amount of written bytes
     */
    public function append(string $data): int
    {
        $oldPos = $this->tell();

        $this->seek(0, SEEK_END);
        $this->write($data);
        $this->seek($oldPos);

        return strlen($data);
    }

    /**
     * @param string $data
     * @param int $position
     * @return int
     */
    public function overwrite(string $data, int $position): int
    {
        $oldPos = $this->tell();

        $this->seek($position);
        $this->write($data);
        $this->seek($oldPos);

        return strlen($data);
    }
}
