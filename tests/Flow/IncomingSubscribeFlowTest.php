<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow\IncomingSubscribeFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\Subscription;
use PHPUnit\Framework\TestCase;

class IncomingSubscribeFlowTest extends TestCase
{
    private const CODE_SUBSCRIBE = 'subscribe';

    private const PACKET_IDENTIFIER = 42;

    private const RETURN_CODE_SUCCESS = 0;

    private const RETURN_CODE_FAILURE = 128;

    private PacketFactory $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new IncomingSubscribeFlow($this->packetFactory, [], [], 0);

        self::assertEquals(self::CODE_SUBSCRIBE, $flow->getCode());
    }

    public function test_start_generates_suback_packet_with_single_subscription(): void
    {
        $subscription = $this->createMock(Subscription::class);

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_SUBACK)
            ->willReturn(new SubscribeResponsePacket());

        $flow = new IncomingSubscribeFlow($this->packetFactory, [$subscription], [self::RETURN_CODE_SUCCESS], self::PACKET_IDENTIFIER);
        $result = $flow->start();

        self::assertInstanceOf(SubscribeResponsePacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertEquals([self::RETURN_CODE_SUCCESS], $result->getReturnCodes());
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

        self::assertEquals($returnCodes, $result->getReturnCodes());
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

        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals($subscriptions, $flow->getResult());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new IncomingSubscribeFlow($this->packetFactory, [], [], 0);
        self::assertFalse($flow->accept(new SubscribeResponsePacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new IncomingSubscribeFlow($this->packetFactory, [], [], 0);
        self::assertNull($flow->next(new SubscribeResponsePacket()));
    }
}
