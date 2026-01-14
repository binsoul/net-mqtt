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
    private int $identifier;

    /**
     * @var Subscription[]
     */
    private array $subscriptions;

    /**
     * @var int[]
     */
    private array $returnCodes;

    /**
     * Constructs an instance of this class.
     *
     * @param Subscription[] $subscriptions
     * @param int[]          $returnCodes
     */
    public function __construct(PacketFactory $packetFactory, array $subscriptions, array $returnCodes, int $identifier)
    {
        parent::__construct($packetFactory);

        $this->subscriptions = array_values($subscriptions);
        $this->returnCodes = array_values($returnCodes);
        $this->identifier = $identifier;
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
