<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\UnsubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\Subscription;

/**
 * Represents a flow starting with an incoming UNSUBSCRIBE packet.
 */
class IncomingUnsubscribeFlow extends AbstractFlow
{
    /**
     * Constructs an instance of this class.
     *
     * @param array<int, Subscription> $subscriptions
     * @param int<1, 65535>            $identifier
     */
    public function __construct(
        PacketFactory $packetFactory,
        private array $subscriptions,
        private readonly int $identifier
    ) {
        parent::__construct($packetFactory);

        $this->subscriptions = array_values($subscriptions);
    }

    public function getCode(): string
    {
        return 'unsubscribe';
    }

    /**
     * @return UnsubscribeResponsePacket
     */
    public function start(): ?Packet
    {
        $this->succeed($this->subscriptions);

        /** @var UnsubscribeResponsePacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_UNSUBACK);
        $packet->setIdentifier($this->identifier);

        return $packet;
    }
}
