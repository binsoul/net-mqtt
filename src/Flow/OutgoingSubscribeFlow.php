<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\SubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use BinSoul\Net\Mqtt\Subscription;
use LogicException;
use RuntimeException;

/**
 * Represents a flow starting with an outgoing SUBSCRIBE packet.
 */
class OutgoingSubscribeFlow extends AbstractFlow
{
    private int $identifier;

    /**
     * @var Subscription[]
     */
    private array $subscriptions;

    /**
     * Constructs an instance of this class.
     *
     * @param Subscription[] $subscriptions
     */
    public function __construct(PacketFactory $packetFactory, array $subscriptions, PacketIdentifierGenerator $generator)
    {
        parent::__construct($packetFactory);

        $this->subscriptions = array_values($subscriptions);
        $this->identifier = $generator->generatePacketIdentifier();
    }

    public function getCode(): string
    {
        return 'subscribe';
    }

    /**
     * @return SubscribeRequestPacket
     */
    public function start(): ?Packet
    {
        /** @var SubscribeRequestPacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_SUBSCRIBE);
        $packet->setIdentifier($this->identifier);

        $topics = [];
        $qosLevels = [];

        foreach ($this->subscriptions as $subscription) {
            $topics[] = $subscription->getFilter();
            $qosLevels[] = $subscription->getQosLevel();
        }

        $packet->setTopics($topics);
        $packet->setQosLevels($qosLevels);

        return $packet;
    }

    public function accept(Packet $packet): bool
    {
        if ($packet->getPacketType() !== Packet::TYPE_SUBACK) {
            return false;
        }

        /** @var SubscribeResponsePacket $packet */
        return $packet->getIdentifier() === $this->identifier;
    }

    public function next(Packet $packet): ?Packet
    {
        if (! ($packet instanceof SubscribeResponsePacket)) {
            throw new RuntimeException(
                sprintf(
                    'SUBACK: Expected packet of class %s but got %s.',
                    SubscribeResponsePacket::class,
                    get_class($packet)
                )
            );
        }

        $returnCodes = $packet->getReturnCodes();

        if (count($returnCodes) !== count($this->subscriptions)) {
            throw new LogicException(
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

        $this->succeed($this->subscriptions);

        return null;
    }
}
