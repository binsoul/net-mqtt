<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SubscribeResponsePacketTest extends TestCase
{
    private function getDefaultData(): string
    {
        return "\x90\x03\x00\x00\x00";
    }

    public function test_getters_and_setters(): void
    {
        $packet = new SubscribeResponsePacket();
        $packet->setIdentifier(1);
        $this->assertEquals(1, $packet->getIdentifier());

        $packet->setReturnCodes([0, 128]);
        $this->assertEquals([0, 128], $packet->getReturnCodes());
    }

    public function test_cannot_set_negative_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeResponsePacket();
        $packet->setIdentifier(-1);
    }

    public function test_cannot_set_too_large_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeResponsePacket();
        $packet->setIdentifier(12345678);
    }

    public function test_cannot_set_invalid_return_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new SubscribeResponsePacket();
        $packet->setReturnCodes([0, 128, 256]);
    }

    public function test_knows_error_return_code(): void
    {
        $packet = new SubscribeResponsePacket();
        $this->assertFalse($packet->isError(0));
        $this->assertTrue($packet->isError(128));
    }

    public function test_returns_names(): void
    {
        $packet = new SubscribeResponsePacket();
        for ($i = 0; $i <= 128; $i++) {
            $this->assertNotEmpty($packet->getReturnCodeName($i));
        }
    }

    public function test_write(): void
    {
        $packet = new SubscribeResponsePacket();
        $packet->setIdentifier(0);
        $packet->setReturnCodes([0]);

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new SubscribeResponsePacket();
        $packet->read($stream);

        $this->assertEquals(Packet::TYPE_SUBACK, $packet->getPacketType());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new SubscribeResponsePacket();
        $packet->setIdentifier(0);
        $packet->setReturnCodes([0]);

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new SubscribeResponsePacket();
        $packet->read($stream);
        $this->assertEquals($this->getDefaultData(), $stream->getData());
    }
}
