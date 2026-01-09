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

        self::assertNotEquals($original, $clone);
        self::assertEquals('bar', $clone->getFilter());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
    }

    public function test_returns_instance_with_different_qos(): void
    {
        $original = new DefaultSubscription('foo', 1);
        $clone = $original->withQosLevel(2);

        self::assertNotEquals($original, $clone);
        self::assertEquals($original->getFilter(), $clone->getFilter());
        self::assertEquals(2, $clone->getQosLevel());
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
