<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PublishReleasePacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PublishReleasePacketTest extends TestCase
{
    public function test_getters_and_setters(): void
    {
        $packet = new PublishReleasePacket();
        $packet->setIdentifier(1);
        $this->assertSame(1, $packet->getIdentifier());
    }

    public function test_cannot_set_negative_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishReleasePacket();
        $packet->setIdentifier(-1);
    }

    public function test_cannot_set_too_large_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = new PublishReleasePacket();
        $packet->setIdentifier(12345678);
    }

    public function test_write(): void
    {
        $packet = new PublishReleasePacket();
        $packet->setIdentifier(1);

        $stream = new PacketStream();
        $packet->write($stream);

        $this->assertSame($this->getDefaultData(), $stream->getData());
    }

    public function test_read(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new PublishReleasePacket();
        $packet->read($stream);

        $this->assertSame(Packet::TYPE_PUBREL, $packet->getPacketType());
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = new PublishReleasePacket();
        $packet->setIdentifier(1);

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new PublishReleasePacket();
        $packet->read($stream);
        $this->assertSame($this->getDefaultData(), $stream->getData());
    }

    public function test_throws_exception_for_wrong_header_flags(): void
    {
        $this->expectException(MalformedPacketException::class);
        $data = "\x60\x02\x00\x01"; // Header 0x60 (Reserved bits is 0x2 for PUBREL, here 0x0)
        $stream = new PacketStream($data);
        $packet = new PublishReleasePacket();
        $packet->read($stream);
    }

    private function getDefaultData(): string
    {
        return "\x62\x02\x00\x01";
    }
}
