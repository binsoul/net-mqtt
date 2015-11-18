<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the RESERVED packet.
 */
class ReservedPacket extends BasePacket
{
    protected $packetType = Packet::TYPE_RESERVED;
}
