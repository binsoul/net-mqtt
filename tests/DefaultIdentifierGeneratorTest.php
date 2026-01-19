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
use function hex2bin;
use PHPUnit\Framework\TestCase;
use function random_bytes;
use RuntimeException;

final class DefaultIdentifierGeneratorTest extends TestCase
{
    private static bool $randomBytesFails = false;

    private static bool $hex2binFails = false;

    public function test_generates_packet_id(): void
    {
        $generator = new DefaultIdentifierGenerator();

        $previous = '';

        for ($i = 1; $i < 10; $i++) {
            $current = $generator->generatePacketIdentifier();
            $this->assertGreaterThanOrEqual(0x0001, $current);
            $this->assertLessThanOrEqual(0xFFFF, $current);

            if ($i === 1) {
                $previous = $generator->generatePacketIdentifier();
                $this->assertLessThanOrEqual(0xFFFF, $previous);
            }

            $this->assertNotSame($current, $previous);
            $previous = $current;
        }
    }

    public function test_wraps_packet_ids(): void
    {
        $generator = new DefaultIdentifierGenerator();

        for ($i = 1; $i <= 0xFFFF; $i++) {
            $generator->generatePacketIdentifier();
        }

        $this->assertSame(1, $generator->generatePacketIdentifier());
    }

    public function test_generates_client_id(): void
    {
        self::$randomBytesFails = false;
        self::$hex2binFails = false;

        $generator = new DefaultIdentifierGenerator();

        $previous = '';

        for ($i = 1; $i < 10; $i++) {
            $current = $generator->generateClientIdentifier();
            $this->assertLessThanOrEqual(23, strlen($current));

            if ($i === 1) {
                $previous = $generator->generateClientIdentifier();
                $this->assertLessThanOrEqual(23, strlen($previous));
            }

            $this->assertNotSame($current, $previous);
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
            $this->assertLessThanOrEqual(23, strlen($current));

            if ($i === 1) {
                $previous = $generator->generateClientIdentifier();
                $this->assertLessThanOrEqual(23, strlen($previous));
            }

            $this->assertNotSame($current, $previous);
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
            $this->assertLessThanOrEqual(23, strlen($current));

            if ($i === 1) {
                $previous = $generator->generateClientIdentifier();
                $this->assertLessThanOrEqual(23, strlen($previous));
            }

            $this->assertNotSame($current, $previous);
            $previous = $current;
        }
    }

    public static function randomBytes(int $length): string
    {
        if (self::$randomBytesFails) {
            throw new RuntimeException('test');
        }

        return random_bytes($length);
    }

    public static function hex2bin(string $string): false|string
    {
        if (self::$hex2binFails) {
            return false;
        }

        return hex2bin($string);
    }
}
