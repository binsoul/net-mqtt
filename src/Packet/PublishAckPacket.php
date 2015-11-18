<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the PUBACK packet.
 */
class PublishAckPacket extends PublishBasePacket
{
    protected $packetType = Packet::TYPE_PUBACK;
}
