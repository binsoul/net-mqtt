<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;

/**
 * Represents the CONNACK packet.
 */
class ConnectResponsePacket extends BasePacket
{
    protected static int $packetType = Packet::TYPE_CONNACK;

    protected int $remainingPacketLength = 2;

    /**
     * @var string[][]
     */
    private static array $returnCodes = [
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

    private int $flags = 0;

    private int $returnCode = 0;

    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength(2);

        $this->flags = $stream->readByte();
        $this->returnCode = $stream->readByte();
    }

    public function write(PacketStream $stream): void
    {
        $this->remainingPacketLength = 2;
        parent::write($stream);

        $stream->writeByte($this->flags);
        $stream->writeByte($this->returnCode);
    }

    /**
     * Returns the return code.
     */
    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    /**
     * Sets the return code.
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
        if ($value) {
            $this->flags |= 1;
        } else {
            $this->flags &= ~1;
        }
    }

    /**
     * Returns a string representation of the returned error code.
     */
    public function getErrorName(): string
    {
        if (isset(self::$returnCodes[$this->returnCode])) {
            return self::$returnCodes[$this->returnCode][0];
        }

        return 'Error ' . $this->returnCode;
    }
}
