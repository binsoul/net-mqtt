<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\EndOfStreamException;
use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;

/**
 * Represents the base class for all packets.
 */
abstract class BasePacket implements Packet
{
    /**
     * Type of the packet. See {@see Packet}.
     *
     * @var int<0, 15>
     *
     * @phpstan-var Packet::TYPE_*
     */
    protected static int $packetType;

    /**
     * Flags of the packet.
     *
     * @var int<0, 15>
     */
    protected int $packetFlags = 0;

    /**
     * Number of bytes of a variable length packet.
     */
    protected int $remainingPacketLength = 0;

    public function __toString(): string
    {
        $output = new PacketStream();
        $this->write($output);

        return $output->getData();
    }

    public function read(PacketStream $stream): void
    {
        $byte = $stream->readByte();
        $packetType = $byte >> 4;

        if ($packetType !== static::$packetType) {
            throw new MalformedPacketException(
                sprintf(
                    'Expected packet type %02x but got %02x.',
                    $packetType,
                    static::$packetType
                )
            );
        }

        $this->packetFlags = $byte & 0x0F;
        $this->readRemainingLength($stream);
    }

    public function write(PacketStream $stream): void
    {
        $stream->writeByte(((static::$packetType & 0x0F) << 4) + ($this->packetFlags & 0x0F));
        $this->writeRemainingLength($stream);
    }

    public function getPacketType(): int
    {
        return static::$packetType;
    }

    /**
     * Returns the packet flags.
     *
     * @return int<0, 15>
     */
    public function getPacketFlags(): int
    {
        return $this->packetFlags;
    }

    /**
     * Returns the remaining length.
     */
    public function getRemainingPacketLength(): int
    {
        return $this->remainingPacketLength;
    }

    /**
     * Asserts that the packet flags have a specific value.
     *
     * @throws MalformedPacketException
     */
    protected function assertPacketFlags(int $value): void
    {
        if ($this->packetFlags !== $value) {
            throw new MalformedPacketException(
                sprintf(
                    'Expected flags %02x but got %02x.',
                    $value,
                    $this->packetFlags
                )
            );
        }
    }

    /**
     * Asserts that the remaining length is greater than zero and has a specific value.
     *
     * @param int|null $value value to test or null if any value greater than zero is valid
     *
     * @throws MalformedPacketException
     */
    protected function assertRemainingPacketLength(?int $value = null): void
    {
        if ($value === null && $this->remainingPacketLength === 0) {
            throw new MalformedPacketException('Expected payload but remaining packet length is zero.');
        }

        if ($value !== null && $this->remainingPacketLength !== $value) {
            throw new MalformedPacketException(
                sprintf(
                    'Expected remaining packet length of %d bytes but got %d.',
                    $value,
                    $this->remainingPacketLength
                )
            );
        }
    }

    /**
     * Reads the remaining length from the given stream.
     *
     * @throws MalformedPacketException
     * @throws EndOfStreamException
     */
    private function readRemainingLength(PacketStream $stream): void
    {
        $this->remainingPacketLength = 0;
        $multiplier = 1;

        do {
            $encodedByte = $stream->readByte();

            $this->remainingPacketLength += ($encodedByte & 127) * $multiplier;
            $multiplier *= 128;

            if ($multiplier > 128 * 128 * 128 * 128) {
                throw new MalformedPacketException('Malformed remaining length.');
            }
        } while (($encodedByte & 128) !== 0);
    }

    /**
     * Writes the remaining length to the given stream.
     */
    private function writeRemainingLength(PacketStream $stream): void
    {
        $x = $this->remainingPacketLength;

        do {
            $encodedByte = $x % 128;
            $x = (int) ($x / 128);

            if ($x > 0) {
                $encodedByte |= 128;
            }

            $stream->writeByte($encodedByte & 0xFF);
        } while ($x > 0);
    }
}
