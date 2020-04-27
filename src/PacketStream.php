<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Exception\EndOfStreamException;

/**
 * Provides methods to operate on a stream of bytes.
 */
class PacketStream
{
    /** @var string */
    private $data;
    /** @var int */
    private $position;

    /**
     * Constructs an instance of this class.
     *
     * @param string $data initial data of the stream
     */
    public function __construct(string $data = '')
    {
        $this->data = $data;
        $this->position = 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->data;
    }

    /**
     * Returns the desired number of bytes.
     *
     * @throws EndOfStreamException
     */
    public function read(int $count): string
    {
        $contentLength = strlen($this->data);
        if ($this->position > $contentLength || $count > $contentLength - $this->position) {
            throw new EndOfStreamException(
                sprintf(
                    'End of stream reached when trying to read %d bytes. content length=%d, position=%d',
                    $count,
                    $contentLength,
                    $this->position
                )
            );
        }

        $chunk = (string) substr($this->data, $this->position, $count);
        $readBytes = strlen($chunk);
        $this->position += $readBytes;

        return $chunk;
    }

    /**
     * Returns a single byte.
     *
     * @throws EndOfStreamException
     */
    public function readByte(): int
    {
        return ord($this->read(1));
    }

    /**
     * Returns a single word.
     *
     * @throws EndOfStreamException
     */
    public function readWord(): int
    {
        return ($this->readByte() << 8) + $this->readByte();
    }

    /**
     * Returns a length prefixed string.
     *
     * @throws EndOfStreamException
     */
    public function readString(): string
    {
        $length = $this->readWord();

        return $this->read($length);
    }

    /**
     * Appends the given value.
     */
    public function write(string $value): void
    {
        $this->data .= $value;
    }

    /**
     * Appends a single byte.
     */
    public function writeByte(int $value): void
    {
        $this->write(chr($value));
    }

    /**
     * Appends a single word.
     */
    public function writeWord(int $value): void
    {
        $this->write(chr(($value & 0xFFFF) >> 8));
        $this->write(chr($value & 0xFF));
    }

    /**
     * Appends a length prefixed string.
     */
    public function writeString(string $string): void
    {
        $this->writeWord(strlen($string));
        $this->write($string);
    }

    /**
     * Returns the length of the stream.
     */
    public function length(): int
    {
        return strlen($this->data);
    }

    /**
     * Returns the number of bytes until the end of the stream.
     */
    public function getRemainingBytes(): int
    {
        return $this->length() - $this->position;
    }

    /**
     * Returns the whole content of the stream.
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Changes the internal position of the stream relative to the current position.
     */
    public function seek(int $offset): void
    {
        $this->position += $offset;
    }

    /**
     * Returns the internal position of the stream.
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Sets the internal position of the stream.
     */
    public function setPosition(int $value): void
    {
        $this->position = $value;
    }

    /**
     * Removes all bytes from the beginning to the current position.
     */
    public function cut(): void
    {
        $this->data = (string) substr($this->data, $this->position);
        $this->position = 0;
    }
}
