<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow\IncomingPingFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;
use BinSoul\Net\Mqtt\Packet\PingResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\TestCase;

class IncomingPingFlowTest extends TestCase
{
    private const CODE_PONG = 'pong';

    private PacketFactory $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new IncomingPingFlow($this->packetFactory);

        self::assertEquals(self::CODE_PONG, $flow->getCode());
    }

    public function test_start_generates_ping_response_packet(): void
    {
        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_PINGRESP)
            ->willReturn(new PingResponsePacket());

        $flow = new IncomingPingFlow($this->packetFactory);
        $result = $flow->start();

        self::assertInstanceOf(PingResponsePacket::class, $result);
    }

    public function test_start_immediately_succeeds_flow(): void
    {
        $this->packetFactory->method('build')->willReturn(new PingResponsePacket());

        $flow = new IncomingPingFlow($this->packetFactory);
        $flow->start();

        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertNull($flow->getResult());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new IncomingPingFlow($this->packetFactory);
        self::assertFalse($flow->accept(new PingRequestPacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new IncomingPingFlow($this->packetFactory);
        self::assertNull($flow->next(new PingRequestPacket()));
    }
}
