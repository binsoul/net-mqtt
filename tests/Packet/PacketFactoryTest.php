<?php

namespace BinSoul\Test\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\UnknownPacketTypeException;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\Packet;

class PacketFactoryTest extends \PHPUnit_Framework_TestCase
{
    function test_default_packets()
    {
        $factory = new PacketFactory();

        $connectRequestPacket = $factory->build(Packet::TYPE_CONNECT);
        $this->assertInstanceOf(Packet\ConnectRequestPacket::class, $connectRequestPacket);

        $connectResponsePacket = $factory->build(Packet::TYPE_CONNACK);
        $this->assertInstanceOf(Packet\ConnectResponsePacket::class, $connectResponsePacket);

        $disconnectRequestPacket = $factory->build(Packet::TYPE_DISCONNECT);
        $this->assertInstanceOf(Packet\DisconnectRequestPacket::class, $disconnectRequestPacket);

        $pingRequestPacket = $factory->build(Packet::TYPE_PINGREQ);
        $this->assertInstanceOf(Packet\PingRequestPacket::class, $pingRequestPacket);

        $pingResponsePacket = $factory->build(Packet::TYPE_PINGRESP);
        $this->assertInstanceOf(Packet\PingResponsePacket::class, $pingResponsePacket);

        $publishAckPacket = $factory->build(Packet::TYPE_PUBACK);
        $this->assertInstanceOf(Packet\PublishAckPacket::class, $publishAckPacket);

        $publishCompletePacket = $factory->build(Packet::TYPE_PUBCOMP);
        $this->assertInstanceOf(Packet\PublishCompletePacket::class, $publishCompletePacket);

        $publishReceivedPacket = $factory->build(Packet::TYPE_PUBREC);
        $this->assertInstanceOf(Packet\PublishReceivedPacket::class, $publishReceivedPacket);

        $publishReleasePacket = $factory->build(Packet::TYPE_PUBREL);
        $this->assertInstanceOf(Packet\PublishReleasePacket::class, $publishReleasePacket);

        $publishRequestPacket = $factory->build(Packet::TYPE_PUBLISH);
        $this->assertInstanceOf(Packet\PublishRequestPacket::class, $publishRequestPacket);

        $subscribeRequestPacket = $factory->build(Packet::TYPE_SUBSCRIBE);
        $this->assertInstanceOf(Packet\SubscribeRequestPacket::class, $subscribeRequestPacket);

        $subscribeResponsePacket = $factory->build(Packet::TYPE_SUBACK);
        $this->assertInstanceOf(Packet\SubscribeResponsePacket::class, $subscribeResponsePacket);

        $unsubscribeRequestPacket = $factory->build(Packet::TYPE_UNSUBSCRIBE);
        $this->assertInstanceOf(Packet\UnsubscribeRequestPacket::class, $unsubscribeRequestPacket);

        $unsubscribeResponsePacket = $factory->build(Packet::TYPE_UNSUBACK);
        $this->assertInstanceOf(Packet\UnsubscribeResponsePacket::class, $unsubscribeResponsePacket);
    }

    function test_unknown_type()
    {
        $factory = new PacketFactory();

        $this->expectException(UnknownPacketTypeException::class);
        $factory->build('unknown');
    }

    function test_mapping_override()
    {
        $factory = new PacketFactory([
            Packet::TYPE_CONNECT => Packet\StrictConnectRequestPacket::class,
        ] + PacketFactory::getDefaultMapping());

        $connectRequestPacket = $factory->build(Packet::TYPE_CONNECT);
        $this->assertInstanceOf(Packet\StrictConnectRequestPacket::class, $connectRequestPacket);
    }
}
