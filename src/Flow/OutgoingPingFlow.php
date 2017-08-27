<?php

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;

/**
 * Represents a flow starting with an outgoing PING packet.
 */
class OutgoingPingFlow extends AbstractFlow
{
    public function getCode()
    {
        return Flow::CODE_PING;
    }

    public function start()
    {
        return new PingRequestPacket();
    }

    public function accept(Packet $packet)
    {
        return $packet->getPacketType() === Packet::TYPE_PINGRESP;
    }

    public function next(Packet $packet)
    {
        $this->succeed();
    }
}
