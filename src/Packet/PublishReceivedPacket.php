<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the PUBREC packet.
 */
class PublishReceivedPacket extends IdentifierOnlyPacket
{
    protected $packetType = Packet::TYPE_PUBREC;
}
