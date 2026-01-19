<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\Subscription;

/**
 * Represents a flow starting with an incoming SUBSCRIBE packet.
 */
class IncomingSubscribeFlow extends AbstractFlow
{
    /**
     * Constructs an instance of this class.
     *
     * @param array<int, Subscription> $subscriptions
     * @param array<int, int<0, 128>>  $returnCodes
     * @param int<1, 65535>            $identifier
     */
    public function __construct(
        PacketFactory $packetFactory,
        private array $subscriptions,
        private array $returnCodes,
        private readonly int $identifier
    ) {
        parent::__construct($packetFactory);

        $this->subscriptions = array_values($subscriptions);
        $this->returnCodes = array_values($returnCodes);
    }

    public function getCode(): string
    {
        return 'subscribe';
    }

    /**
     * @return SubscribeResponsePacket
     */
    public function start(): ?Packet
    {
        $this->succeed($this->subscriptions);

        /** @var SubscribeResponsePacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_SUBACK);
        $packet->setIdentifier($this->identifier);
        $packet->setReturnCodes($this->returnCodes);

        return $packet;
    }
}
