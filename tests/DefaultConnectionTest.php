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

        $this->assertEquals('', $conn->getUsername());
        $this->assertEquals('', $conn->getPassword());
        $this->assertEquals('', $conn->getClientID());
        $this->assertNull($conn->getWill());
        $this->assertGreaterThan(0, $conn->getKeepAlive());
        $this->assertGreaterThan(0, $conn->getProtocol());
        $this->assertTrue($conn->isCleanSession());
    }

    public function test_returns_instance_with_different_credentials(): void
    {
        $original = new DefaultConnection('foo', 'bar');
        $clone = $original->withCredentials('username', 'password');

        $this->assertNotSame($clone, $original);
        $this->assertEquals('username', $clone->getUsername());
        $this->assertEquals('password', $clone->getPassword());
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
        $this->assertEquals('clientid', $clone->getClientID());
    }

    public function test_returns_instance_with_different_protocol(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withProtocol(3);

        $this->assertNotSame($clone, $original);
        $this->assertEquals(3, $clone->getProtocol());
    }

    public function test_returns_instance_with_different_keepalive(): void
    {
        $original = new DefaultConnection();
        $clone = $original->withKeepAlive(30);

        $this->assertNotSame($clone, $original);
        $this->assertEquals(30, $clone->getKeepAlive());
    }
}
