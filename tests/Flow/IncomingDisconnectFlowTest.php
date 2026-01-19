<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Flow\IncomingDisconnectFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class IncomingDisconnectFlowTest extends TestCase
{
    private const string CODE_DISCONNECT = 'disconnect';

    private PacketFactory&MockObject $packetFactory;

    private Connection&MockObject $connection;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
        $this->connection = $this->createMock(Connection::class);
    }

    public function test_returns_correct_code(): void
    {
        $flow = new IncomingDisconnectFlow($this->packetFactory, $this->connection);

        $this->assertSame(self::CODE_DISCONNECT, $flow->getCode());
    }

    public function test_start_returns_null(): void
    {
        $flow = new IncomingDisconnectFlow($this->packetFactory, $this->connection);
        $result = $flow->start();

        $this->assertNotInstanceOf(Packet::class, $result);
    }

    public function test_start_immediately_succeeds_flow(): void
    {
        $flow = new IncomingDisconnectFlow($this->packetFactory, $this->connection);
        $flow->start();

        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertSame($this->connection, $flow->getResult());
    }

    public function test_accept_returns_false(): void
    {
        $flow = new IncomingDisconnectFlow($this->packetFactory, $this->connection);
        $this->assertFalse($flow->accept(new ConnectResponsePacket()));
    }

    public function test_next_returns_null(): void
    {
        $flow = new IncomingDisconnectFlow($this->packetFactory, $this->connection);
        $this->assertNotInstanceOf(Packet::class, $flow->next(new ConnectResponsePacket()));
    }
}
