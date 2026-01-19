<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Flow\OutgoingDisconnectFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\DisconnectRequestPacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class OutgoingDisconnectFlowTest extends TestCase
{
    private const string CODE_DISCONNECT = 'disconnect';

    private PacketFactory&Stub $packetFactory;

    private Connection&Stub $connection;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createStub(PacketFactory::class);
        $this->connection = $this->createStub(Connection::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);

        $this->assertSame(self::CODE_DISCONNECT, $flow->getCode());
    }

    public function test_start_generates_disconnect_request_packet(): void
    {
        $packetFactory = $this->createMock(PacketFactory::class);
        $packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_DISCONNECT)
            ->willReturn(new DisconnectRequestPacket());

        $flow = new OutgoingDisconnectFlow($packetFactory, $this->connection);
        $result = $flow->start();

        $this->assertInstanceOf(DisconnectRequestPacket::class, $result);
    }

    public function test_start_returns_connection_as_result(): void
    {
        $this->packetFactory->method('build')->willReturn(new DisconnectRequestPacket());

        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);
        $flow->start();

        $this->assertSame($this->connection, $flow->getResult());
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);
        $this->assertFalse($flow->accept(new DisconnectRequestPacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);
        $this->assertNotInstanceOf(Packet::class, $flow->next(new DisconnectRequestPacket()));
    }
}
