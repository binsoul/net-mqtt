<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow\IncomingUnsubscribeFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\Packet\UnsubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\Subscription;
use PHPUnit\Framework\TestCase;

class IncomingUnsubscribeFlowTest extends TestCase
{
    private const string CODE_UNSUBSCRIBE = 'unsubscribe';

    private const int PACKET_IDENTIFIER = 42;

    private PacketFactory $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new IncomingUnsubscribeFlow($this->packetFactory, [], 0);

        self::assertEquals(self::CODE_UNSUBSCRIBE, $flow->getCode());
    }

    public function test_start_generates_unsuback_packet_with_single_subscription(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $packet = new UnsubscribeResponsePacket();

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_UNSUBACK)
            ->willReturn($packet);

        $flow = new IncomingUnsubscribeFlow($this->packetFactory, [$subscription], self::PACKET_IDENTIFIER);
        $result = $flow->start();

        self::assertInstanceOf(UnsubscribeResponsePacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
    }

    public function test_start_generates_unsuback_packet_with_multiple_subscriptions(): void
    {
        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);
        $subscription3 = $this->createMock(Subscription::class);

        $this->packetFactory->method('build')->willReturn(new UnsubscribeResponsePacket());

        $flow = new IncomingUnsubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2, $subscription3],
            self::PACKET_IDENTIFIER
        );
        $result = $flow->start();

        self::assertInstanceOf(UnsubscribeResponsePacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
    }

    public function test_start_immediately_succeeds_flow(): void
    {
        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);

        $this->packetFactory->method('build')->willReturn(new UnsubscribeResponsePacket());

        $subscriptions = [$subscription1, $subscription2];
        $flow = new IncomingUnsubscribeFlow($this->packetFactory, $subscriptions, self::PACKET_IDENTIFIER);
        $flow->start();

        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals($subscriptions, $flow->getResult());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new IncomingUnsubscribeFlow($this->packetFactory, [], 0);
        self::assertFalse($flow->accept(new ConnectResponsePacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new IncomingUnsubscribeFlow($this->packetFactory, [], 0);
        self::assertNull($flow->next(new ConnectResponsePacket()));
    }
}
