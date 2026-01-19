<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Flow\OutgoingConnectFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectRequestPacket;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\Packet\PublishAckPacket;
use BinSoul\Net\Mqtt\PacketFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OutgoingConnectFlowTest extends TestCase
{
    private const string CLIENT_ID_AUTO = 'auto-generated-id';

    private const string CLIENT_ID_TEST = 'test-client-123';

    private const string CODE_CONNECT = 'connect';

    private const string ERROR_MESSAGE_UNAUTHORIZED = 'Not authorized';

    private const int KEEP_ALIVE_DEFAULT = 60;

    private const int KEEP_ALIVE_LONG = 300;

    private const string PASSWORD_TEST = 'secret';

    private const int PROTOCOL_LEVEL_3 = 3;

    private const int PROTOCOL_LEVEL_4 = 4;

    private const int QOS_LEVEL_AT_LEAST_ONCE = 1;

    private const int RETURN_CODE_ERROR = 5;

    private const int RETURN_CODE_SUCCESS = 0;

    private const string USERNAME_TEST = 'testuser';

    private const string WILL_PAYLOAD = 'offline';

    private const string WILL_TOPIC = 'status/client';

    private PacketFactory&MockObject $packetFactory;

    private Connection&MockObject $connection;

    private ClientIdentifierGenerator&MockObject $clientIdGenerator;

    protected function setUp(): void
    {
        $this->packetFactory = $this->createMock(PacketFactory::class);
        $this->connection = $this->createMock(Connection::class);
        $this->clientIdGenerator = $this->createMock(ClientIdentifierGenerator::class);
    }

    public function test_returns_correct_code(): void
    {
        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);

        $this->assertSame(self::CODE_CONNECT, $flow->getCode());
    }

    public function test_generates_client_id_when_empty(): void
    {
        $clonedConnection = $this->createMock(Connection::class);
        $clonedConnection->method('getClientID')->willReturn(self::CLIENT_ID_AUTO);

        $this->connection->method('getClientID')->willReturn('');
        $this->connection->method('withClientID')->willReturn($clonedConnection);

        $this->clientIdGenerator
            ->expects($this->once())
            ->method('generateClientIdentifier')
            ->willReturn(self::CLIENT_ID_AUTO);

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $packet = new ConnectResponsePacket();
        $packet->setReturnCode(self::RETURN_CODE_SUCCESS);

        $flow->next($packet);

        $this->assertEquals(self::CLIENT_ID_AUTO, $flow->getResult()->getClientID());
    }

    public function test_does_not_generate_client_id_when_provided(): void
    {
        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);

        $this->clientIdGenerator
            ->expects($this->never())
            ->method('generateClientIdentifier');

        new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
    }

    public function test_start_generates_connect_request_packet(): void
    {
        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);
        $this->connection->method('getProtocol')->willReturn(self::PROTOCOL_LEVEL_4);
        $this->connection->method('getKeepAlive')->willReturn(self::KEEP_ALIVE_DEFAULT);
        $this->connection->method('isCleanSession')->willReturn(true);
        $this->connection->method('getUsername')->willReturn(self::USERNAME_TEST);
        $this->connection->method('getPassword')->willReturn(self::PASSWORD_TEST);
        $this->connection->method('getWill')->willReturn(null);

        $this->packetFactory
            ->expects($this->once())
            ->method('build')
            ->with(Packet::TYPE_CONNECT)
            ->willReturn(new ConnectRequestPacket());

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $result = $flow->start();

        $this->assertInstanceOf(ConnectRequestPacket::class, $result);
        $this->assertSame(self::PROTOCOL_LEVEL_4, $result->getProtocolLevel());
        $this->assertSame(self::KEEP_ALIVE_DEFAULT, $result->getKeepAlive());
        $this->assertSame(self::CLIENT_ID_TEST, $result->getClientID());
        $this->assertTrue($result->isCleanSession());
        $this->assertSame(self::USERNAME_TEST, $result->getUsername());
        $this->assertSame(self::PASSWORD_TEST, $result->getPassword());
    }

    public function test_start_sets_clean_session_to_false(): void
    {
        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);
        $this->connection->method('getProtocol')->willReturn(self::PROTOCOL_LEVEL_4);
        $this->connection->method('getKeepAlive')->willReturn(self::KEEP_ALIVE_DEFAULT);
        $this->connection->method('isCleanSession')->willReturn(false);
        $this->connection->method('getUsername')->willReturn('');
        $this->connection->method('getPassword')->willReturn('');
        $this->connection->method('getWill')->willReturn(null);

        $this->packetFactory->method('build')->willReturn(new ConnectRequestPacket());

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $result = $flow->start();
        $this->assertInstanceOf(ConnectRequestPacket::class, $result);

        $this->assertFalse($result->isCleanSession());
    }

    public function test_start_configures_will_message(): void
    {
        $will = new DefaultMessage(self::WILL_TOPIC, self::WILL_PAYLOAD, self::QOS_LEVEL_AT_LEAST_ONCE, true);

        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);
        $this->connection->method('getProtocol')->willReturn(self::PROTOCOL_LEVEL_4);
        $this->connection->method('getKeepAlive')->willReturn(self::KEEP_ALIVE_DEFAULT);
        $this->connection->method('isCleanSession')->willReturn(true);
        $this->connection->method('getUsername')->willReturn('');
        $this->connection->method('getPassword')->willReturn('');
        $this->connection->method('getWill')->willReturn($will);

        $this->packetFactory->method('build')->willReturn(new ConnectRequestPacket());

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $result = $flow->start();
        $this->assertInstanceOf(ConnectRequestPacket::class, $result);

        $this->assertTrue($result->hasWill());
        $this->assertSame(self::WILL_TOPIC, $result->getWillTopic());
        $this->assertSame(self::WILL_PAYLOAD, $result->getWillMessage());
        $this->assertSame(self::QOS_LEVEL_AT_LEAST_ONCE, $result->getWillQosLevel());
        $this->assertTrue($result->isWillRetained());
    }

    public function test_start_with_different_protocol_level(): void
    {
        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);
        $this->connection->method('getProtocol')->willReturn(self::PROTOCOL_LEVEL_3);
        $this->connection->method('getKeepAlive')->willReturn(self::KEEP_ALIVE_LONG);
        $this->connection->method('isCleanSession')->willReturn(true);
        $this->connection->method('getUsername')->willReturn('');
        $this->connection->method('getPassword')->willReturn('');
        $this->connection->method('getWill')->willReturn(null);

        $this->packetFactory->method('build')->willReturn(new ConnectRequestPacket());

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $result = $flow->start();
        $this->assertInstanceOf(ConnectRequestPacket::class, $result);

        $this->assertSame(self::PROTOCOL_LEVEL_3, $result->getProtocolLevel());
        $this->assertSame(self::KEEP_ALIVE_LONG, $result->getKeepAlive());
    }

    public function test_flow_is_not_finished_after_start(): void
    {
        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);
        $this->connection->method('getProtocol')->willReturn(self::PROTOCOL_LEVEL_4);
        $this->connection->method('getKeepAlive')->willReturn(self::KEEP_ALIVE_DEFAULT);
        $this->connection->method('isCleanSession')->willReturn(true);
        $this->connection->method('getUsername')->willReturn('');
        $this->connection->method('getPassword')->willReturn('');
        $this->connection->method('getWill')->willReturn(null);

        $this->packetFactory->method('build')->willReturn(new ConnectRequestPacket());

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $flow->start();

        $this->assertFalse($flow->isFinished());
        $this->assertFalse($flow->isSuccess());
    }

    public function test_accept_returns_true_for_connack_packet(): void
    {
        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);

        $this->assertTrue($flow->accept(new ConnectResponsePacket()));
    }

    public function test_accept_returns_false_for_wrong_packet_type(): void
    {
        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);

        $this->assertFalse($flow->accept(new PublishAckPacket()));
    }

    public function test_next_succeeds_flow_on_successful_response(): void
    {
        $this->connection->method('getClientID')->willReturn(self::CLIENT_ID_TEST);

        $packet = new ConnectResponsePacket();
        $packet->setReturnCode(self::RETURN_CODE_SUCCESS);

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $result = $flow->next($packet);

        $this->assertNotInstanceOf(Packet::class, $result);
        $this->assertTrue($flow->isFinished());
        $this->assertTrue($flow->isSuccess());
        $this->assertSame($this->connection, $flow->getResult());
    }

    public function test_next_fails_flow_on_error_response(): void
    {
        $packet = new ConnectResponsePacket();
        $packet->setReturnCode(self::RETURN_CODE_ERROR);

        $flow = new OutgoingConnectFlow($this->packetFactory, $this->connection, $this->clientIdGenerator);
        $result = $flow->next($packet);

        $this->assertNotInstanceOf(Packet::class, $result);
        $this->assertTrue($flow->isFinished());
        $this->assertFalse($flow->isSuccess());
        $this->assertSame(self::ERROR_MESSAGE_UNAUTHORIZED, $flow->getErrorMessage());
        $this->assertNull($flow->getResult());
    }
}
