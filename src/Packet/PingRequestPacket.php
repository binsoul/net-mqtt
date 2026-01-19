<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use Override;

/**
 * Represents the PINGREQ packet.
 */
class PingRequestPacket extends BasePacket
{
    protected static int $packetType = Packet::TYPE_PINGREQ;

    #[Override]
    public function read(PacketStream $stream): void
    {
        parent::read($stream);

        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength(0);
    }
}
