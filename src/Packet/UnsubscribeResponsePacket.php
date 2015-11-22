<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the UNSUBACK packet.
 */
class UnsubscribeResponsePacket extends IdentifierOnlyPacket
{
    protected $packetType = Packet::TYPE_UNSUBACK;
}
