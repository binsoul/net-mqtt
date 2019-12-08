<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\UnsubscribeRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UnsubscribeRequestPacketTest extends TestCase
{
    private function getDefaultData(): string
    {
        return "\xa2\x05\x00\x01\x00\x01#";
    }

    public function test_getters_and_setters(): void
    {
        $packet = new UnsubscribeRequestPacket();
        $packet->setIdentifier(1);
        $this->assertEquals(1, $packet->getIdentifier());

        $packet->setTopics(['#']);
        $this->assertEquals(['#'], $packet->getTopics());
    }

    public function test_cannot_set_negative_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new UnsubscribeRequestPacket();
        $packet->setIdentifier(-1);
    }

    public function test_cannot_set_too_large_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new UnsubscribeRequestPacket();
        $packet->setIdentifier(12345678);
    }

    public function test_cannot_set_empty_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new UnsubscribeRequestPacket();
        $packet->setTopics(['']);
    }

    public function test_cannot_set_too_large_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new UnsubscribeRequestPacket();
        $packet->setTopics([str_repeat('x', 0x10000)]);
    }

    public function test_write(): void
    {
        $packet = new UnsubscribeRequestPacket();
        $packet->setIdentifier(1);
        $packet->setTopics(['#']);

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new UnsubscribeRequestPacket();
        $packet->read($stream);

        $this->assertEquals(Packet::TYPE_UNSUBSCRIBE, $packet->getPacketType());
    }

    public function test_read_multiple_topics(): void
    {
        $stream = new PacketStream("\xa2\x09\x00\x01\x00\x01#\x00\x02##");
        $packet = new UnsubscribeRequestPacket();
        $packet->read($stream);

        $this->assertEquals(Packet::TYPE_UNSUBSCRIBE, $packet->getPacketType());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new UnsubscribeRequestPacket();
        $packet->setIdentifier(1);
        $packet->setTopics(['#']);

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new UnsubscribeRequestPacket();
        $packet->read($stream);
        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }
}
