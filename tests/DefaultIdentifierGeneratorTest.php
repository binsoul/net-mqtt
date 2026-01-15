<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Mock random_bytes function for tests.
 */
function random_bytes(int $length): string
{
    return \BinSoul\Test\Net\Mqtt\DefaultIdentifierGeneratorTest::randomBytes($length);
}

function hex2bin(string $string)
{
    return \BinSoul\Test\Net\Mqtt\DefaultIdentifierGeneratorTest::hex2bin($string);
}

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultIdentifierGenerator;
use PHPUnit\Framework\TestCase;

class DefaultIdentifierGeneratorTest extends TestCase
{
    private static $randomBytesFails = false;

    private static $hex2binFails = false;

    public function test_generates_packet_id(): void
    {
        $generator = new DefaultIdentifierGenerator();

        $previous = '';

        for ($i = 1; $i < 10; $i++) {
            $current = $generator->generatePacketIdentifier();
            self::assertGreaterThanOrEqual(0x0001, $current);
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
        self::$randomBytesFails = false;
        self::$hex2binFails = false;

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

    public function test_random_bytes_fails(): void
    {
        self::$randomBytesFails = true;
        self::$hex2binFails = false;

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

    public function test_hex2bin_fails(): void
    {
        self::$randomBytesFails = true;
        self::$hex2binFails = true;

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

    public static function randomBytes(int $length): string
    {
        if (self::$randomBytesFails) {
            throw new \RuntimeException('test');
        }

        return \random_bytes($length);
    }

    public static function hex2bin(string $string)
    {
        if (self::$hex2binFails) {
            return false;
        }

        return \hex2bin($string);
    }
}
