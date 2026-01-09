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

        for ($i = 1; $i < 10; $i++) {
            $current = $generator->generatePacketIdentifier();
            self::assertLessThanOrEqual(0xFFFF, $current);

            if ($i === 1) {
                $previous = $generator->generatePacketIdentifier();
                self::assertLessThanOrEqual(0xFFFF, $previous);
            }

            self::assertNotEquals($current, $previous);
            $previous = $current;
        }
    }

    public function test_wraps_packet_ids(): void
    {
        $generator = new DefaultIdentifierGenerator();

        for ($i = 1; $i <= 0xFFFF; $i++) {
            $generator->generatePacketIdentifier();
        }

        self::assertEquals(1, $generator->generatePacketIdentifier());
    }

    public function test_generates_client_id(): void
    {
        $generator = new DefaultIdentifierGenerator();

        $previous = '';

        for ($i = 1; $i < 10; $i++) {
            $current = $generator->generateClientIdentifier();
            self::assertLessThanOrEqual(23, strlen($current));

            if ($i === 1) {
                $previous = $generator->generateClientIdentifier();
                self::assertLessThanOrEqual(23, strlen($previous));
            }

            self::assertNotEquals($current, $previous);
            $previous = $current;
        }
    }
}
