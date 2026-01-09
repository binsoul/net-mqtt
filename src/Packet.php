<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Exception\EndOfStreamException;
use BinSoul\Net\Mqtt\Exception\MalformedPacketException;

/**
 * Represent a packet of the MQTT protocol.
 */
interface Packet
{
    public const TYPE_CONNECT = 1;

    public const TYPE_CONNACK = 2;

    public const TYPE_PUBLISH = 3;

    public const TYPE_PUBACK = 4;

    public const TYPE_PUBREC = 5;

    public const TYPE_PUBREL = 6;

    public const TYPE_PUBCOMP = 7;

    public const TYPE_SUBSCRIBE = 8;

    public const TYPE_SUBACK = 9;

    public const TYPE_UNSUBSCRIBE = 10;

    public const TYPE_UNSUBACK = 11;

    public const TYPE_PINGREQ = 12;

    public const TYPE_PINGRESP = 13;

    public const TYPE_DISCONNECT = 14;

    /**
     * Returns the serialized form of the packet.
     */
    public function __toString(): string;

    /**
     * Returns the type of the packet.
     */
    public function getPacketType(): int;

    /**
     * Reads the packet from the given stream.
     *
     * @throws MalformedPacketException
     * @throws EndOfStreamException
     */
    public function read(PacketStream $stream): void;

    /**
     * Writes the packet to the given stream.
     */
    public function write(PacketStream $stream): void;
}
