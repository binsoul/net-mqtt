<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\SubscribeRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SubscribeRequestPacketTest extends TestCase
{
    private function getDefaultData(): string
    {
        return "\x82\x06\x00\x01\x00\x01#\x00";
    }

    public function test_getters_and_setters(): void
    {
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(1);
        $this->assertEquals(1, $packet->getIdentifier());

        $packet->setTopic('#');
        $this->assertEquals('#', $packet->getTopic());

        $packet->setQosLevel(1);
        $this->assertEquals(1, $packet->getQosLevel());
    }

    public function test_cannot_set_negative_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(-1);
    }

    public function test_cannot_set_too_large_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(12345678);
    }

    public function test_cannot_set_empty_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setTopic('');
    }

    public function test_cannot_set_too_large_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setTopic(str_repeat('x', 0x10000));
    }

    public function test_cannot_set_invalid_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setQosLevel(10);
    }

    public function test_write(): void
    {
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(1);
        $packet->setTopic('#');
        $packet->setQosLevel(0);

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new SubscribeRequestPacket();
        $packet->read($stream);

        $this->assertEquals(Packet::TYPE_SUBSCRIBE, $packet->getPacketType());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(1);
        $packet->setTopic('#');
        $packet->setQosLevel(0);

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new SubscribeRequestPacket();
        $packet->read($stream);
        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }
}
