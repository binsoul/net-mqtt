<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Flow\IncomingPublishFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\Packet\PublishCompletePacket;
use BinSoul\Net\Mqtt\Packet\PublishReceivedPacket;
use BinSoul\Net\Mqtt\Packet\PublishReleasePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class IncomingPublishFlowTest extends TestCase
{
    private const string CODE_MESSAGE = 'message';

    private const int PACKET_IDENTIFIER = 42;

    private const string PAYLOAD_SIMPLE = 'test message';

    private const int QOS_LEVEL_AT_LEAST_ONCE = 1;

    private const int QOS_LEVEL_AT_MOST_ONCE = 0;

    private const int QOS_LEVEL_EXACTLY_ONCE = 2;

    private const string TOPIC_TEST = 'test/topic';

    private PacketFactory&Stub $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createStub(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST);
        $flow = new IncomingPublishFlow($this->packetFactory, $message);

        $this->assertSame(self::CODE_MESSAGE, $flow->getCode());
    }

    public function test_start_returns_null_for_qos_0(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE);

        $flow = new IncomingPublishFlow($this->packetFactory, $message);
        $result = $flow->start();

        $this->assertNull($result);
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertEquals($message, $flow->getResult());
    }

    public function test_start_returns_puback_for_qos_1(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);

        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PUBACK)
            ->willReturn(new PublishAckPacket());

        $flow = new IncomingPublishFlow($packetFactory, $message, self::PACKET_IDENTIFIER);
        $result = $flow->start();

        $this->assertInstanceOf(PublishAckPacket::class, $result);
        $this->assertSame(self::PACKET_IDENTIFIER, $result->getIdentifier());
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertEquals($message, $flow->getResult());
    }

    public function test_start_returns_pubrec_for_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);

        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PUBREC)
            ->willReturn(new PublishReceivedPacket());

        $flow = new IncomingPublishFlow($packetFactory, $message, self::PACKET_IDENTIFIER);
        $result = $flow->start();

        $this->assertInstanceOf(PublishReceivedPacket::class, $result);
        $this->assertSame(self::PACKET_IDENTIFIER, $result->getIdentifier());
        $this->assertFalse($flow->isFinished());
    }

    public function test_accept_returns_false_for_qos_0(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new IncomingPublishFlow($this->packetFactory, $message);

        $this->assertFalse($flow->accept(new PublishReleasePacket()));
    }

    public function test_accept_returns_false_for_qos_1(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);

        $packet = new PublishReleasePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        $this->assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_false_for_wrong_packet_type_with_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);

        $this->assertFalse($flow->accept(new PublishAckPacket()));
    }

    public function test_accept_returns_true_for_pubrel_with_qos_2_and_correct_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);

        $packet = new PublishReleasePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        $this->assertTrue($flow->accept($packet));
    }

    public function test_accept_returns_false_for_pubrel_with_qos_2_and_wrong_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);

        $packet = new PublishReleasePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER + 1);

        $this->assertFalse($flow->accept($packet));
    }

    public function test_next_completes_flow_and_returns_pubcomp(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $publishReleasePacket = new PublishReleasePacket();
        $publishReleasePacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type): PublishReceivedPacket|PublishCompletePacket|null {
                    if ($type === Packet::TYPE_PUBREC) {
                        return new PublishReceivedPacket();
                    }

                    if ($type === Packet::TYPE_PUBCOMP) {
                        return new PublishCompletePacket();
                    }

                    return null;
                }
            );

        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);
        $flow->start();

        $result = $flow->next($publishReleasePacket);

        $this->assertInstanceOf(PublishCompletePacket::class, $result);
        $this->assertSame(self::PACKET_IDENTIFIER, $result->getIdentifier());
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertEquals($message, $flow->getResult());
    }

    public function test_flow_is_finished_after_start_for_qos_0(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE);

        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);
        $flow->start();

        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
    }

    public function test_flow_is_finished_after_start_for_qos_1(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishAckPacket());

        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);
        $flow->start();

        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
    }

    public function test_flow_is_not_finished_after_start_for_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishReceivedPacket());

        $flow = new IncomingPublishFlow($this->packetFactory, $message, self::PACKET_IDENTIFIER);
        $flow->start();

        $this->assertFalse($flow->isFinished());
        $this->assertFalse($flow->isSuccess());
    }
}
