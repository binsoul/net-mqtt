<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the base class for all packets.
 */
class BasePacket implements Packet
{
    /**
     * Type of the packet. See {@see Packet}.
     *
     * @var int
     */
    protected $packetType = 0;
    /**
     * Flags of the packet.
     *
     * @var int
     */
    protected $packetFlags = 0;

    /**
     * Number of bytes of a variable length packet.
     *
     * @var int
     */
    protected $remainingPacketLength = 0;

    public function __toString()
    {
        $output = new PacketStream();
        $this->write($output);

        return $output->getData();
    }

    public function read(PacketStream $stream)
    {
        $byte = $stream->readByte();

        $this->packetType = $byte >> 4;
        $this->packetFlags = $byte & 0x0F;
        $this->readRemainingLength($stream);
    }

    public function write(PacketStream $stream)
    {
        $stream->writeByte((($this->packetType & 0x0F) << 4) + ($this->packetFlags & 0x0F));
        $this->writeRemainingLength($stream);
    }

    /**
     * Reads the remaining length from the given stream.
     *
     * @param PacketStream $stream
     *
     * @throws MalformedPacketException
     */
    private function readRemainingLength(PacketStream $stream)
    {
        $this->remainingPacketLength = 0;
        $multiplier = 1;

        do {
            $encodedByte = $stream->readByte();

            $this->remainingPacketLength += ($encodedByte & 127) * $multiplier;
            $multiplier *= 128;

            if ($multiplier > 128 * 128 * 128) {
                throw new MalformedPacketException('Malformed remaining length.');
            }
        } while (($encodedByte & 128) != 0);
    }

    /**
     * Writes the remaining length to the given stream.
     *
     * @param PacketStream $stream
     */
    private function writeRemainingLength(PacketStream $stream)
    {
        $x = $this->remainingPacketLength;
        do {
            $encodedByte = $x % 128;
            $x = (int) ($x / 128);
            if ($x > 0) {
                $encodedByte = $encodedByte | 128;
            }

            $stream->writeByte($encodedByte);
        } while ($x > 0);
    }

    public function getPacketType()
    {
        return $this->packetType;
    }

    /**
     * Sets the packet type.
     *
     * @param int $value
     */
    public function setPacketType($value)
    {
        $this->packetType = $value;
    }

    /**
     * Returns the packet flags.
     *
     * @return int
     */
    public function getPacketFlags()
    {
        return $this->packetFlags;
    }

    /**
     * Sets the packet flags.
     *
     * @param int $value
     */
    public function setPacketFlags($value)
    {
        $this->packetFlags = $value;
    }

    /**
     * Returns the remaining length.
     *
     * @return int
     */
    public function getRemainingPacketLength()
    {
        return $this->remainingPacketLength;
    }

    /**
     * Sets the remaining length.
     *
     * @param int $value
     */
    public function setRemainingPacketLength($value)
    {
        $this->remainingPacketLength = $value;
    }

    /**
     * Returns a readable output of the given byte string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function debug($string)
    {
        $bytes = '';
        $ascii = '';
        for ($n = 0; $n < strlen($string); ++$n) {
            $char = substr($string, $n, 1);
            $byte = ord($char);
            $bytes .= str_pad(bin2hex($char), 3, ' ', STR_PAD_LEFT);
            if ($byte >= 32 && $byte <= 126) {
                $ascii .= str_pad($char, 3, ' ', STR_PAD_LEFT);
            } else {
                $ascii .= str_pad(bin2hex($char), 3, ' ', STR_PAD_LEFT);
            }
        }

        return $bytes."\n".$ascii."\n";
    }

    /**
     * Asserts that the packet flags have a specific value.
     *
     * @param int $value
     *
     * @throws MalformedPacketException
     */
    protected function assertPacketFlags($value)
    {
        if ($this->packetFlags != $value) {
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
    protected function assertRemainingPacketLength($value = null)
    {
        if ($value === null && $this->remainingPacketLength == 0) {
            throw new MalformedPacketException('Expected payload but remaining packet length is zero.');
        }

        if ($value !== null && $this->remainingPacketLength != $value) {
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
     * Asserts that the given string is a well-formed MQTT string.
     *
     * @param $value
     *
     * @throws MalformedPacketException
     */
    protected function assertValidStringLength($value)
    {
        if (strlen($value) > 0xFFFF) {
            throw new MalformedPacketException(
                sprintf(
                    'The string "%s" is longer than 65535 byte.',
                    substr($value, 0, 50)
                )
            );
        }
    }

    /**
     * Asserts that the given string is a well-formed MQTT string.
     *
     * @param $value
     *
     * @throws MalformedPacketException
     */
    protected function assertValidString($value)
    {
        $this->assertValidStringLength($value);

        if (!mb_check_encoding($value, 'UTF-8')) {
            throw new MalformedPacketException(
                sprintf(
                    'The string "%s" is not well-formed UTF-8.',
                    substr($value, 0, 50)
                )
            );
        }

        if (preg_match('/[\xD8-\xDF][\x00-\xFF]|\x00\x00/xs', $value)) {
            throw new MalformedPacketException(
                sprintf(
                    'The string "%s" contains invalid characters.',
                    substr($value, 0, 50)
                )
            );
        }
    }

    /**
     * Asserts that the given quality of service level is valid.
     *
     * @param $level
     */
    protected function assertValidQosLevel($level)
    {
        if ($level < 0 || $level > 2) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected a quality of service level between 0 and 2 but got %d.',
                    $level
                )
            );
        }
    }
}
