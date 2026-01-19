<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet\ConnectRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ConnectRequestPacketTest extends TestCase
{
    public function test_generates_client_id_on_write(): void
    {
        $packet = new ConnectRequestPacket();
        $this->assertSame('', $packet->getClientID());
        $packet->write(new PacketStream());
        $this->assertNotSame('', $packet->getClientID());
    }

    public function test_allows_empty_client_id(): void
    {
        $packet = new ConnectRequestPacket();
        $packet->setClientID('');
        $this->assertSame('', $packet->getClientID());

        $packet->write(new PacketStream());
        $this->assertSame('', $packet->getClientID());
    }

    public function test_sets_clean_session_for_empty_client_id(): void
    {
        $packet = new ConnectRequestPacket();
        $packet->setClientID('test');
        $packet->setCleanSession(false);
        $this->assertSame('test', $packet->getClientID());
        $this->assertFalse($packet->isCleanSession());

        $packet->setClientID('');
        $this->assertSame('', $packet->getClientID());
        $this->assertTrue($packet->isCleanSession());

        $packet->setCleanSession(false);
        $this->assertTrue($packet->isCleanSession());
    }

    public function test_minimal_packet(): void
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertSame(1, $packet->getPacketType());
        $this->assertSame(0, $packet->getPacketFlags());
        $this->assertSame(4, $packet->getProtocolLevel());
        $this->assertTrue($packet->isCleanSession());
        $this->assertSame(10, $packet->getKeepAlive());
        $this->assertSame('foobar', $packet->getClientID());
        $this->assertSame('', $packet->getUsername());
        $this->assertSame('', $packet->getPassword());
        $this->assertFalse($packet->hasWill());
        $this->assertFalse($packet->isWillRetained());
        $this->assertSame('', $packet->getWillMessage());
        $this->assertSame('', $packet->getWillTopic());
        $this->assertSame(0, $packet->getWillQosLevel());

        $this->assertSame($this->getDefaultData(), (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(4);

        $this->assertSame($this->getDefaultData(), (string) $packet);
    }

    public function test_packet_with_protocol_level_3(): void
    {
        $data = "\x10\x14\x00\x06MQIsdp\x03\x02\x00\x0a\x00\x06foobar";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertSame(3, $packet->getProtocolLevel());

        $this->assertSame($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(3);

        $this->assertSame($data, (string) $packet);
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

        $this->assertFalse($packet->isCleanSession());

        $this->assertSame($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setCleanSession(false);

        $this->assertSame($data, (string) $packet);
    }

    public function test_packet_with_username(): void
    {
        $data = "\x10\x1d\x00\x04MQTT\x04B\x00\x0a\x00\x06foobar\x00\x09üsername";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertTrue($packet->hasUsername());
        $this->assertSame('üsername', $packet->getUsername());

        $this->assertSame($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setUsername('üsername');

        $this->assertTrue($packet->hasUsername());
        $this->assertSame('üsername', $packet->getUsername());
        $this->assertSame($data, (string) $packet);

        $packet->setUsername('');
        $this->assertFalse($packet->hasUsername());
        $this->assertSame('', $packet->getUsername());
        $this->assertSame($this->getDefaultData(), (string) $packet);
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

        $this->assertSame('pässwörd', $packet->getPassword());

        $this->assertSame($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setPassword('pässwörd');

        $this->assertSame($data, (string) $packet);

        $packet->setPassword('');
        $this->assertSame($this->getDefaultData(), (string) $packet);
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

        $this->assertTrue($packet->hasWill());
        $this->assertTrue($packet->isWillRetained());
        $this->assertSame('message', $packet->getWillMessage());
        $this->assertSame('topic', $packet->getWillTopic());
        $this->assertSame(2, $packet->getWillQosLevel());

        $this->assertSame($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', 'message', 2, true);

        $this->assertSame($data, (string) $packet);

        $packet->setWill('topic', 'message', 2, false);
        $this->assertFalse($packet->isWillRetained());

        $packet->removeWill();
        $this->assertSame($this->getDefaultData(), (string) $packet);
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

    public function test_cannot_set_wildcard_will_topic(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('#', 'message', 0, false);
    }

    public function test_cannot_set_invalid_will_message(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', str_repeat('a', 0xFFFF + 1), 0, false);
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
        $this->assertSame(4, $packet->getProtocolLevel());
        $this->assertTrue($packet->isCleanSession());
        $this->assertSame(10, $packet->getKeepAlive());
        $this->assertSame('foobar', $packet->getClientID());
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
