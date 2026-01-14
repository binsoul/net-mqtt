<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Flow\OutgoingDisconnectFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\DisconnectRequestPacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\TestCase;

class OutgoingDisconnectFlowTest extends TestCase
{
    private const CODE_DISCONNECT = 'disconnect';

    private PacketFactory $packetFactory;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
        $this->connection = $this->createMock(Connection::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);

        self::assertEquals(self::CODE_DISCONNECT, $flow->getCode());
    }

    public function test_start_generates_disconnect_request_packet(): void
    {
        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_DISCONNECT)
            ->willReturn(new DisconnectRequestPacket());

        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);
        $result = $flow->start();

        self::assertInstanceOf(DisconnectRequestPacket::class, $result);
    }

    public function test_start_returns_connection_as_result(): void
    {
        $this->packetFactory->method('build')->willReturn(new DisconnectRequestPacket());

        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);
        $flow->start();

        self::assertSame($this->connection, $flow->getResult());
        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);
        self::assertFalse($flow->accept(new DisconnectRequestPacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new OutgoingDisconnectFlow($this->packetFactory, $this->connection);
        self::assertNull($flow->next(new DisconnectRequestPacket()));
    }
}
