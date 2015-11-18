<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the UNSUBACK packet.
 */
class UnsubscribeResponsePacket extends BasePacket
{
    use IdentifiablePacket;

    protected $packetType = Packet::TYPE_UNSUBACK;

    public function read(PacketStream $stream)
    {
        parent::read($stream);

        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength(2);

        $this->identifier = $stream->readWord();
    }

    public function write(PacketStream $stream)
    {
        $this->remainingPacketLength = 2;

        parent::write($stream);
        $stream->writeWord($this->generateIdentifier());
    }
}
