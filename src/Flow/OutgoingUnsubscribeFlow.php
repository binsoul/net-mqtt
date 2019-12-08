<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\UnsubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\UnsubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use BinSoul\Net\Mqtt\Subscription;

/**
 * Represents a flow starting with an outgoing UNSUBSCRIBE packet.
 */
class OutgoingUnsubscribeFlow extends AbstractFlow
{
    /** @var int */
    private $identifier;
    /** @var Subscription[] */
    private $subscriptions;

    /**
     * Constructs an instance of this class.
     *
     * @param PacketFactory             $packetFactory
     * @param Subscription[]            $subscriptions
     * @param PacketIdentifierGenerator $generator
     */
    public function __construct(PacketFactory $packetFactory, array $subscriptions, PacketIdentifierGenerator $generator)
    {
        parent::__construct($packetFactory);

        $this->subscriptions = $subscriptions;
        $this->identifier = $generator->generatePacketIdentifier();
    }

    public function getCode(): string
    {
        return 'unsubscribe';
    }

    public function start(): ?Packet
    {
        /** @var UnsubscribeRequestPacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_UNSUBSCRIBE);
        $packet->setIdentifier($this->identifier);

        $topics = [];
        foreach ($this->subscriptions as $subscription) {
            $topics[] = $subscription->getFilter();
        }
        $packet->setTopics($topics);

        return $packet;
    }

    public function accept(Packet $packet): bool
    {
        if ($packet->getPacketType() !== Packet::TYPE_UNSUBACK) {
            return false;
        }

        /** @var UnsubscribeResponsePacket $packet */
        return $packet->getIdentifier() === $this->identifier;
    }

    public function next(Packet $packet): ?Packet
    {
        $this->succeed($this->subscriptions);

        return null;
    }
}
