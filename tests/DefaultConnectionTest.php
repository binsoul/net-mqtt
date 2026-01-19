<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultConnection;
use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Message;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DefaultConnectionTest extends TestCase
{
    public function test_has_sane_defaults(): void
    {
        $conn = new DefaultConnection();

        $this->assertSame('', $conn->getUsername());
        $this->assertSame('', $conn->getPassword());
        $this->assertSame('', $conn->getClientID());
        $this->assertNotInstanceOf(Message::class, $conn->getWill());
        $this->assertGreaterThan(0, $conn->getKeepAlive());
        $this->assertGreaterThan(0, $conn->getProtocol());
        $this->assertTrue($conn->isCleanSession());
    }

    public function test_returns_instance_with_different_credentials(): void
    {
        $original = new DefaultConnection('foo', 'bar');
        $clone = $original->withCredentials('username', 'password');

        $this->assertNotSame($clone, $original);
        $this->assertSame('username', $clone->getUsername());
        $this->assertSame('password', $clone->getPassword());
    }

    public function test_returns_instance_with_different_will(): void
    {
        $will = new DefaultMessage('topic');
        $original = new DefaultConnection();
        $clone = $original->withWill($will);

        $this->assertNotSame($clone, $original);
        $this->assertSame($will, $clone->getWill());
    }

    public function test_returns_instance_with_different_client_id(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withClientID('clientid');

        $this->assertNotSame($clone, $original);
        $this->assertSame('clientid', $clone->getClientID());
    }

    public function test_returns_instance_with_different_protocol(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withProtocol(3);

        $this->assertNotSame($clone, $original);
        $this->assertSame(3, $clone->getProtocol());
    }

    public function test_returns_instance_with_different_keepalive(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withKeepAlive(30);

        $this->assertNotSame($clone, $original);
        $this->assertSame(30, $clone->getKeepAlive());
    }

    public function test_throws_exception_if_protocol_too_high(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $original = new DefaultConnection();
        $original->withProtocol(5);
    }

    public function test_throws_exception_if_protocol_too_low(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $original = new DefaultConnection();
        $original->withProtocol(2);
    }

    public function test_throws_exception_if_keep_alive_too_high(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $original = new DefaultConnection();
        $original->withKeepAlive(0xFFFF + 1);
    }

    public function test_throws_exception_if_keep_alive_too_low(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $original = new DefaultConnection();
        $original->withKeepAlive(-1);
    }
}
