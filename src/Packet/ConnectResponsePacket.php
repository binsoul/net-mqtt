<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use Override;

/**
 * Represents the CONNACK packet.
 */
class ConnectResponsePacket extends BasePacket
{
    /**
     * @var array<int, array<int, string>>
     */
    private const array RETURN_CODES = [
        0 => [
            'Connection accepted',
            '',
        ],
        1 => [
            'Unacceptable protocol version',
            'The Server does not support the level of the MQTT protocol requested by the client.',
        ],
        2 => [
            'Identifier rejected',
            'The client identifier is correct UTF-8 but not allowed by the server.',
        ],
        3 => [
            'Server unavailable',
            'The network connection has been made but the MQTT service is unavailable',
        ],
        4 => [
            'Bad user name or password',
            'The data in the user name or password is malformed.',
        ],
        5 => [
            'Not authorized',
            'The client is not authorized to connect.',
        ],
    ];

    protected static int $packetType = Packet::TYPE_CONNACK;

    protected int $remainingPacketLength = 2;

    /**
     * @var int<0, 255>
     */
    private int $flags = 0;

    /**
     * @var int<0, 255>
     */
    private int $returnCode = 0;

    #[Override]
    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength(2);

        $this->flags = $stream->readByte();
        $this->returnCode = $stream->readByte();
    }

    #[Override]
    public function write(PacketStream $stream): void
    {
        $this->remainingPacketLength = 2;
        parent::write($stream);

        $stream->writeByte($this->flags);
        $stream->writeByte($this->returnCode);
    }

    /**
     * Returns the return code.
     *
     * @return int<0, 255>
     */
    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    /**
     * Sets the return code.
     *
     * @param int<0, 255> $value
     */
    public function setReturnCode(int $value): void
    {
        $this->returnCode = $value;
    }

    /**
     * Indicates if the connection was successful.
     */
    public function isSuccess(): bool
    {
        return $this->returnCode === 0;
    }

    /**
     * Indicates if the connection failed.
     */
    public function isError(): bool
    {
        return $this->returnCode > 0;
    }

    /**
     * Indicates if the server has a stored session for this client.
     */
    public function isSessionPresent(): bool
    {
        return (bool) ($this->flags & 0x1);
    }

    /**
     * Changes the session present flag.
     */
    public function setSessionPresent(bool $value): void
    {
        $this->flags = $value ? ($this->flags | 1) & 0xFF : ($this->flags & ~1) & 0xFF;
    }

    /**
     * Returns a string representation of the returned error code.
     */
    public function getErrorName(): string
    {
        if (array_key_exists($this->returnCode, self::RETURN_CODES)) {
            return self::RETURN_CODES[$this->returnCode][0];
        }

        return sprintf('Unknown %02x', $this->returnCode);
    }
}
