<?php

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet\StrictConnectRequestPacket;
use BinSoul\Net\Mqtt\PacketStream;

class StrictConnectRequestPacketTest extends \PHPUnit_Framework_TestCase
{
    private function createDefaultPacket()
    {
        $packet = new StrictConnectRequestPacket();
        $packet->setProtocolLevel(4);
        $packet->setCleanSession(true);
        $packet->setKeepAlive(10);
        $packet->setClientID('foobar');

        return $packet;
    }

    /**
     * @expectedException \BinSoul\Net\Mqtt\Exception\MalformedPacketException
     */
    public function test_too_long_client_id_in_packet()
    {
        $stream = new PacketStream("\x10\x12\x00\x04MQTT\x04\x02\x00\x0a\x00\x19foobarfoobarfoobarfoobarfoobar");
        $packet = new StrictConnectRequestPacket();
        $packet->read($stream);
    }

    /**
     * @expectedException \BinSoul\Net\Mqtt\Exception\MalformedPacketException
     */
    public function test_invalid_client_id_in_packet()
    {
        $stream = new PacketStream("\x10\x12\x00\x04MQTT\x04\x02\x00\x0a\x00\x07foobar!");
        $packet = new StrictConnectRequestPacket();
        $packet->read($stream);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_cannot_set_too_long_client_id()
    {
        $packet = $this->createDefaultPacket();
        $packet->setClientID('123456789123456789123456789');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_cannot_set_invalid_client_id()
    {
        $packet = $this->createDefaultPacket();
        $packet->setClientID('!fööbär!');
    }
}
