<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow\IncomingSubscribeFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\Subscription;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class IncomingSubscribeFlowTest extends TestCase
{
    private const string CODE_SUBSCRIBE = 'subscribe';

    private const int PACKET_IDENTIFIER = 42;

    private const int RETURN_CODE_SUCCESS = 0;

    private const int RETURN_CODE_FAILURE = 128;

    private PacketFactory&MockObject $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new IncomingSubscribeFlow($this->packetFactory, [], [], 0);

        $this->assertSame(self::CODE_SUBSCRIBE, $flow->getCode());
    }

    public function test_start_generates_suback_packet_with_single_subscription(): void
    {
        $subscription = $this->createMock(Subscription::class);

        $this->packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_SUBACK)
            ->willReturn(new SubscribeResponsePacket());

        $flow = new IncomingSubscribeFlow($this->packetFactory, [$subscription], [self::RETURN_CODE_SUCCESS], self::PACKET_IDENTIFIER);
        $result = $flow->start();

        $this->assertInstanceOf(SubscribeResponsePacket::class, $result);
        $this->assertSame(self::PACKET_IDENTIFIER, $result->getIdentifier());
        $this->assertSame([self::RETURN_CODE_SUCCESS], $result->getReturnCodes());
    }

    public function test_start_generates_suback_packet_with_multiple_subscriptions(): void
    {
        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);
        $subscription3 = $this->createMock(Subscription::class);

        $this->packetFactory->method('build')->willReturn(new SubscribeResponsePacket());

        $returnCodes = [self::RETURN_CODE_SUCCESS, self::RETURN_CODE_SUCCESS, self::RETURN_CODE_FAILURE];
        $flow = new IncomingSubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2, $subscription3],
            $returnCodes,
            self::PACKET_IDENTIFIER
        );
        $result = $flow->start();
        $this->assertInstanceOf(SubscribeResponsePacket::class, $result);

        $this->assertSame($returnCodes, $result->getReturnCodes());
    }

    public function test_start_immediately_succeeds_flow(): void
    {
        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);
        $subscriptions = [$subscription1, $subscription2];

        $this->packetFactory->method('build')->willReturn(new SubscribeResponsePacket());

        $flow = new IncomingSubscribeFlow(
            $this->packetFactory,
            $subscriptions,
            [self::RETURN_CODE_SUCCESS, self::RETURN_CODE_SUCCESS],
            self::PACKET_IDENTIFIER
        );

        $flow->start();

        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertEquals($subscriptions, $flow->getResult());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new IncomingSubscribeFlow($this->packetFactory, [], [], 0);
        $this->assertFalse($flow->accept(new SubscribeResponsePacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new IncomingSubscribeFlow($this->packetFactory, [], [], 0);
        $this->assertNotInstanceOf(Packet::class, $flow->next(new SubscribeResponsePacket()));
    }
}
