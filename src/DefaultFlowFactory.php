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
     * Constructs an instance of this class.
     *
     * @param ClientIdentifierGenerator $clientIdentifierGenerator
     * @param PacketIdentifierGenerator $packetIdentifierGenerator
     */
    public function __construct(
        ClientIdentifierGenerator $clientIdentifierGenerator,
        PacketIdentifierGenerator $packetIdentifierGenerator
    ) {
        $this->clientIdentifierGenerator = $clientIdentifierGenerator;
        $this->packetIdentifierGenerator = $packetIdentifierGenerator;
    }

    public function buildIncomingPingFlow()
    {
        return new IncomingPingFlow();
    }

    public function buildIncomingPublishFlow(Message $message, $identifier = null)
    {
        return new IncomingPublishFlow($message, $identifier);
    }

    public function buildOutgoingConnectFlow(Connection $connection)
    {
        return new OutgoingConnectFlow($connection, $this->clientIdentifierGenerator);
    }

    public function buildOutgoingDisconnectFlow(Connection $connection)
    {
        return new OutgoingDisconnectFlow($connection);
    }

    public function buildOutgoingPingFlow()
    {
        return new OutgoingPingFlow();
    }

    public function buildOutgoingPublishFlow(Message $message)
    {
        return new OutgoingPublishFlow($message, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingSubscribeFlow(array $subscriptions)
    {
        return new OutgoingSubscribeFlow($subscriptions, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingUnsubscribeFlow(array $subscriptions)
    {
        return new OutgoingUnsubscribeFlow($subscriptions, $this->packetIdentifierGenerator);
    }
}
