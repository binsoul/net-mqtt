<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\DisconnectRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use PHPUnit\Framework\TestCase;

class DisconnectRequestPacketTest extends TestCase
{
    private function getDefaultData(): string
    {
        return "\xe0\x00";
    }

    public function test_write(): void
    {
        $packet = new DisconnectRequestPacket();
        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new DisconnectRequestPacket();
        $packet->read($stream);

        $this->assertEquals(Packet::TYPE_DISCONNECT, $packet->getPacketType());
    }
}
