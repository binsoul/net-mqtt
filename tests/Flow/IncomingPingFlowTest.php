<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow\IncomingPingFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;
use BinSoul\Net\Mqtt\Packet\PingResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class IncomingPingFlowTest extends TestCase
{
    private const string CODE_PONG = 'pong';

    private PacketFactory&Stub $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createStub(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new IncomingPingFlow($this->packetFactory);

        $this->assertSame(self::CODE_PONG, $flow->getCode());
    }

    public function test_start_generates_ping_response_packet(): void
    {
        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_PINGRESP)
            ->willReturn(new PingResponsePacket());

        $flow = new IncomingPingFlow($packetFactory);
        $result = $flow->start();

        $this->assertInstanceOf(PingResponsePacket::class, $result);
    }

    public function test_start_immediately_succeeds_flow(): void
    {
        $this->packetFactory->method('build')->willReturn(new PingResponsePacket());

        $flow = new IncomingPingFlow($this->packetFactory);
        $flow->start();

        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertNull($flow->getResult());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new IncomingPingFlow($this->packetFactory);
        $this->assertFalse($flow->accept(new PingRequestPacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new IncomingPingFlow($this->packetFactory);
        $this->assertNotInstanceOf(Packet::class, $flow->next(new PingRequestPacket()));
    }
}
