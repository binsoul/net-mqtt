<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the PUBCOMP packet.
 */
class PublishCompletePacket extends IdentifierOnlyPacket
{
    protected $packetType = Packet::TYPE_PUBCOMP;
}
