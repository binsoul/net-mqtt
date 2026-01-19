<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Flow\OutgoingPublishFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\Packet\PublishCompletePacket;
use BinSoul\Net\Mqtt\Packet\PublishReceivedPacket;
use BinSoul\Net\Mqtt\Packet\PublishReleasePacket;
use BinSoul\Net\Mqtt\Packet\PublishRequestPacket;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class OutgoingPublishFlowTest extends TestCase
{
    private const string CODE_PUBLISH = 'publish';

    private const int PACKET_IDENTIFIER = 42;

    private const string PAYLOAD_EMPTY = '';

    private const string PAYLOAD_JSON = '{"temperature":23.5}';

    private const string PAYLOAD_SIMPLE = 'test message';

    private const int QOS_LEVEL_AT_LEAST_ONCE = 1;

    private const int QOS_LEVEL_AT_MOST_ONCE = 0;

    private const int QOS_LEVEL_EXACTLY_ONCE = 2;

    private const string TOPIC_SENSOR = 'sensor/temperature';

    private const string TOPIC_TEST = 'test/topic';

    private PacketFactory&Stub $packetFactory;

    private PacketIdentifierGenerator&Stub $identifierGenerator;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createStub(PacketFactory::class);
        $this->identifierGenerator = $this->createStub(PacketIdentifierGenerator::class);
        $this->identifierGenerator
            ->method('generatePacketIdentifier')
            ->willReturn(self::PACKET_IDENTIFIER);
    }

    public function test_returns_correct_code(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $this->assertSame(self::CODE_PUBLISH, $flow->getCode());
    }

    public function test_start_generates_publish_packet_with_qos_0(): void
    {
        $message = new DefaultMessage(
            self::TOPIC_TEST,
            self::PAYLOAD_SIMPLE,
            self::QOS_LEVEL_AT_MOST_ONCE
        );

        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        $this->assertInstanceOf(PublishRequestPacket::class, $result);
        $this->assertSame(self::TOPIC_TEST, $result->getTopic());
        $this->assertSame(self::PAYLOAD_SIMPLE, $result->getPayload());
        $this->assertSame(self::QOS_LEVEL_AT_MOST_ONCE, $result->getQosLevel());
        $this->assertFalse($result->isRetained());
        $this->assertFalse($result->isDuplicate());
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
    }

    public function test_start_generates_publish_packet_with_qos_1(): void
    {
        $message = new DefaultMessage(
            self::TOPIC_SENSOR,
            self::PAYLOAD_JSON,
            self::QOS_LEVEL_AT_LEAST_ONCE
        );

        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        $this->assertInstanceOf(PublishRequestPacket::class, $result);
        $this->assertSame(self::TOPIC_SENSOR, $result->getTopic());
        $this->assertSame(self::PAYLOAD_JSON, $result->getPayload());
        $this->assertSame(self::QOS_LEVEL_AT_LEAST_ONCE, $result->getQosLevel());
        $this->assertSame(self::PACKET_IDENTIFIER, $result->getIdentifier());
        $this->assertFalse($flow->isFinished());
    }

    public function test_start_generates_publish_packet_with_qos_2(): void
    {
        $message = new DefaultMessage(
            self::TOPIC_TEST,
            self::PAYLOAD_EMPTY,
            self::QOS_LEVEL_EXACTLY_ONCE
        );

        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        $this->assertInstanceOf(PublishRequestPacket::class, $result);
        $this->assertSame(self::TOPIC_TEST, $result->getTopic());
        $this->assertSame(self::PAYLOAD_EMPTY, $result->getPayload());
        $this->assertSame(self::QOS_LEVEL_EXACTLY_ONCE, $result->getQosLevel());
        $this->assertSame(self::PACKET_IDENTIFIER, $result->getIdentifier());
        $this->assertFalse($flow->isFinished());
    }

    public function test_start_sets_retained_flag(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE, true);

        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();
        $this->assertInstanceOf(PublishRequestPacket::class, $result);

        $this->assertTrue($result->isRetained());
        $this->assertFalse($result->isDuplicate());
    }

    public function test_start_sets_duplicate_flag(): void
    {
        $message = new DefaultMessage(
            self::TOPIC_TEST,
            self::PAYLOAD_SIMPLE,
            self::QOS_LEVEL_AT_LEAST_ONCE,
            false,
            true
        );

        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();
        $this->assertInstanceOf(PublishRequestPacket::class, $result);

        $this->assertFalse($result->isRetained());
        $this->assertTrue($result->isDuplicate());
    }

    public function test_accept_returns_false_for_qos_0(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();

        $this->assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_puback_with_qos_1_and_correct_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        $this->assertTrue($flow->accept($packet));
    }

    public function test_accept_returns_false_for_puback_with_qos_1_and_wrong_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER + 1);

        $this->assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_false_for_puback_with_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        $this->assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_pubrec_with_qos_2_and_correct_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishReceivedPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        $this->assertTrue($flow->accept($packet));
    }

    public function test_accept_returns_false_for_pubrec_with_qos_2_and_wrong_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishReceivedPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER + 1);

        $this->assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_false_for_pubcomp_before_pubrec_received(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishCompletePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        $this->assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_pubcomp_after_pubrec_received(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $pubrecPacket = new PublishReceivedPacket();
        $pubrecPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type): PublishReleasePacket|PublishRequestPacket {
                    if ($type === Packet::TYPE_PUBREL) {
                        return new PublishReleasePacket();
                    }

                    return new PublishRequestPacket();
                }
            );

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $flow->start();
        $flow->next($pubrecPacket);

        $pubcompPacket = new PublishCompletePacket();
        $pubcompPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->assertTrue($flow->accept($pubcompPacket));
    }

    public function test_accept_returns_false_for_pubcomp_with_wrong_identifier_after_pubrec(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $pubrecPacket = new PublishReceivedPacket();
        $pubrecPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type): PublishReleasePacket|PublishRequestPacket {
                    if ($type === Packet::TYPE_PUBREL) {
                        return new PublishReleasePacket();
                    }

                    return new PublishRequestPacket();
                }
            );

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $flow->start();
        $flow->next($pubrecPacket);

        $publishCompletePacket = new PublishCompletePacket();
        $publishCompletePacket->setIdentifier(self::PACKET_IDENTIFIER + 1);

        $this->assertFalse($flow->accept($publishCompletePacket));
    }

    public function test_next_completes_flow_with_puback_for_qos_1(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $packet = new PublishAckPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory->method('build')->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $flow->start();

        $result = $flow->next($packet);

        $this->assertNotInstanceOf(PublishReleasePacket::class, $result);
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertEquals($message, $flow->getResult());
    }

    public function test_next_returns_pubrel_for_pubrec_with_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $pubrecPacket = new PublishReceivedPacket();
        $pubrecPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type): PublishReleasePacket|PublishRequestPacket {
                    if ($type === Packet::TYPE_PUBREL) {
                        return new PublishReleasePacket();
                    }

                    return new PublishRequestPacket();
                }
            );

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $flow->start();

        $result = $flow->next($pubrecPacket);

        $this->assertInstanceOf(PublishReleasePacket::class, $result);
        $this->assertSame(self::PACKET_IDENTIFIER, $result->getIdentifier());
        $this->assertFalse($flow->isFinished());
    }

    public function test_next_completes_flow_with_pubcomp_for_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $publishReceivedPacket = new PublishReceivedPacket();
        $publishReceivedPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $publishCompletePacket = new PublishCompletePacket();
        $publishCompletePacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type): PublishReleasePacket|PublishRequestPacket {
                    if ($type === Packet::TYPE_PUBREL) {
                        return new PublishReleasePacket();
                    }

                    return new PublishRequestPacket();
                }
            );

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $flow->start();
        $flow->next($publishReceivedPacket);

        $result = $flow->next($publishCompletePacket);

        $this->assertNotInstanceOf(PublishReleasePacket::class, $result);
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertEquals($message, $flow->getResult());
    }

    public function test_qos_0_does_not_generate_packet_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $packet = $flow->start();
        $this->assertInstanceOf(PublishRequestPacket::class, $packet);
        $this->assertNull($packet->getIdentifier());
    }

    public function test_qos_1_generates_packet_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $packet = $flow->start();
        $this->assertInstanceOf(PublishRequestPacket::class, $packet);
        $this->assertNotNull($packet->getIdentifier());
    }

    public function test_qos_2_generates_packet_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $packet = $flow->start();
        $this->assertInstanceOf(PublishRequestPacket::class, $packet);
        $this->assertNotNull($packet->getIdentifier());
    }
}
