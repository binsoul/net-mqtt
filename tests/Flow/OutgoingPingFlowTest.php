<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow\OutgoingPingFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;
use BinSoul\Net\Mqtt\Packet\PingResponsePacket;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class OutgoingPingFlowTest extends TestCase
{
    private const string CODE_PING = 'ping';

    private PacketFactory&Stub $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createStub(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);

        $this->assertSame(self::CODE_PING, $flow->getCode());
    }

    public function test_start_generates_ping_request_packet(): void
    {
        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PINGREQ)
            ->willReturn(new PingRequestPacket());

        $flow = new OutgoingPingFlow($packetFactory);
        $result = $flow->start();

        $this->assertInstanceOf(PingRequestPacket::class, $result);
        $this->assertFalse($flow->isFinished());
        $this->assertFalse($flow->isSuccess());
    }

    public function test_accept_returns_true_for_ping_response_packet(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);

        $this->assertTrue($flow->accept(new PingResponsePacket()));
    }

    public function test_accept_returns_false_for_wrong_packet_type(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);

        $this->assertFalse($flow->accept(new PublishAckPacket()));
    }

    public function test_next_completes_flow_successfully(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);
        $result = $flow->next(new PingResponsePacket());

        $this->assertNotInstanceOf(Packet::class, $result);
        $this->assertNull($flow->getResult());
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
    }
}
