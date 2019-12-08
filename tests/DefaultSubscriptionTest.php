<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultSubscription;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DefaultSubscriptionTest extends TestCase
{
    public function test_returns_instance_with_different_filter(): void
    {
        $original = new DefaultSubscription('foo', 1);
        $clone = $original->withFilter('bar');

        $this->assertNotEquals($original, $clone);
        $this->assertEquals('bar', $clone->getFilter());
        $this->assertEquals($original->getQosLevel(), $clone->getQosLevel());
    }

    public function test_returns_instance_with_different_qos(): void
    {
        $original = new DefaultSubscription('foo', 1);
        $clone = $original->withQosLevel(2);

        $this->assertNotEquals($original, $clone);
        $this->assertEquals($original->getFilter(), $clone->getFilter());
        $this->assertEquals(2, $clone->getQosLevel());
    }

    public function test_negative_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DefaultSubscription('topic', -1);
    }

    public function test_too_large_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DefaultSubscription('topic', 10);
    }
}
