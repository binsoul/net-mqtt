<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet\PublishRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PublishRequestPacketTest extends TestCase
{
    public function test_getters_and_setters(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(1);
        self::assertEquals(1, $packet->getIdentifier());

        $packet->setTopic('topic');
        self::assertEquals('topic', $packet->getTopic());

        $packet->setPayload('message');
        self::assertEquals('message', $packet->getPayload());

        $packet->setQosLevel(1);
        self::assertEquals(1, $packet->getQosLevel());

        $packet->setDuplicate(true);
        self::assertTrue($packet->isDuplicate());
        $packet->setDuplicate(false);
        self::assertFalse($packet->isDuplicate());

        $packet->setRetained(true);
        self::assertTrue($packet->isRetained());
        $packet->setRetained(false);
        self::assertFalse($packet->isRetained());
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
        $packet->setIdentifier(0);
        $packet->setTopic('topic');
        $packet->setQosLevel(0);
        $packet->setDuplicate(false);
        $packet->setRetained(false);
        $packet->setPayload('message');

        $stream = new PacketStream();
        $packet->write($stream);

        self::assertEquals($this->getDefaultData(), $stream->getData());
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

        self::assertEquals($this->getQosLevel1Data(), $stream->getData());
    }

    public function test_write_without_identifier(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setTopic('topic');
        $packet->setQosLevel(1);
        $packet->setDuplicate(false);
        $packet->setRetained(false);
        $packet->setPayload('message');

        self::assertNull($packet->getIdentifier());

        $stream = new PacketStream();
        $packet->write($stream);

        self::assertNotNull($packet->getIdentifier());
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

        self::assertGreaterThan(1024 * 1024, $stream->length());
    }

    public function test_read_qos_level0(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new PublishRequestPacket();
        $packet->read($stream);

        self::assertNull($packet->getIdentifier());
        self::assertEquals('topic', $packet->getTopic());
        self::assertEquals(0, $packet->getQosLevel());
        self::assertFalse($packet->isDuplicate());
        self::assertFalse($packet->isRetained());
        self::assertEquals('message', $packet->getPayload());
    }

    public function test_read_qos_level1(): void
    {
        $stream = new PacketStream($this->getQosLevel1Data());
        $packet = new PublishRequestPacket();
        $packet->read($stream);

        self::assertEquals(1, $packet->getIdentifier());
        self::assertEquals('topic', $packet->getTopic());
        self::assertEquals(1, $packet->getQosLevel());
        self::assertFalse($packet->isDuplicate());
        self::assertFalse($packet->isRetained());
        self::assertEquals('message', $packet->getPayload());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new PublishRequestPacket();
        $packet->setIdentifier(0);
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
        self::assertEquals($this->getDefaultData(), $stream->getData());
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
