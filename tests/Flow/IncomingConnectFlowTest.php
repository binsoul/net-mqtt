<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Flow\IncomingConnectFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\TestCase;

class IncomingConnectFlowTest extends TestCase
{
    private const string CODE_CONNECT = 'connect';

    private const int RETURN_CODE_ERROR = 5;

    private const int RETURN_CODE_SUCCESS = 0;

    private PacketFactory $packetFactory;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
        $this->connection = $this->createMock(Connection::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_SUCCESS, false);

        self::assertEquals(self::CODE_CONNECT, $flow->getCode());
    }

    public function test_start_generates_connack_packet_with_success(): void
    {
        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_CONNACK)
            ->willReturn(new ConnectResponsePacket());

        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_SUCCESS, false);
        $result = $flow->start();

        self::assertInstanceOf(ConnectResponsePacket::class, $result);
        self::assertEquals(self::RETURN_CODE_SUCCESS, $result->getReturnCode());
        self::assertFalse($result->isSessionPresent());
    }

    public function test_start_generates_connack_packet_with_error(): void
    {
        $this->packetFactory
            ->expects(self::once())
            ->method('build')
            ->with(Packet::TYPE_CONNACK)
            ->willReturn(new ConnectResponsePacket());

        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_ERROR, false);
        $result = $flow->start();

        self::assertInstanceOf(ConnectResponsePacket::class, $result);
        self::assertEquals(self::RETURN_CODE_ERROR, $result->getReturnCode());
        self::assertFalse($result->isSessionPresent());
    }

    public function test_start_sets_session_present_to_true(): void
    {
        $this->packetFactory->method('build')->willReturn(new ConnectResponsePacket());

        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_SUCCESS, true);
        $result = $flow->start();

        self::assertTrue($result->isSessionPresent());
    }

    public function test_start_sets_session_present_to_false(): void
    {
        $this->packetFactory->method('build')->willReturn(new ConnectResponsePacket());

        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_SUCCESS, false);
        $result = $flow->start();

        self::assertFalse($result->isSessionPresent());
    }

    public function test_start_immediately_succeeds_flow(): void
    {
        $this->packetFactory->method('build')->willReturn(new ConnectResponsePacket());

        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_SUCCESS, false);
        $flow->start();

        self::assertTrue($flow->isFinished());
        self::assertTrue($flow->isSuccess());
        self::assertSame($this->connection, $flow->getResult());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_SUCCESS, false);
        self::assertFalse($flow->accept(new ConnectResponsePacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new IncomingConnectFlow($this->packetFactory, $this->connection, self::RETURN_CODE_SUCCESS, false);
        self::assertNull($flow->next(new ConnectResponsePacket()));
    }
}
