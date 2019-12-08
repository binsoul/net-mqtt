<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\PacketStream;
use PHPUnit\Framework\TestCase;

class ConnectResponsePacketTest extends TestCase
{
    private function createDefaultPacket(): ConnectResponsePacket
    {
        $packet = new ConnectResponsePacket();
        $packet->setReturnCode(0);
        $packet->setSessionPresent(false);

        return $packet;
    }

    private function getDefaultData(): string
    {
        return "\x20\x02\x00\x00";
    }

    public function test_getters_and_setters(): void
    {
        $packet = new ConnectResponsePacket();
        $packet->setReturnCode(0);
        $this->assertEquals(0, $packet->getReturnCode());
        $this->assertTrue($packet->isSuccess());
        $this->assertFalse($packet->isError());

        $packet = new ConnectResponsePacket();
        $packet->setReturnCode(1);
        $this->assertEquals(1, $packet->getReturnCode());
        $this->assertFalse($packet->isSuccess());
        $this->assertTrue($packet->isError());

        $packet = new ConnectResponsePacket();
        $packet->setSessionPresent(false);
        $this->assertFalse($packet->isSessionPresent());

        $packet->setSessionPresent(true);
        $this->assertTrue($packet->isSessionPresent());
    }

    public function test_returns_error_names(): void
    {
        $packet = new ConnectResponsePacket();
        for ($i = 0; $i < 10; $i++) {
            $packet->setReturnCode($i);
            $this->assertNotEmpty($packet->getErrorName());
        }
    }

    public function test_write(): void
    {
        $packet = $this->createDefaultPacket();
        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new ConnectResponsePacket();
        $packet->read($stream);

        $this->assertEquals(0, $packet->getReturnCode());
        $this->assertFalse($packet->isSessionPresent());
        $this->assertTrue($packet->isSuccess());
        $this->assertFalse($packet->isError());
        $this->assertEquals('Connection accepted', $packet->getErrorName());
    }

    public function test_remaining_length(): void
    {
        $stream = new PacketStream("\x20\x02\x00\x00");
        $packet = new ConnectResponsePacket();
        $packet->read($stream);

        $this->assertEquals(2, $packet->getRemainingPacketLength());
    }

    public function test_packet_without_remaining_length(): void
    {
        $this->expectException(MalformedPacketException::class);

        $stream = new PacketStream("\x20\x00\x00\x00");
        $packet = new ConnectResponsePacket();
        $packet->read($stream);
    }

    public function test_packet_huge_remaining_length(): void
    {
        $this->expectException(MalformedPacketException::class);

        $stream = new PacketStream("\x20\xff\xff\xff\xff\xff\x00\x00");
        $packet = new ConnectResponsePacket();
        $packet->read($stream);
    }

    public function test_packet_with_wrong_type(): void
    {
        $this->expectException(MalformedPacketException::class);

        $stream = new PacketStream("\xf0\x02\x00\x00");
        $packet = new ConnectResponsePacket();
        $packet->read($stream);
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = $this->createDefaultPacket();

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new ConnectResponsePacket();
        $packet->read($stream);
        $this->assertEquals(0, $packet->getReturnCode());
        $this->assertFalse($packet->isSessionPresent());
    }
}
