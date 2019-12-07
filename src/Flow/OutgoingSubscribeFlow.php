<?php

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\SubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use BinSoul\Net\Mqtt\Subscription;

/**
 * Represents a flow starting with an outgoing SUBSCRIBE packet.
 */
class OutgoingSubscribeFlow extends AbstractFlow
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

        $this->subscriptions = array_values($subscriptions);
        $this->identifier = $generator->generatePacketIdentifier();
    }

    public function getCode()
    {
        return 'subscribe';
    }

    public function start()
    {
        /** @var SubscribeRequestPacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_SUBSCRIBE);
        $packet->setTopic($this->subscriptions[0]->getFilter());
        $packet->setQosLevel($this->subscriptions[0]->getQosLevel());
        $packet->setIdentifier($this->identifier);

        return $packet;
    }

    public function accept(Packet $packet)
    {
        if ($packet->getPacketType() !== Packet::TYPE_SUBACK) {
            return false;
        }

        /** @var SubscribeResponsePacket $packet */
        return $packet->getIdentifier() === $this->identifier;
    }

    public function next(Packet $packet)
    {
        /** @var SubscribeResponsePacket $packet */
        $returnCodes = $packet->getReturnCodes();
        if (count($returnCodes) !== count($this->subscriptions)) {
            throw new \LogicException(
                sprintf(
                    'SUBACK: Expected %d return codes but got %d.',
                    count($this->subscriptions),
                    count($returnCodes)
                )
            );
        }

        foreach ($returnCodes as $index => $code) {
            if ($packet->isError($code)) {
                $this->fail(sprintf('Failed to subscribe to "%s".', $this->subscriptions[$index]->getFilter()));

                return null;
            }
        }

        $this->succeed($this->subscriptions[0]);

        return null;
    }
}
