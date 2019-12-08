<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

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
}
