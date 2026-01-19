<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\DisconnectRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use PHPUnit\Framework\TestCase;

final class DisconnectRequestPacketTest extends TestCase
{
    public function test_write(): void
    {
        $packet = new DisconnectRequestPacket();
        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertSame($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new DisconnectRequestPacket();
        $packet->read($stream);

        $this->assertSame(Packet::TYPE_DISCONNECT, $packet->getPacketType());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new DisconnectRequestPacket();

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new DisconnectRequestPacket();
        $packet->read($stream);
        $this->assertSame($this->getDefaultData(), $stream->getData());
    }

    private function getDefaultData(): string
    {
        return "\xe0\x00";
    }
}
