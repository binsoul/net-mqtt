<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\DefaultSubscription;
use BinSoul\Net\Mqtt\Flow\OutgoingSubscribeFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\Packet\SubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class OutgoingSubscribeFlowTest extends TestCase
{
    private const CODE_SUBSCRIBE = 'subscribe';

    private const ERROR_CODE_FAILURE = 128;

    private const PACKET_IDENTIFIER = 12345;

    private const QOS_LEVEL_AT_MOST_ONCE = 0;

    private const QOS_LEVEL_AT_LEAST_ONCE = 1;

    private const QOS_LEVEL_EXACTLY_ONCE = 2;

    private const TOPIC_FILTER_ALL = '#';

    private const TOPIC_FILTER_SENSOR = 'sensor/+/temperature';

    private const TOPIC_FILTER_TEST = 'test/topic';

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
        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        self::assertEquals(self::CODE_SUBSCRIBE, $flow->getCode());
    }

    public function test_start_generates_subscribe_packet_with_single_subscription(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_LEAST_ONCE);
        $packet = new SubscribeRequestPacket();

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_SUBSCRIBE)
            ->willReturn($packet);

        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);
        $result = $flow->start();

        self::assertInstanceOf(SubscribeRequestPacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertEquals([self::TOPIC_FILTER_TEST], $result->getTopics());
        self::assertEquals([self::QOS_LEVEL_AT_LEAST_ONCE], $result->getQosLevels());
    }

    public function test_start_generates_subscribe_packet_with_multiple_subscriptions(): void
    {
        $subscription1 = new DefaultSubscription(self::TOPIC_FILTER_ALL, self::QOS_LEVEL_AT_MOST_ONCE);
        $subscription2 = new DefaultSubscription(self::TOPIC_FILTER_SENSOR, self::QOS_LEVEL_EXACTLY_ONCE);
        $packet = new SubscribeRequestPacket();

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_SUBSCRIBE)
            ->willReturn($packet);

        $flow = new OutgoingSubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2],
            $this->identifierGenerator
        );
        $result = $flow->start();

        self::assertInstanceOf(SubscribeRequestPacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertEquals([self::TOPIC_FILTER_ALL, self::TOPIC_FILTER_SENSOR], $result->getTopics());
        self::assertEquals([self::QOS_LEVEL_AT_MOST_ONCE, self::QOS_LEVEL_EXACTLY_ONCE], $result->getQosLevels());
    }

    public function test_accept_returns_false_for_wrong_packet_type(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $wrongPacket = new PublishAckPacket();

        self::assertFalse($flow->accept($wrongPacket));
    }

    public function test_accept_returns_false_for_wrong_identifier(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new SubscribeResponsePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER + 1);

        self::assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_correct_packet(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new SubscribeResponsePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        self::assertTrue($flow->accept($packet));
    }

    public function test_next_throws_runtime_exception_for_wrong_packet_class(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $wrongPacket = new PublishAckPacket();

        $this->expectException(RuntimeException::class);

        $flow->next($wrongPacket);
    }

    public function test_next_throws_logic_exception_for_mismatched_return_code_count(): void
    {
        $subscription1 = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $subscription2 = new DefaultSubscription(self::TOPIC_FILTER_SENSOR, self::QOS_LEVEL_AT_LEAST_ONCE);
        $flow = new OutgoingSubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2],
            $this->identifierGenerator
        );

        $packet = new SubscribeResponsePacket();
        $packet->setReturnCodes([self::QOS_LEVEL_AT_MOST_ONCE]);

        $this->expectException(LogicException::class);

        $flow->next($packet);
    }

    public function test_next_fails_flow_when_return_code_is_error(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new SubscribeResponsePacket();
        $packet->setReturnCodes([self::ERROR_CODE_FAILURE]);

        $result = $flow->next($packet);

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertFalse($flow->isSuccess());
    }

    public function test_next_fails_flow_on_first_error_with_multiple_subscriptions(): void
    {
        $subscription1 = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $subscription2 = new DefaultSubscription(self::TOPIC_FILTER_SENSOR, self::QOS_LEVEL_AT_LEAST_ONCE);
        $flow = new OutgoingSubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2],
            $this->identifierGenerator
        );

        $packet = new SubscribeResponsePacket();
        $packet->setReturnCodes([self::QOS_LEVEL_AT_MOST_ONCE, self::ERROR_CODE_FAILURE]);

        $result = $flow->next($packet);

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertFalse($flow->isSuccess());
    }

    public function test_next_succeeds_flow_with_valid_return_codes(): void
    {
        $subscription = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingSubscribeFlow($this->packetFactory, [$subscription], $this->identifierGenerator);

        $packet = new SubscribeResponsePacket();
        $packet->setReturnCodes([self::QOS_LEVEL_AT_MOST_ONCE]);

        $result = $flow->next($packet);

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals([$subscription], $flow->getResult());
    }

    public function test_next_succeeds_flow_with_multiple_valid_return_codes(): void
    {
        $subscription1 = new DefaultSubscription(self::TOPIC_FILTER_ALL, self::QOS_LEVEL_AT_MOST_ONCE);
        $subscription2 = new DefaultSubscription(self::TOPIC_FILTER_SENSOR, self::QOS_LEVEL_AT_LEAST_ONCE);
        $subscription3 = new DefaultSubscription(self::TOPIC_FILTER_TEST, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingSubscribeFlow(
            $this->packetFactory,
            [$subscription1, $subscription2, $subscription3],
            $this->identifierGenerator
        );

        $packet = new SubscribeResponsePacket();
        $packet->setReturnCodes([
            self::QOS_LEVEL_AT_MOST_ONCE,
            self::QOS_LEVEL_AT_LEAST_ONCE,
            self::QOS_LEVEL_EXACTLY_ONCE,
        ]);

        $result = $flow->next($packet);

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals([$subscription1, $subscription2, $subscription3], $flow->getResult());
    }
}
