<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\UnsubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\UnsubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use BinSoul\Net\Mqtt\Subscription;
use Override;

/**
 * Represents a flow starting with an outgoing UNSUBSCRIBE packet.
 */
class OutgoingUnsubscribeFlow extends AbstractFlow
{
    /**
     * @var int<1, 65535>
     */
    private int $identifier;

    /**
     * Constructs an instance of this class.
     *
     * @param array<int, Subscription> $subscriptions
     */
    public function __construct(
        PacketFactory $packetFactory,
        private array $subscriptions,
        PacketIdentifierGenerator $generator
    ) {
        parent::__construct($packetFactory);

        $this->subscriptions = array_values($subscriptions);
        $this->identifier = $generator->generatePacketIdentifier();
    }

    public function getCode(): string
    {
        return 'unsubscribe';
    }

    /**
     * @return UnsubscribeRequestPacket
     */
    public function start(): ?Packet
    {
        /** @var UnsubscribeRequestPacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_UNSUBSCRIBE);
        $packet->setIdentifier($this->identifier);

        $topics = [];

        foreach ($this->subscriptions as $subscription) {
            $topics[] = $subscription->getFilter();
        }

        $packet->setFilters($topics);

        return $packet;
    }

    #[Override]
    public function accept(Packet $packet): bool
    {
        if ($packet->getPacketType() !== Packet::TYPE_UNSUBACK) {
            return false;
        }

        /** @var UnsubscribeResponsePacket $packet */
        return $packet->getIdentifier() === $this->identifier;
    }

    #[Override]
    public function next(Packet $packet): ?Packet
    {
        $this->succeed($this->subscriptions);

        return null;
    }
}
