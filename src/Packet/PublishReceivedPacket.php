<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the PUBREC packet.
 */
class PublishReceivedPacket extends PublishBasePacket
{
    protected $packetType = Packet::TYPE_PUBREC;
}
