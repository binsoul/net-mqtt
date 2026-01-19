<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Exception\EndOfStreamException;
use InvalidArgumentException;

/**
 * Provides methods to operate on a stream of bytes.
 */
class PacketStream
{
    /**
     * @var int<0, max>
     */
    private int $position = 0;

    /**
     * Constructs an instance of this class.
     *
     * @param string $data initial data of the stream
     */
    public function __construct(
        private string $data = ''
    ) {
    }

    public function __toString(): string
    {
        return $this->data;
    }

    /**
     * Returns the desired number of bytes.
     *
     * @paramt int<0, max> $count
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

        $chunk = substr($this->data, $this->position, $count);
        $readBytes = strlen($chunk);
        $this->position += $readBytes;

        return $chunk;
    }

    /**
     * Returns a single byte.
     *
     * @return int<0, 255>
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
     * @return int<0, 65535>
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
     *
     * @param int<0, 255> $value
     */
    public function writeByte(int $value): void
    {
        $this->write(chr($value));
    }

    /**
     * Appends a single word.
     *
     * @param int<0, 65535> $value
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
        if (strlen($string) > 0xFFFF) {
            throw new InvalidArgumentException('The string length must be less than or equal to 65535 bytes');
        }

        $this->writeWord(strlen($string));
        $this->write($string);
    }

    /**
     * Returns the length of the stream.
     *
     * @return int<0, max>
     */
    public function length(): int
    {
        return strlen($this->data);
    }

    /**
     * Returns the number of bytes until the end of the stream.
     *
     * @return int<0, max>
     */
    public function getRemainingBytes(): int
    {
        $result = $this->length() - $this->position;

        return max($result, 0);
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
        $newPosition = $this->position + $offset;
        $this->position = max($newPosition, 0);
    }

    /**
     * Returns the internal position of the stream.
     *
     * @return int<0, max>
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Sets the internal position of the stream.
     *
     * @param int<0, max> $value
     */
    public function setPosition(int $value): void
    {
        $this->position = max($value, 0);
    }

    /**
     * Removes all bytes from the beginning to the current position.
     */
    public function cut(): void
    {
        $this->data = substr($this->data, $this->position);
        $this->position = 0;
    }
}
