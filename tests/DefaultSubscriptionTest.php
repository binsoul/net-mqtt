<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultSubscription;
use PHPUnit\Framework\TestCase;

class DefaultSubscriptionTest extends TestCase
{
    public function test_returns_instance_with_different_filter()
    {
        $original = new DefaultSubscription('foo', 1);
        $clone = $original->withFilter('bar');

        self::assertEquals('bar', $clone->getFilter());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
    }

    public function test_returns_instance_with_different_qos()
    {
        $original = new DefaultSubscription('foo', 1);
        $clone = $original->withQosLevel(2);

        self::assertEquals($original->getFilter(), $clone->getFilter());
        self::assertEquals(2, $clone->getQosLevel());
    }
}
