<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\DefaultSubscription;
use BinSoul\Net\Mqtt\Flow\OutgoingUnsubscribeFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\Packet\UnsubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\UnsubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use PHPUnit\Framework\TestCase;

class OutgoingUnsubscribeFlowTest extends TestCase
{
    private const string CODE_UNSUBSCRIBE = 'unsubscribe';

    private const int PACKET_IDENTIFIER = 54321;

    private const int QOS_LEVEL_AT_LEAST_ONCE = 1;

    private const int QOS_LEVEL_AT_MOST_ONCE = 0;

    private const int QOS_LEVEL_EXACTLY_ONCE = 2;

    private const string TOPIC_FILTER_ALL = '#';

    private const string TOPIC_FILTER_DEVICE = 'device/+/status';

    private const string TOPIC_FILTER_SENSOR = 'sensor/+/temperature';

    private const string TOPIC_FILTER_TEST = 'test/topic';

    private PacketFactory $packetFactory;

    private PacketIdentifierGenerator $identifierGenerator;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
        $this->identifierGenerator = $this->createMock(PacketIdentifierGenerator::class);
        $this->identifierGenerator
            ->method('generatePacketIdentifier')
            ->willReturn(self::PACKET_IDENTIFIER);
    }

    public function test_returns_correct_code(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingUnsubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        self::assertEquals(self::CODE_UNSUBSCRIBE, $flow->getCode());
    }

    public function test_start_generates_unsubscribe_packet_with_single_subscription(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_LEAST_ONCE);
        $packet = new UnsubscribeRequestPacket();

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_UNSUBSCRIBE)
            ->willReturn($packet);

        $flow = new OutgoingUnsubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);
        $result = $flow->start();

        self::assertInstanceOf(UnsubscribeRequestPacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertEquals([self::TOPIC_FILTER_TEST], $result->getFilters());
    }

    public function test_start_generates_unsubscribe_packet_with_multiple_subscriptions(): void
    {
        $subscription1 = new DefaultSubscription(self::TOPIC_FILTER_ALL, self::QOS_LEVEL_AT_MOST_ONCE);
        $subscription2 = new DefaultSubscription(self::TOPIC_FILTER_SENSOR, self::QOS_LEVEL_EXACTLY_ONCE);
        $subscription3 = new DefaultSubscription(self::TOPIC_FILTER_DEVICE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $packet = new UnsubscribeRequestPacket();

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_UNSUBSCRIBE)
            ->willReturn($packet);

        $flow = new OutgoingUnsubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2, $subscription3],
            $this->identifierGenerator
        );
        $result = $flow->start();

        self::assertInstanceOf(UnsubscribeRequestPacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertEquals(
            [self::TOPIC_FILTER_ALL, self::TOPIC_FILTER_SENSOR, self::TOPIC_FILTER_DEVICE],
            $result->getFilters()
        );
    }

    public function test_accept_returns_false_for_wrong_packet_type(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingUnsubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $wrongPacket = new PublishAckPacket();

        self::assertFalse($flow->accept($wrongPacket));
    }

    public function test_accept_returns_false_for_wrong_identifier(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingUnsubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new UnsubscribeResponsePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER + 1);

        self::assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_correct_packet(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingUnsubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new UnsubscribeResponsePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        self::assertTrue($flow->accept($packet));
    }

    public function test_next_succeeds_flow_with_single_subscription(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingUnsubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new UnsubscribeResponsePacket();

        $result = $flow->next($packet);

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals([$subscription], $flow->getResult());
    }

    public function test_next_succeeds_flow_with_multiple_subscriptions(): void
    {
        $subscription1 = new DefaultSubscription(self::TOPIC_FILTER_ALL, self::QOS_LEVEL_AT_MOST_ONCE);
        $subscription2 = new DefaultSubscription(self::TOPIC_FILTER_SENSOR, self::QOS_LEVEL_AT_LEAST_ONCE);
        $subscription3 = new DefaultSubscription(self::TOPIC_FILTER_DEVICE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingUnsubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2, $subscription3],
            $this->identifierGenerator
        );

        $packet = new UnsubscribeResponsePacket();

        $result = $flow->next($packet);

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals([$subscription1, $subscription2, $subscription3], $flow->getResult());
    }

    public function test_next_always_succeeds_regardless_of_packet_content(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingUnsubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new UnsubscribeResponsePacket();

        $result = $flow->next($packet);

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEmpty($flow->getErrorMessage());
    }
}
