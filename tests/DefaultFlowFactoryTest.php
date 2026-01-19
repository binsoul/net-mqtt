<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\DefaultFlowFactory;
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
use BinSoul\Net\Mqtt\Message;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use BinSoul\Net\Mqtt\Subscription;
use PHPUnit\Framework\TestCase;

final class DefaultFlowFactoryTest extends TestCase
{
    private DefaultFlowFactory $factory;

    protected function setUp(): void
    {
        $clientIdentifierGenerator = $this->createMock(ClientIdentifierGenerator::class);
        $packetIdentifierGenerator = $this->createMock(PacketIdentifierGenerator::class);
        $packetFactory = $this->createMock(PacketFactory::class);

        $this->factory = new DefaultFlowFactory(
            $clientIdentifierGenerator,
            $packetIdentifierGenerator,
            $packetFactory
        );
    }

    public function test_builds_incoming_connect_flow(): void
    {
        $connection = $this->createMock(Connection::class);

        $flow = $this->factory->buildIncomingConnectFlow($connection, 0, true);

        $this->assertInstanceOf(IncomingConnectFlow::class, $flow);
    }

    public function test_builds_incoming_disconnect_flow(): void
    {
        $connection = $this->createMock(Connection::class);

        $flow = $this->factory->buildIncomingDisconnectFlow($connection);

        $this->assertInstanceOf(IncomingDisconnectFlow::class, $flow);
    }

    public function test_builds_incoming_ping_flow(): void
    {
        $flow = $this->factory->buildIncomingPingFlow();

        $this->assertInstanceOf(IncomingPingFlow::class, $flow);
    }

    public function test_builds_incoming_publish_flow_without_identifier(): void
    {
        $message = $this->createMock(Message::class);

        $flow = $this->factory->buildIncomingPublishFlow($message);

        $this->assertInstanceOf(IncomingPublishFlow::class, $flow);
    }

    public function test_builds_incoming_publish_flow_with_identifier(): void
    {
        $message = $this->createMock(Message::class);

        $flow = $this->factory->buildIncomingPublishFlow($message, 123);

        $this->assertInstanceOf(IncomingPublishFlow::class, $flow);
    }

    public function test_builds_incoming_subscribe_flow(): void
    {
        $subscriptions = [$this->createMock(Subscription::class)];

        $flow = $this->factory->buildIncomingSubscribeFlow($subscriptions, [0], 456);

        $this->assertInstanceOf(IncomingSubscribeFlow::class, $flow);
    }

    public function test_builds_incoming_unsubscribe_flow(): void
    {
        $subscriptions = [$this->createMock(Subscription::class)];

        $flow = $this->factory->buildIncomingUnsubscribeFlow($subscriptions, 789);

        $this->assertInstanceOf(IncomingUnsubscribeFlow::class, $flow);
    }

    public function test_builds_outgoing_connect_flow(): void
    {
        $connection = $this->createMock(Connection::class);

        $flow = $this->factory->buildOutgoingConnectFlow($connection);

        $this->assertInstanceOf(OutgoingConnectFlow::class, $flow);
    }

    public function test_builds_outgoing_disconnect_flow(): void
    {
        $connection = $this->createMock(Connection::class);

        $flow = $this->factory->buildOutgoingDisconnectFlow($connection);

        $this->assertInstanceOf(OutgoingDisconnectFlow::class, $flow);
    }

    public function test_builds_outgoing_ping_flow(): void
    {
        $flow = $this->factory->buildOutgoingPingFlow();

        $this->assertInstanceOf(OutgoingPingFlow::class, $flow);
    }

    public function test_builds_outgoing_publish_flow(): void
    {
        $message = $this->createMock(Message::class);

        $flow = $this->factory->buildOutgoingPublishFlow($message);

        $this->assertInstanceOf(OutgoingPublishFlow::class, $flow);
    }

    public function test_builds_outgoing_subscribe_flow(): void
    {
        $subscriptions = [$this->createMock(Subscription::class)];

        $flow = $this->factory->buildOutgoingSubscribeFlow($subscriptions);

        $this->assertInstanceOf(OutgoingSubscribeFlow::class, $flow);
    }

    public function test_builds_outgoing_unsubscribe_flow(): void
    {
        $subscriptions = [$this->createMock(Subscription::class)];

        $flow = $this->factory->buildOutgoingUnsubscribeFlow($subscriptions);

        $this->assertInstanceOf(OutgoingUnsubscribeFlow::class, $flow);
    }
}
