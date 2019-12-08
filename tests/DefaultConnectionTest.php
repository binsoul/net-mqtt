<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultConnection;
use BinSoul\Net\Mqtt\DefaultMessage;
use PHPUnit\Framework\TestCase;

class DefaultConnectionTest extends TestCase
{
    public function test_has_sane_defaults(): void
    {
        $conn = new DefaultConnection();

        self::assertEquals('', $conn->getUsername());
        self::assertEquals('', $conn->getPassword());
        self::assertEquals('', $conn->getClientID());
        self::assertNull($conn->getWill());
        self::assertGreaterThan(0, $conn->getKeepAlive());
        self::assertGreaterThan(0, $conn->getProtocol());
        self::assertTrue($conn->isCleanSession());
    }

    public function test_returns_instance_with_different_credentials(): void
    {
        $original = new DefaultConnection('foo', 'bar');
        $clone = $original->withCredentials('username', 'password');

        self::assertNotSame($clone, $original);
        self::assertEquals('username', $clone->getUsername());
        self::assertEquals('password', $clone->getPassword());
    }

    public function test_returns_instance_with_different_will(): void
    {
        $will = new DefaultMessage('topic');
        $original = new DefaultConnection();
        $clone = $original->withWill($will);

        self::assertNotSame($clone, $original);
        self::assertSame($will, $clone->getWill());
    }

    public function test_returns_instance_with_different_client_id(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withClientID('clientid');

        self::assertNotSame($clone, $original);
        self::assertEquals('clientid', $clone->getClientID());
    }

    public function test_returns_instance_with_different_protocol(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withProtocol(3);

        self::assertNotSame($clone, $original);
        self::assertEquals(3, $clone->getProtocol());
    }

    public function test_returns_instance_with_different_keepalive(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withKeepAlive(30);

        self::assertNotSame($clone, $original);
        self::assertEquals(30, $clone->getKeepAlive());
    }
}
