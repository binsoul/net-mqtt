<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use PHPUnit\Framework\TestCase;

class PingRequestPacketTest extends TestCase
{
    private function getDefaultData(): string
    {
        return "\xc0\x00";
    }

    public function test_write(): void
    {
        $packet = new PingRequestPacket();
        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new PingRequestPacket();
        $packet->read($stream);

        $this->assertEquals(Packet::TYPE_PINGREQ, $packet->getPacketType());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new PingRequestPacket();

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new PingRequestPacket();
        $packet->read($stream);
        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }
}
