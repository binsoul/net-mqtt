<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;

/**
 * Represents the CONNECT packet with strict rules for client ids.
 */
class StrictConnectRequestPacket extends ConnectRequestPacket
{
    public function read(PacketStream $stream): void
    {
        parent::read($stream);

        $this->assertValidClientID($this->clientID ?? '');
    }

    /**
     * Sets the client id.
     *
     * @throws InvalidArgumentException
     */
    public function setClientID(string $value): void
    {
        try {
            $this->assertValidClientID($value);
        } catch (MalformedPacketException $malformedPacketException) {
            throw new InvalidArgumentException($malformedPacketException->getMessage(), $malformedPacketException->getCode(), $malformedPacketException);
        }

        $this->clientID = $value;
    }

    /**
     * Asserts that a client id is shorter than 24 bytes and only contains characters 0-9, a-z or A-Z.
     *
     * @throws MalformedPacketException
     */
    private function assertValidClientID(string $value): void
    {
        if (strlen($value) > 23) {
            throw new MalformedPacketException(
                sprintf(
                    'Expected client id shorter than 24 bytes but got "%s".',
                    $value
                )
            );
        }

        if ($value !== '' && ! ctype_alnum($value)) {
            throw new MalformedPacketException(
                sprintf(
                    'Expected a client id containing characters 0-9, a-z or A-Z but got "%s".',
                    $value
                )
            );
        }
    }
}
