<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow\OutgoingPingFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\PingRequestPacket;
use BinSoul\Net\Mqtt\Packet\PingResponsePacket;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\TestCase;

class OutgoingPingFlowTest extends TestCase
{
    private const string CODE_PING = 'ping';

    private PacketFactory $packetFactory;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);

        self::assertEquals(self::CODE_PING, $flow->getCode());
    }

    public function test_start_generates_ping_request_packet(): void
    {
        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_PINGREQ)
            ->willReturn(new PingRequestPacket());

        $flow = new OutgoingPingFlow($this->packetFactory);
        $result = $flow->start();

        self::assertInstanceOf(PingRequestPacket::class, $result);
        self::assertFalse($flow->isFinished());
        self::assertFalse($flow->isSuccess());
    }

    public function test_accept_returns_true_for_ping_response_packet(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);

        self::assertTrue($flow->accept(new PingResponsePacket()));
    }

    public function test_accept_returns_false_for_wrong_packet_type(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);

        self::assertFalse($flow->accept(new PublishAckPacket()));
    }

    public function test_next_completes_flow_successfully(): void
    {
        $flow = new OutgoingPingFlow($this->packetFactory);
        $result = $flow->next(new PingResponsePacket());

        self::assertNull($result);
        self::assertNull($flow->getResult());
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
    }
}
