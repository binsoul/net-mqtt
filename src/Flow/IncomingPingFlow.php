<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingResponsePacket;

/**
 * Represents a flow starting with an incoming PING packet.
 */
class IncomingPingFlow extends AbstractFlow
{
    public function getCode(): string
    {
        return 'pong';
    }

    /**
     * @return PingResponsePacket
     */
    public function start(): ?Packet
    {
        $this->succeed();

        /** @var PingResponsePacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_PINGRESP);

        return $packet;
    }
}
