<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet\PublishRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PublishRequestPacketTest extends TestCase
{
    public function test_getters_and_setters(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(1);
        $this->assertSame(1, $packet->getIdentifier());

        $packet->setTopic('topic');
        $this->assertSame('topic', $packet->getTopic());

        $packet->setPayload('message');
        $this->assertSame('message', $packet->getPayload());

        $packet->setQosLevel(1);
        $this->assertSame(1, $packet->getQosLevel());

        $packet->setDuplicate(true);
        $this->assertTrue($packet->isDuplicate());
        $packet->setDuplicate(false);
        $this->assertFalse($packet->isDuplicate());

        $packet->setRetained(true);
        $this->assertTrue($packet->isRetained());
        $packet->setRetained(false);
        $this->assertFalse($packet->isRetained());
    }

    public function test_cannot_set_negative_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(-1);
    }

    public function test_cannot_set_too_large_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(12345678);
    }

    public function test_cannot_set_empty_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setTopic('');
    }

    public function test_cannot_set_too_large_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setTopic(str_repeat('x', 0x10000));
    }

    public function test_cannot_set_single_level_wildcard_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setTopic('topic/+');
    }

    public function test_cannot_set_multi_level_wildcard_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setTopic('#');
    }

    public function test_cannot_set_negative_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setQosLevel(-1);
    }

    public function test_cannot_set_too_large_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishRequestPacket();
        $packet->setQosLevel(3);
    }

    public function test_write_qos_level0(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(1);
        $packet->setTopic('topic');
        $packet->setQosLevel(0);
        $packet->setDuplicate(false);
        $packet->setRetained(false);
        $packet->setPayload('message');

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertSame($this->getDefaultData(), $stream->getData());
    }

    public function test_write_qos_level1(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(1);
        $packet->setTopic('topic');
        $packet->setQosLevel(1);
        $packet->setDuplicate(false);
        $packet->setRetained(false);
        $packet->setPayload('message');

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertSame($this->getQosLevel1Data(), $stream->getData());
    }

    public function test_write_without_identifier(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setTopic('topic');
        $packet->setQosLevel(1);
        $packet->setDuplicate(false);
        $packet->setRetained(false);
        $packet->setPayload('message');

        $this->assertNull($packet->getIdentifier());

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertNotNull($packet->getIdentifier());
    }

    public function test_write_large_payload(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(10);
        $packet->setTopic('topic');
        $packet->setQosLevel(0);
        $packet->setDuplicate(false);
        $packet->setRetained(false);
        $packet->setPayload(str_repeat('x', 1024 * 1024));

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertGreaterThan(1024 * 1024, $stream->length());
    }

    public function test_read_qos_level0(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new PublishRequestPacket();
        $packet->read($stream);

        $this->assertNull($packet->getIdentifier());
        $this->assertSame('topic', $packet->getTopic());
        $this->assertSame(0, $packet->getQosLevel());
        $this->assertFalse($packet->isDuplicate());
        $this->assertFalse($packet->isRetained());
        $this->assertSame('message', $packet->getPayload());
    }

    public function test_read_qos_level1(): void
    {
        $stream = new PacketStream($this->getQosLevel1Data());
        $packet = new PublishRequestPacket();
        $packet->read($stream);

        $this->assertSame(1, $packet->getIdentifier());
        $this->assertSame('topic', $packet->getTopic());
        $this->assertSame(1, $packet->getQosLevel());
        $this->assertFalse($packet->isDuplicate());
        $this->assertFalse($packet->isRetained());
        $this->assertSame('message', $packet->getPayload());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(1);
        $packet->setTopic('topic');
        $packet->setQosLevel(0);
        $packet->setDuplicate(false);
        $packet->setRetained(false);
        $packet->setPayload('message');

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new PublishRequestPacket();
        $packet->read($stream);
        $this->assertSame($this->getDefaultData(), $stream->getData());
    }

    public function test_corrects_dup_flag_at_qos_0_on_write(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setTopic('topic');
        $packet->setQosLevel(0);
        $packet->setDuplicate(true);

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertFalse($packet->isDuplicate());

        $stream->setPosition(0);
        $readPacket = new PublishRequestPacket();
        $readPacket->read($stream);

        $this->assertFalse($readPacket->isDuplicate());
    }

    public function test_throws_exception_for_dup_flag_at_qos_0(): void
    {
        $this->expectException(MalformedPacketException::class);
        $data = "\x38\x0e\x00\x05topicmessage"; // Header 0x38 (DUP=1, QoS=0)
        $stream = new PacketStream($data);
        $packet = new PublishRequestPacket();
        $packet->read($stream);
    }

    public function test_throws_exception_for_qos_3(): void
    {
        $this->expectException(MalformedPacketException::class);
        $data = "\x36\x0e\x00\x05topicmessage"; // Header 0x36 (QoS=3)
        $stream = new PacketStream($data);
        $packet = new PublishRequestPacket();
        $packet->read($stream);
    }

    private function getDefaultData(): string
    {
        return "\x30\x0e\x00\x05topicmessage";
    }

    private function getQosLevel1Data(): string
    {
        return "\x32\x10\x00\x05topic\x00\x01message";
    }
}
