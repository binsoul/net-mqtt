<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultMessage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DefaultMessageTest extends TestCase
{
    public function test_returns_instance_with_different_topic(): void
    {
        $original = new DefaultMessage('foo', 'payload', 1, true, true);
        $clone = $original->withTopic('bar');

        self::assertNotEquals($original, $clone);
        self::assertEquals('bar', $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_different_payload(): void
    {
        $original = new DefaultMessage('topic', 'foo', 1, true, true);
        $clone = $original->withPayload('bar');

        self::assertNotEquals($original, $clone);
        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals('bar', $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_different_qos(): void
    {
        $original = new DefaultMessage('topic', 'foo', 1, true, true);
        $clone = $original->withQosLevel(2);

        self::assertNotEquals($original, $clone);
        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals(2, $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_retain_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, false, true);
        $clone = $original->retain();

        self::assertNotEquals($original, $clone);
        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_without_retain_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, true);
        $clone = $original->release();

        self::assertNotEquals($original, $clone);
        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertFalse($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_duplicate_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, false);
        $clone = $original->duplicate();

        self::assertNotEquals($original, $clone);
        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_without_duplicate_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, true);
        $clone = $original->original();

        self::assertNotEquals($original, $clone);
        self::assertEquals($original->getTopic(), $clone->getTopic());
        self::assertEquals($original->getPayload(), $clone->getPayload());
        self::assertEquals($original->getQosLevel(), $clone->getQosLevel());
        self::assertTrue($clone->isRetained());
        self::assertFalse($clone->isDuplicate());
    }

    public function test_negative_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DefaultMessage('topic', 'payload', -1, true, true);
    }

    public function test_too_large_qos_level(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DefaultMessage('topic', 'payload', 10, true, true);
    }
}
