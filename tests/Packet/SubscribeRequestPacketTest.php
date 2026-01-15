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
    public function test_getters_and_setters(): void
    {
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(1);
        self::assertEquals(1, $packet->getIdentifier());

        $packet->setFilters(['#', 'test/a/b/c']);
        self::assertEquals(['#', 'test/a/b/c'], $packet->getFilters());

        $packet->setQosLevels([1, 2]);
        self::assertEquals([1, 2], $packet->getQosLevels());
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

    public function test_cannot_set_empty_filter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setFilters(['test/a/b/c', '']);
    }

    public function test_cannot_set_too_large_filter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setFilters([str_repeat('x', 0x10000)]);
    }

    public function test_cannot_set_empty_filters_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setFilters([]);
    }

    public function test_cannot_set_invalid_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeRequestPacket();
        $packet->setQosLevels([10]);
    }

    public function test_uses_default_qos_level(): void
    {
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(1);
        $packet->setFilters(['first' => '#', 'second' => 'test/a/b/c']);
        $packet->setQosLevels(['second' => 1]);

        $stream = new PacketStream();
        $packet->write($stream);

        self::assertEquals($this->getDefaultDataMultipleFilters(), $stream->getData());
    }

    public function test_write(): void
    {
        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(1);
        $packet->setFilters(['#']);
        $packet->setQosLevels([0]);

        $stream = new PacketStream();
        $packet->write($stream);

        self::assertEquals($this->getDefaultDataSingleFilter(), $stream->getData());

        $packet = new SubscribeRequestPacket();
        $packet->setIdentifier(1);
        $packet->setFilters(['#', 'test/a/b/c']);
        $packet->setQosLevels([0, 1]);

        $stream = new PacketStream();
        $packet->write($stream);

        self::assertEquals($this->getDefaultDataMultipleFilters(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultDataSingleFilter());
        $packet = new SubscribeRequestPacket();
        $packet->read($stream);

        self::assertEquals(Packet::TYPE_SUBSCRIBE, $packet->getPacketType());
        self::assertEquals(['#'], $packet->getFilters());
        self::assertEquals([0], $packet->getQosLevels());

        $stream = new PacketStream($this->getDefaultDataMultipleFilters());
        $packet = new SubscribeRequestPacket();
        $packet->read($stream);

        self::assertEquals(Packet::TYPE_SUBSCRIBE, $packet->getPacketType());
        self::assertEquals(['#', 'test/a/b/c'], $packet->getFilters());
        self::assertEquals([0, 1], $packet->getQosLevels());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packetWrite = new SubscribeRequestPacket();
        $packetWrite->setIdentifier(1);
        $packetWrite->setFilters(['#', 'test/a/b/c']);
        $packetWrite->setQosLevels([0, 1]);

        $stream = new PacketStream();
        $packetWrite->write($stream);
        $stream->setPosition(0);

        $packetRead = new SubscribeRequestPacket();
        $packetRead->read($stream);

        self::assertEquals(['#', 'test/a/b/c'], $packetRead->getFilters());
        self::assertEquals([0, 1], $packetRead->getQosLevels());
    }

    private function getDefaultDataSingleFilter(): string
    {
        return "\x82\x06\x00\x01\x00\x01#\x00";
    }

    private function getDefaultDataMultipleFilters(): string
    {
        return "\x82\x13\x00\x01\x00\x01#\x00\x00\x0atest/a/b/c\x01";
    }
}
