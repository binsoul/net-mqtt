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

        $this->assertNotEquals($original, $clone);
        $this->assertEquals('bar', $clone->getTopic());
        $this->assertEquals($original->getPayload(), $clone->getPayload());
        $this->assertEquals($original->getQosLevel(), $clone->getQosLevel());
        $this->assertTrue($clone->isRetained());
        $this->assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_different_payload(): void
    {
        $original = new DefaultMessage('topic', 'foo', 1, true, true);
        $clone = $original->withPayload('bar');

        $this->assertNotEquals($original, $clone);
        $this->assertEquals($original->getTopic(), $clone->getTopic());
        $this->assertEquals('bar', $clone->getPayload());
        $this->assertEquals($original->getQosLevel(), $clone->getQosLevel());
        $this->assertTrue($clone->isRetained());
        $this->assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_different_qos(): void
    {
        $original = new DefaultMessage('topic', 'foo', 1, true, true);
        $clone = $original->withQosLevel(2);

        $this->assertNotEquals($original, $clone);
        $this->assertEquals($original->getTopic(), $clone->getTopic());
        $this->assertEquals($original->getPayload(), $clone->getPayload());
        $this->assertEquals(2, $clone->getQosLevel());
        $this->assertTrue($clone->isRetained());
        $this->assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_retain_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, false, true);
        $clone = $original->retain();

        $this->assertNotEquals($original, $clone);
        $this->assertEquals($original->getTopic(), $clone->getTopic());
        $this->assertEquals($original->getPayload(), $clone->getPayload());
        $this->assertEquals($original->getQosLevel(), $clone->getQosLevel());
        $this->assertTrue($clone->isRetained());
        $this->assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_without_retain_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, true);
        $clone = $original->release();

        $this->assertNotEquals($original, $clone);
        $this->assertEquals($original->getTopic(), $clone->getTopic());
        $this->assertEquals($original->getPayload(), $clone->getPayload());
        $this->assertEquals($original->getQosLevel(), $clone->getQosLevel());
        $this->assertFalse($clone->isRetained());
        $this->assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_with_duplicate_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, false);
        $clone = $original->duplicate();

        $this->assertNotEquals($original, $clone);
        $this->assertEquals($original->getTopic(), $clone->getTopic());
        $this->assertEquals($original->getPayload(), $clone->getPayload());
        $this->assertEquals($original->getQosLevel(), $clone->getQosLevel());
        $this->assertTrue($clone->isRetained());
        $this->assertTrue($clone->isDuplicate());
    }

    public function test_returns_instance_without_duplicate_flag(): void
    {
        $original = new DefaultMessage('topic', 'payload', 1, true, true);
        $clone = $original->original();

        $this->assertNotEquals($original, $clone);
        $this->assertEquals($original->getTopic(), $clone->getTopic());
        $this->assertEquals($original->getPayload(), $clone->getPayload());
        $this->assertEquals($original->getQosLevel(), $clone->getQosLevel());
        $this->assertTrue($clone->isRetained());
        $this->assertFalse($clone->isDuplicate());
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
