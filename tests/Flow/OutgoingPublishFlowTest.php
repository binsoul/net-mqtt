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
use PHPUnit\Framework\TestCase;

class OutgoingPublishFlowTest extends TestCase
{
    private const CODE_PUBLISH = 'publish';

    private const PACKET_IDENTIFIER = 42;

    private const PAYLOAD_EMPTY = '';

    private const PAYLOAD_JSON = '{"temperature":23.5}';

    private const PAYLOAD_SIMPLE = 'test message';

    private const QOS_LEVEL_AT_LEAST_ONCE = 1;

    private const QOS_LEVEL_AT_MOST_ONCE = 0;

    private const QOS_LEVEL_EXACTLY_ONCE = 2;

    private const TOPIC_SENSOR = 'sensor/temperature';

    private const TOPIC_TEST = 'test/topic';

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
        $message = new DefaultMessage(self::TOPIC_TEST);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        self::assertEquals(self::CODE_PUBLISH, $flow->getCode());
    }

    public function test_start_generates_publish_packet_with_qos_0(): void
    {
        $message = new DefaultMessage(
            self::TOPIC_TEST,
            self::PAYLOAD_SIMPLE,
            self::QOS_LEVEL_AT_MOST_ONCE
        );

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        self::assertInstanceOf(PublishRequestPacket::class, $result);
        self::assertEquals(self::TOPIC_TEST, $result->getTopic());
        self::assertEquals(self::PAYLOAD_SIMPLE, $result->getPayload());
        self::assertEquals(self::QOS_LEVEL_AT_MOST_ONCE, $result->getQosLevel());
        self::assertFalse($result->isRetained());
        self::assertFalse($result->isDuplicate());
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
    }

    public function test_start_generates_publish_packet_with_qos_1(): void
    {
        $message = new DefaultMessage(
            self::TOPIC_SENSOR,
            self::PAYLOAD_JSON,
            self::QOS_LEVEL_AT_LEAST_ONCE
        );

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        self::assertInstanceOf(PublishRequestPacket::class, $result);
        self::assertEquals(self::TOPIC_SENSOR, $result->getTopic());
        self::assertEquals(self::PAYLOAD_JSON, $result->getPayload());
        self::assertEquals(self::QOS_LEVEL_AT_LEAST_ONCE, $result->getQosLevel());
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertFalse($flow->isFinished());
    }

    public function test_start_generates_publish_packet_with_qos_2(): void
    {
        $message = new DefaultMessage(
            self::TOPIC_TEST,
            self::PAYLOAD_EMPTY,
            self::QOS_LEVEL_EXACTLY_ONCE
        );

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        self::assertInstanceOf(PublishRequestPacket::class, $result);
        self::assertEquals(self::TOPIC_TEST, $result->getTopic());
        self::assertEquals(self::PAYLOAD_EMPTY, $result->getPayload());
        self::assertEquals(self::QOS_LEVEL_EXACTLY_ONCE, $result->getQosLevel());
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertFalse($flow->isFinished());
    }

    public function test_start_sets_retained_flag(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE, true);

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        self::assertTrue($result->isRetained());
        self::assertFalse($result->isDuplicate());
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

        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_PUBLISH)
            ->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $result = $flow->start();

        self::assertFalse($result->isRetained());
        self::assertTrue($result->isDuplicate());
    }

    public function test_accept_returns_false_for_qos_0(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();

        self::assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_puback_with_qos_1_and_correct_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        self::assertTrue($flow->accept($packet));
    }

    public function test_accept_returns_false_for_puback_with_qos_1_and_wrong_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER + 1);

        self::assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_false_for_puback_with_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishAckPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        self::assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_pubrec_with_qos_2_and_correct_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishReceivedPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        self::assertTrue($flow->accept($packet));
    }

    public function test_accept_returns_false_for_pubrec_with_qos_2_and_wrong_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishReceivedPacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER + 1);

        self::assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_false_for_pubcomp_before_pubrec_received(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);

        $packet = new PublishCompletePacket();
        $packet->setIdentifier(self::PACKET_IDENTIFIER);

        self::assertFalse($flow->accept($packet));
    }

    public function test_accept_returns_true_for_pubcomp_after_pubrec_received(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $pubrecPacket = new PublishReceivedPacket();
        $pubrecPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type) {
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

        self::assertTrue($flow->accept($pubcompPacket));
    }

    public function test_accept_returns_false_for_pubcomp_with_wrong_identifier_after_pubrec(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $pubrecPacket = new PublishReceivedPacket();
        $pubrecPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type) {
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

        self::assertFalse($flow->accept($publishCompletePacket));
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

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals($message, $flow->getResult());
    }

    public function test_next_returns_pubrel_for_pubrec_with_qos_2(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $pubrecPacket = new PublishReceivedPacket();
        $pubrecPacket->setIdentifier(self::PACKET_IDENTIFIER);

        $this->packetFactory
            ->method('build')
            ->willReturnCallback(
                function (int $type) {
                    if ($type === Packet::TYPE_PUBREL) {
                        return new PublishReleasePacket();
                    }

                    return new PublishRequestPacket();
                }
            );

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $flow->start();
        $result = $flow->next($pubrecPacket);

        self::assertInstanceOf(PublishReleasePacket::class, $result);
        self::assertEquals(self::PACKET_IDENTIFIER, $result->getIdentifier());
        self::assertFalse($flow->isFinished());
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
                function (int $type) {
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

        self::assertNull($result);
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertEquals($message, $flow->getResult());
    }

    public function test_qos_0_does_not_generate_packet_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_MOST_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $packet = $flow->start();
        self::assertNull($packet->getIdentifier());
    }

    public function test_qos_1_generates_packet_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_AT_LEAST_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $packet = $flow->start();
        self::assertNotNull($packet->getIdentifier());
    }

    public function test_qos_2_generates_packet_identifier(): void
    {
        $message = new DefaultMessage(self::TOPIC_TEST, self::PAYLOAD_SIMPLE, self::QOS_LEVEL_EXACTLY_ONCE);
        $this->packetFactory->method('build')->willReturn(new PublishRequestPacket());

        $flow = new OutgoingPublishFlow($this->packetFactory, $message, $this->identifierGenerator);
        $packet = $flow->start();
        self::assertNotNull($packet->getIdentifier());
    }
}
