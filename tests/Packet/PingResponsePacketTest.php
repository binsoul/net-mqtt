<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingResponsePacket;
use BinSoul\Net\Mqtt\PacketStream;
use PHPUnit\Framework\TestCase;

class PingResponsePacketTest extends TestCase
{
    private function getDefaultData(): string
    {
        return "\xd0\x00";
    }

    public function test_write(): void
    {
        $packet = new PingResponsePacket();
        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new PingResponsePacket();
        $packet->read($stream);

        $this->assertEquals(Packet::TYPE_PINGRESP, $packet->getPacketType());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new PingResponsePacket();

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new PingResponsePacket();
        $packet->read($stream);
        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }
}
