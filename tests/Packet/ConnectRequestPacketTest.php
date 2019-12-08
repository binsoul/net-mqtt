<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet\ConnectRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConnectRequestPacketTest extends TestCase
{
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

    public function test_generates_client_id_on_write()
    {
        $packet = new ConnectRequestPacket();
        $this->assertEquals('', $packet->getClientID());
        $packet->write(new PacketStream());
        $this->assertNotEquals('', $packet->getClientID());
    }

    public function test_minimal_packet()
    {
        $stream = new PacketStream($this->getDefaultData());
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertEquals(1, $packet->getPacketType());
        $this->assertEquals(0, $packet->getPacketFlags());
        $this->assertEquals(4, $packet->getProtocolLevel());
        $this->assertTrue($packet->isCleanSession());
        $this->assertEquals(10, $packet->getKeepAlive());
        $this->assertEquals('foobar', $packet->getClientID());
        $this->assertEquals('', $packet->getUsername());
        $this->assertEquals('', $packet->getPassword());
        $this->assertFalse($packet->hasWill());
        $this->assertFalse($packet->isWillRetained());
        $this->assertEquals('', $packet->getWillMessage());
        $this->assertEquals('', $packet->getWillTopic());
        $this->assertEquals(0, $packet->getWillQosLevel());

        $this->assertEquals($this->getDefaultData(), (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(4);

        $this->assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_packet_with_protocol_level_3()
    {
        $data = "\x10\x14\x00\x06MQIsdp\x03\x02\x00\x0a\x00\x06foobar";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertEquals(3, $packet->getProtocolLevel());

        $this->assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(3);

        $this->assertEquals($data, (string) $packet);
    }

    public function test_with_existing_session()
    {
        $data = "\x10\x12\x00\x04MQTT\x04\x00\x00\x0a\x00\x06foobar";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertFalse($packet->isCleanSession());

        $this->assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setCleanSession(false);

        $this->assertEquals($data, (string) $packet);
    }

    public function test_packet_with_username()
    {
        $data = "\x10\x1d\x00\x04MQTT\x04B\x00\x0a\x00\x06foobar\x00\x09üsername";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertTrue($packet->hasUsername());
        $this->assertEquals('üsername', $packet->getUsername());

        $this->assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setUsername('üsername');

        $this->assertTrue($packet->hasUsername());
        $this->assertEquals('üsername', $packet->getUsername());
        $this->assertEquals($data, (string) $packet);

        $packet->setUsername('');
        $this->assertFalse($packet->hasUsername());
        $this->assertEquals('', $packet->getUsername());
        $this->assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_cannot_set_invalid_username()
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = $this->createDefaultPacket();
        $packet->setUsername(str_repeat('x', 0x10000));
    }

    public function test_packet_with_password()
    {
        $data = "\x10\x1e\x00\x04MQTT\x04\x82\x00\x0a\x00\x06foobar\x00\x0ap\xc3\xa4ssw\xc3\xb6rd";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertEquals('pässwörd', $packet->getPassword());

        $this->assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setPassword('pässwörd');

        $this->assertEquals($data, (string) $packet);

        $packet->setPassword('');
        $this->assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_cannot_set_invalid_password()
    {
        $this->expectException(InvalidArgumentException::class);
        $packet = $this->createDefaultPacket();
        $packet->setPassword(str_repeat('x', 0x10000));
    }

    public function test_packet_with_will()
    {
        $data = "\x10\"\x00\x04MQTT\x046\x00\x0a\x00\x06foobar\x00\x05topic\x00\x07message";
        $stream = new PacketStream($data);
        $packet = new ConnectRequestPacket();
        $packet->read($stream);

        $this->assertTrue($packet->hasWill());
        $this->assertTrue($packet->isWillRetained());
        $this->assertEquals('message', $packet->getWillMessage());
        $this->assertEquals('topic', $packet->getWillTopic());
        $this->assertEquals(2, $packet->getWillQosLevel());

        $this->assertEquals($data, (string) $packet);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', 'message', 2, true);

        $this->assertEquals($data, (string) $packet);

        $packet->setWill('topic', 'message', 2, false);
        $this->assertFalse($packet->isWillRetained());

        $packet->removeWill();
        $this->assertEquals($this->getDefaultData(), (string) $packet);
    }

    public function test_cannot_invalid_protocol_level()
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setProtocolLevel(2);
    }

    public function test_cannot_set_invalid_keepalive()
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setKeepAlive(100000);
    }

    public function test_cannot_set_invalid_will_topic()
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('', 'message', 0, false);
    }

    public function test_cannot_set_invalid_will_message()
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', '', 0, false);
    }

    public function test_cannot_set_invalid_will_qos()
    {
        $this->expectException(InvalidArgumentException::class);

        $packet = $this->createDefaultPacket();
        $packet->setWill('topic', 'message', 10, false);
    }
}
