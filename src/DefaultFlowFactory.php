<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Flow\IncomingConnectFlow;
use BinSoul\Net\Mqtt\Flow\IncomingDisconnectFlow;
use BinSoul\Net\Mqtt\Flow\IncomingPingFlow;
use BinSoul\Net\Mqtt\Flow\IncomingPublishFlow;
use BinSoul\Net\Mqtt\Flow\IncomingSubscribeFlow;
use BinSoul\Net\Mqtt\Flow\IncomingUnsubscribeFlow;
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
    private ClientIdentifierGenerator $clientIdentifierGenerator;

    private PacketIdentifierGenerator $packetIdentifierGenerator;

    private PacketFactory $packetFactory;

    /**
     * Constructs an instance of this class.
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

    public function buildIncomingConnectFlow(Connection $connection, int $returnCode, bool $sessionPresent): Flow
    {
        return new IncomingConnectFlow($this->packetFactory, $connection, $returnCode, $sessionPresent);
    }

    public function buildIncomingDisconnectFlow(Connection $connection): Flow
    {
        return new IncomingDisconnectFlow($this->packetFactory, $connection);
    }

    public function buildIncomingPingFlow(): Flow
    {
        return new IncomingPingFlow($this->packetFactory);
    }

    public function buildIncomingPublishFlow(Message $message, int $identifier = null): Flow
    {
        return new IncomingPublishFlow($this->packetFactory, $message, $identifier);
    }

    public function buildIncomingSubscribeFlow(array $subscriptions, array $returnCodes, int $identifier): Flow
    {
        return new IncomingSubscribeFlow($this->packetFactory, $subscriptions, $returnCodes, $identifier);
    }

    public function buildIncomingUnsubscribeFlow(array $subscriptions, int $identifier): Flow
    {
        return new IncomingUnsubscribeFlow($this->packetFactory, $subscriptions, $identifier);
    }

    public function buildOutgoingConnectFlow(Connection $connection): Flow
    {
        return new OutgoingConnectFlow($this->packetFactory, $connection, $this->clientIdentifierGenerator);
    }

    public function buildOutgoingDisconnectFlow(Connection $connection): Flow
    {
        return new OutgoingDisconnectFlow($this->packetFactory, $connection);
    }

    public function buildOutgoingPingFlow(): Flow
    {
        return new OutgoingPingFlow($this->packetFactory);
    }

    public function buildOutgoingPublishFlow(Message $message): Flow
    {
        return new OutgoingPublishFlow($this->packetFactory, $message, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingSubscribeFlow(array $subscriptions): Flow
    {
        return new OutgoingSubscribeFlow($this->packetFactory, $subscriptions, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingUnsubscribeFlow(array $subscriptions): Flow
    {
        return new OutgoingUnsubscribeFlow($this->packetFactory, $subscriptions, $this->packetIdentifierGenerator);
    }
}
