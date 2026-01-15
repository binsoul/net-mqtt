<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet\ConnectRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConnectRequestPacketTest extends TestCase
{
    public function test_generates_client_id_on_write(): void
    {
        $packet = new ConnectRequestPacket();
        self::assertEquals('', $packet->getClientID());
        $packet->write(new PacketStream());
        self::assertNotEquals('', $packet->getClientID());
    }

    public function test_minimal_packet(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        self::assertEquals(1, $packet->getPacketType());
        self::assertEquals(0, $packet->getPacketFlags());
        self::assertEquals(4, $packet->getProtocolLevel());
        self::assertTrue($packet->isCleanSession());
        self::assertEquals(10, $packet->getKeepAlive());
        self::assertEquals('foobar', $packet->getClientID());
        self::assertEquals('', $packet->getUsername());
        self::assertEquals('', $packet->getPassword());
        self::assertFalse($packet->hasWill());
        self::assertFalse($packet->isWillRetained());
        self::assertEquals('', $packet->getWillMessage());
        self::assertEquals('', $packet->getWillTopic());
        self::assertEquals(0, $packet->getWillQosLevel());

        self::assertEquals($this->getDefaultData(), (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(4);

        self::assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_packet_with_protocol_level_3(): void
    {
        $data = "\x10\x14\x00\x06MQIsdp\x03\x02\x00\x0a\x00\x06foobar";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        self::assertEquals(3, $packet->getProtocolLevel());

        self::assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(3);

        self::assertEquals($data, (string) $packet);
    }

    public function test_packet_with_invalid_protocol_level(): void
    {
        $this->expectException(MalformedPacketException::class);
        $data = "\x10\x14\x00\x06MQIsdp\x05\x02\x00\x0a\x00\x06foobar";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);
    }

    public function test_with_existing_session(): void
    {
        $data = "\x10\x12\x00\x04MQTT\x04\x00\x00\x0a\x00\x06foobar";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        self::assertFalse($packet->isCleanSession());

        self::assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setCleanSession(false);

        self::assertEquals($data, (string) $packet);
    }

    public function test_packet_with_username(): void
    {
        $data = "\x10\x1d\x00\x04MQTT\x04B\x00\x0a\x00\x06foobar\x00\x09üsername";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        self::assertTrue($packet->hasUsername());
        self::assertEquals('üsername', $packet->getUsername());

        self::assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setUsername('üsername');

        self::assertTrue($packet->hasUsername());
        self::assertEquals('üsername', $packet->getUsername());
        self::assertEquals($data, (string) $packet);

        $packet->setUsername('');
        self::assertFalse($packet->hasUsername());
        self::assertEquals('', $packet->getUsername());
        self::assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_cannot_set_too_large_username(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = $this->createDefaultPacket();
        $packet->setUsername(str_repeat('x', 0x10000));
    }

    public function test_cannot_set_invalid_utf8(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = $this->createDefaultPacket();
        $packet->setUsername("\xfe\xfe\xff\xff");
    }

    public function test_cannot_set_out_of_range_utf8(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = $this->createDefaultPacket();
        $packet->setUsername("abc\x00\x00");
    }

    public function test_packet_with_password(): void
    {
        $data = "\x10\x1e\x00\x04MQTT\x04\x82\x00\x0a\x00\x06foobar\x00\x0ap\xc3\xa4ssw\xc3\xb6rd";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        self::assertEquals('pässwörd', $packet->getPassword());

        self::assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setPassword('pässwörd');

        self::assertEquals($data, (string) $packet);

        $packet->setPassword('');
        self::assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_cannot_set_invalid_password(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = $this->createDefaultPacket();
        $packet->setPassword(str_repeat('x', 0x10000));
    }

    public function test_packet_with_will(): void
    {
        $data = "\x10\"\x00\x04MQTT\x046\x00\x0a\x00\x06foobar\x00\x05topic\x00\x07message";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        self::assertTrue($packet->hasWill());
        self::assertTrue($packet->isWillRetained());
        self::assertEquals('message', $packet->getWillMessage());
        self::assertEquals('topic', $packet->getWillTopic());
        self::assertEquals(2, $packet->getWillQosLevel());

        self::assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', 'message', 2, true);

        self::assertEquals($data, (string) $packet);

        $packet->setWill('topic', 'message', 2, false);
        self::assertFalse($packet->isWillRetained());

        $packet->removeWill();
        self::assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_packet_without_will_but_will_qos(): void
    {
        $this->expectException(MalformedPacketException::class);
        $data = "\x10\"\x00\x04MQTT\x04\x18\x00\x0a\x00\x06foobar\x00\x05topic\x00\x07message";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);
    }

    public function test_packet_without_will_but_will_retained(): void
    {
        $this->expectException(MalformedPacketException::class);
        $data = "\x10\"\x00\x04MQTT\x04\x20\x00\x0a\x00\x06foobar\x00\x05topic\x00\x07message";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);
    }

    public function test_packet_without_remaining_length(): void
    {
        $this->expectException(MalformedPacketException::class);
        $data = "\x10\x00\x00\x04MQTT\x04\x02\x00\x0a\x00\x06foobar";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);
    }

    public function test_cannot_set_invalid_protocol_level(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(2);
    }

    public function test_cannot_set_invalid_keepalive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setKeepAlive(100000);
    }

    public function test_cannot_set_invalid_will_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('', 'message', 0, false);
    }

    public function test_cannot_set_invalid_will_message(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', '', 0, false);
    }

    public function test_cannot_set_invalid_will_qos(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', 'message', 10, false);
    }

    public function test_can_read_what_it_writes(): void
    {
        $packet = $this->createDefaultPacket();

        $stream = new PacketStream();
        $packet->write($stream);
        $stream->setPosition(0);

        $packet = new ConnectRequestPacket();
        $packet->read($stream);
        self::assertEquals(4, $packet->getProtocolLevel());
        self::assertTrue($packet->isCleanSession());
        self::assertEquals(10, $packet->getKeepAlive());
        self::assertEquals('foobar', $packet->getClientID());
    }

    private function createDefaultPacket(): ConnectRequestPacket
    {
        $packet = new ConnectRequestPacket();
        $packet->setProtocolLevel(4);
        $packet->setCleanSession(true);
        $packet->setKeepAlive(10);
        $packet->setClientID('foobar');

        return $packet;
    }

    private function getDefaultData(): string
    {
        return "\x10\x12\x00\x04MQTT\x04\x02\x00\x0a\x00\x06foobar";
    }
}
