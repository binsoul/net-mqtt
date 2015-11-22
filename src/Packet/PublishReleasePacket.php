<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the PUBREL packet.
 */
class PublishReleasePacket extends PublishBasePacket
{
    protected $packetType = Packet::TYPE_PUBREL;
    protected $packetFlags = 2;
    protected $expectedPacketFlags = 2;
}
