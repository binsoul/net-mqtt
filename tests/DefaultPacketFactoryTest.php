<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultPacketFactory;
use BinSoul\Net\Mqtt\Exception\UnknownPacketTypeException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectRequestPacket;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\Packet\DisconnectRequestPacket;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;
use BinSoul\Net\Mqtt\Packet\PingResponsePacket;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\Packet\PublishCompletePacket;
use BinSoul\Net\Mqtt\Packet\PublishReceivedPacket;
use BinSoul\Net\Mqtt\Packet\PublishReleasePacket;
use BinSoul\Net\Mqtt\Packet\PublishRequestPacket;
use BinSoul\Net\Mqtt\Packet\SubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\SubscribeResponsePacket;
use BinSoul\Net\Mqtt\Packet\UnsubscribeRequestPacket;
use BinSoul\Net\Mqtt\Packet\UnsubscribeResponsePacket;
use PHPUnit\Framework\TestCase;

final class DefaultPacketFactoryTest extends TestCase
{
    private DefaultPacketFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DefaultPacketFactory();
    }

    public function test_builds_connect_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_CONNECT);

        $this->assertInstanceOf(ConnectRequestPacket::class, $packet);
    }

    public function test_builds_connack_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_CONNACK);

        $this->assertInstanceOf(ConnectResponsePacket::class, $packet);
    }

    public function test_builds_publish_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_PUBLISH);

        $this->assertInstanceOf(PublishRequestPacket::class, $packet);
    }

    public function test_builds_puback_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_PUBACK);

        $this->assertInstanceOf(PublishAckPacket::class, $packet);
    }

    public function test_builds_pubrec_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_PUBREC);

        $this->assertInstanceOf(PublishReceivedPacket::class, $packet);
    }

    public function test_builds_pubrel_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_PUBREL);

        $this->assertInstanceOf(PublishReleasePacket::class, $packet);
    }

    public function test_builds_pubcomp_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_PUBCOMP);

        $this->assertInstanceOf(PublishCompletePacket::class, $packet);
    }

    public function test_builds_subscribe_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_SUBSCRIBE);

        $this->assertInstanceOf(SubscribeRequestPacket::class, $packet);
    }

    public function test_builds_suback_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_SUBACK);

        $this->assertInstanceOf(SubscribeResponsePacket::class, $packet);
    }

    public function test_builds_unsubscribe_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_UNSUBSCRIBE);

        $this->assertInstanceOf(UnsubscribeRequestPacket::class, $packet);
    }

    public function test_builds_unsuback_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_UNSUBACK);

        $this->assertInstanceOf(UnsubscribeResponsePacket::class, $packet);
    }

    public function test_builds_pingreq_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_PINGREQ);

        $this->assertInstanceOf(PingRequestPacket::class, $packet);
    }

    public function test_builds_pingresp_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_PINGRESP);

        $this->assertInstanceOf(PingResponsePacket::class, $packet);
    }

    public function test_builds_disconnect_packet(): void
    {
        $packet = $this->factory->build(Packet::TYPE_DISCONNECT);

        $this->assertInstanceOf(DisconnectRequestPacket::class, $packet);
    }

    public function test_throws_exception_for_unknown_packet_type(): void
    {
        $this->expectException(UnknownPacketTypeException::class);

        $this->factory->build(99);
    }

    public function test_throws_exception_for_zero_packet_type(): void
    {
        $this->expectException(UnknownPacketTypeException::class);

        $this->factory->build(0);
    }

    public function test_throws_exception_for_negative_packet_type(): void
    {
        $this->expectException(UnknownPacketTypeException::class);

        $this->factory->build(-1);
    }
}
