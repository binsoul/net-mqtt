<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;

/**
 * Represents a flow starting with an outgoing PING packet.
 */
class OutgoingPingFlow extends AbstractFlow
{
    public function getCode(): string
    {
        return 'ping';
    }

    /**
     * @return PingRequestPacket
     */
    public function start(): ?Packet
    {
        /** @var PingRequestPacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_PINGREQ);

        return $packet;
    }

    public function accept(Packet $packet): bool
    {
        return $packet->getPacketType() === Packet::TYPE_PINGRESP;
    }

    public function next(Packet $packet): ?Packet
    {
        $this->succeed();

        return null;
    }
}
