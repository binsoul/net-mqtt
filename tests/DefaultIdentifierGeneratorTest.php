<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultIdentifierGenerator;
use PHPUnit\Framework\TestCase;

class DefaultIdentifierGeneratorTest extends TestCase
{
    public function test_generates_packet_id(): void
    {
        $generator = new DefaultIdentifierGenerator();

        $previous = '';
        for ($i = 1; $i < 10; ++$i) {
            $current = $generator->generatePacketIdentifier();
            $this->assertLessThanOrEqual(0xFFFF, $current);
            if ($i === 1) {
                $previous = $generator->generatePacketIdentifier();
                $this->assertLessThanOrEqual(0xFFFF, $previous);
            }

            $this->assertNotEquals($current, $previous);
            $previous = $current;
        }
    }

    public function test_wraps_packet_ids(): void
    {
        $generator = new DefaultIdentifierGenerator();

        for ($i = 1; $i <= 0xFFFF; ++$i) {
            $generator->generatePacketIdentifier();
        }

        $this->assertEquals(1, $generator->generatePacketIdentifier());
    }

    public function test_generates_client_id(): void
    {
        $generator = new DefaultIdentifierGenerator();

        $previous = '';
        for ($i = 1; $i < 10; ++$i) {
            $current = $generator->generateClientIdentifier();
            $this->assertLessThanOrEqual(23, strlen($current));
            if ($i === 1) {
                $previous = $generator->generateClientIdentifier();
                $this->assertLessThanOrEqual(23, strlen($previous));
            }

            $this->assertNotEquals($current, $previous);
            $previous = $current;
        }
    }
}
