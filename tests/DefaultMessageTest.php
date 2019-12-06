<?php

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultMessage;
use PHPUnit\Framework\TestCase;

class DefaultMessageTest extends TestCase
{
    public function test_returns_instance_with_different_topic()
    {
        $original = new DefaultMessage('foo', 'payload', 1, true, true);
        $clone = $original->withTopic('bar');

        self::assertEquals('bar', $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_different_payload()
    {
        $original = new DefaultMessage('topic', 'foo', 1, true, true);
        $clone = $original->withPayload('bar');

        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals('bar', $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_different_qos()
    {
        $original = new DefaultMessage('topic', 'foo', 1, true, true);
        $clone = $original->withQosLevel(2);

        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals(2, $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_retain_flag()
    {
        $original = new DefaultMessage('topic', 'payload', 1, false, true);
        $clone = $original->retain();

        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_without_retain_flag()
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, true);
        $clone = $original->release();

        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertFalse($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_duplicate_flag()
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, false);
        $clone = $original->duplicate();

        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_without_duplicate_flag()
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, true);
        $clone = $original->original();

        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertFalse($clone->isDuplicate());
    }
}
