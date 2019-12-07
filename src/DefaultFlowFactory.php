<?php

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Flow\IncomingPingFlow;
use BinSoul\Net\Mqtt\Flow\IncomingPublishFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingConnectFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingDisconnectFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingPingFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingPublishFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingSubscribeFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingUnsubscribeFlow;

/**
 * Provides a default implementation of the {@see FlowFactory} interface.
 */
class DefaultFlowFactory implements FlowFactory
{
    /**
     * @var ClientIdentifierGenerator
     */
    private $clientIdentifierGenerator;
    /**
     * @var PacketIdentifierGenerator
     */
    private $packetIdentifierGenerator;
    /**
     * @var PacketFactory
     */
    private $packetFactory;

    /**
     * Constructs an instance of this class.
     *
     * @param ClientIdentifierGenerator $clientIdentifierGenerator
     * @param PacketIdentifierGenerator $packetIdentifierGenerator
     * @param PacketFactory             $packetFactory
     */
    public function __construct(
        ClientIdentifierGenerator $clientIdentifierGenerator,
        PacketIdentifierGenerator $packetIdentifierGenerator,
        PacketFactory $packetFactory
    ) {
        $this->clientIdentifierGenerator = $clientIdentifierGenerator;
        $this->packetIdentifierGenerator = $packetIdentifierGenerator;
        $this->packetFactory = $packetFactory;
    }

    public function buildIncomingPingFlow()
    {
        return new IncomingPingFlow($this->packetFactory);
    }

    public function buildIncomingPublishFlow(Message $message, $identifier = null)
    {
        return new IncomingPublishFlow($this->packetFactory, $message, $identifier);
    }

    public function buildOutgoingConnectFlow(Connection $connection)
    {
        return new OutgoingConnectFlow($this->packetFactory, $connection, $this->clientIdentifierGenerator);
    }

    public function buildOutgoingDisconnectFlow(Connection $connection)
    {
        return new OutgoingDisconnectFlow($this->packetFactory, $connection);
    }

    public function buildOutgoingPingFlow()
    {
        return new OutgoingPingFlow($this->packetFactory);
    }

    public function buildOutgoingPublishFlow(Message $message)
    {
        return new OutgoingPublishFlow($this->packetFactory, $message, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingSubscribeFlow(array $subscriptions)
    {
        return new OutgoingSubscribeFlow($this->packetFactory, $subscriptions, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingUnsubscribeFlow(array $subscriptions)
    {
        return new OutgoingUnsubscribeFlow($this->packetFactory, $subscriptions, $this->packetIdentifierGenerator);
    }
}
