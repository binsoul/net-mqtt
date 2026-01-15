<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use Exception;
use InvalidArgumentException;

class Validator
{
    /**
     * Asserts that the given string is a well-formed MQTT string.
     *
     * @template T of Exception
     *
     * @param class-string<T> $exceptionClass
     *
     * @throws T
     */
    public static function assertValidStringLength(string $value, string $exceptionClass = InvalidArgumentException::class): void
    {
        if (strlen($value) > 0xFFFF) {
            throw new $exceptionClass(
                sprintf(
                    'The string "%s" is longer than 65535 byte.',
                    substr($value, 0, 50)
                )
            );
        }
    }

    /**
     * Asserts that the given string is a well-formed MQTT string.
     *
     * @template T of Exception
     *
     * @param class-string<T> $exceptionClass
     *
     * @throws T
     */
    public static function assertValidString(string $value, string $exceptionClass = InvalidArgumentException::class): void
    {
        self::assertValidStringLength($value);

        if (! mb_check_encoding($value, 'UTF-8')) {
            throw new $exceptionClass(
                sprintf(
                    'The string "%s" is not well-formed UTF-8.',
                    substr($value, 0, 50)
                )
            );
        }

        if (preg_match('/[\xD8-\xDF][\x00-\xFF]|\x00\x00/x', $value)) {
            throw new $exceptionClass(
                sprintf(
                    'The string "%s" contains invalid characters.',
                    substr($value, 0, 50)
                )
            );
        }
    }

    /**
     * Asserts that the given string is a valid non-empty string.
     *
     * @template T of Exception
     *
     * @param class-string<T> $exceptionClass
     *
     * @phpstan-return ($value is non-empty-string ? void : never)
     *
     * @throws T
     */
    public static function assertValidNonEmptyString(string $value, string $exceptionClass = InvalidArgumentException::class): void
    {
        if ($value === '') {
            throw new $exceptionClass('The topic is empty.');
        }

        self::assertValidString($value);
    }

    /**
     * Asserts that the given string is a valid topic.
     *
     * @template T of Exception
     *
     * @param class-string<T> $exceptionClass
     *
     * @phpstan-return ($value is non-empty-string ? void : never)
     *
     * @throws T
     */
    public static function assertValidTopic(string $value, string $exceptionClass = InvalidArgumentException::class): void
    {
        if (strpbrk($value, '+#')) {
            throw new $exceptionClass('The topic contains wildcards.');
        }

        self::assertValidNonEmptyString($value);
    }

    /**
     * Asserts that the given quality of service level is valid.
     *
     * @template T of Exception
     *
     * @param class-string<T> $exceptionClass
     *
     * @phpstan-return ($level is 0|1|2 ? void : never)
     *
     * @throws T
     */
    public static function assertValidQosLevel(int $level, string $exceptionClass = InvalidArgumentException::class): void
    {
        if ($level < 0 || $level > 2) {
            throw new $exceptionClass(
                sprintf(
                    'Expected a quality of service level between 0 and 2 but got %d.',
                    $level
                )
            );
        }
    }

    /**
     * Asserts that the given quality of service level is valid.
     *
     * @template T of Exception
     *
     * @param class-string<T> $exceptionClass
     *
     * @phpstan-return ($identifier is int<1, 65535> ? void : never)
     *
     * @throws T
     */
    public static function assertValidIdentifier(int $identifier, string $exceptionClass = InvalidArgumentException::class): void
    {
        if ($identifier < 1 || $identifier > 0xFFFF) {
            throw new $exceptionClass(
                sprintf(
                    'Expected an identifier between 1 and 65535 but got %d.',
                    $identifier
                )
            );
        }
    }
}
