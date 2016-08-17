<?php

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultSubscription;

class DefaultSubscriptionTest extends \PHPUnit_Framework_TestCase
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
