<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the PUBREL packet.
 */
class PublishReleasePacket extends PublishBasePacket
{
    protected $packetType = Packet::TYPE_PUBREL;
    protected $packetFlags = 2;

    public function read(PacketStream $stream)
    {
        parent::read($stream);
        $this->assertPacketFlags(2);
        $this->assertRemainingPacketLength(2);

        $this->identifier = $stream->readWord();
    }
}
