<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ValidatorTest extends TestCase
{
    private const string EXCEPTION_CLASS_DEFAULT = InvalidArgumentException::class;

    private const string EXCEPTION_CLASS_ALTERNATIVE = RuntimeException::class;

    private const int MAX_STRING_LENGTH = 0xFFFF;

    public function test_accepts_valid_string_length(): void
    {
        $value = str_repeat('a', self::MAX_STRING_LENGTH);

        Validator::assertValidStringLength($value);

        $this->expectNotToPerformAssertions();
    }

    public function test_throws_exception_for_string_too_long(): void
    {
        $value = str_repeat('a', self::MAX_STRING_LENGTH + 1);

        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidStringLength($value);
    }

    public function test_throws_custom_exception_for_string_too_long(): void
    {
        $value = str_repeat('a', self::MAX_STRING_LENGTH + 1);

        $this->expectException(self::EXCEPTION_CLASS_ALTERNATIVE);

        Validator::assertValidStringLength($value, self::EXCEPTION_CLASS_ALTERNATIVE);
    }

    public function test_accepts_valid_utf8_string(): void
    {
        Validator::assertValidString('Hello World');

        $this->expectNotToPerformAssertions();
    }

    public function test_accepts_valid_utf8_string_with_special_characters(): void
    {
        Validator::assertValidString('Hëllö Wörld');

        $this->expectNotToPerformAssertions();
    }

    public function test_accepts_empty_string(): void
    {
        Validator::assertValidString('');

        $this->expectNotToPerformAssertions();
    }

    public function test_throws_exception_for_invalid_utf8(): void
    {
        $value = "\xC3\x28";

        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidString($value);
    }

    public function test_throws_exception_for_null_characters(): void
    {
        $value = "test\x00\x00";

        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidString($value);
    }

    public function test_throws_custom_exception_for_invalid_string(): void
    {
        $value = "\xC3\x28";

        $this->expectException(self::EXCEPTION_CLASS_ALTERNATIVE);

        Validator::assertValidString($value, self::EXCEPTION_CLASS_ALTERNATIVE);
    }

    public function test_accepts_valid_non_empty_string(): void
    {
        Validator::assertValidNonEmptyString('topic');

        $this->expectNotToPerformAssertions();
    }

    public function test_throws_exception_for_empty_string(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidNonEmptyString('');
    }

    public function test_throws_custom_exception_for_empty_string(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_ALTERNATIVE);

        Validator::assertValidNonEmptyString('', self::EXCEPTION_CLASS_ALTERNATIVE);
    }

    public function test_throws_exception_for_non_empty_invalid_string(): void
    {
        $value = "test\xC3\x28";

        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidNonEmptyString($value);
    }

    public function test_accepts_valid_topic(): void
    {
        Validator::assertValidTopic('home/sensor/temperature');

        $this->expectNotToPerformAssertions();
    }

    public function test_throws_exception_for_topic_with_plus_wildcard(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidTopic('home/+/temperature');
    }

    public function test_throws_exception_for_topic_with_hash_wildcard(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidTopic('home/sensor/#');
    }

    public function test_throws_exception_for_empty_topic(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidTopic('');
    }

    public function test_throws_custom_exception_for_invalid_topic(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_ALTERNATIVE);

        Validator::assertValidTopic('home/+/temperature', self::EXCEPTION_CLASS_ALTERNATIVE);
    }

    public function test_accepts_qos_level_zero(): void
    {
        Validator::assertValidQosLevel(0);

        $this->expectNotToPerformAssertions();
    }

    public function test_accepts_qos_level_one(): void
    {
        Validator::assertValidQosLevel(1);

        $this->expectNotToPerformAssertions();
    }

    public function test_accepts_qos_level_two(): void
    {
        Validator::assertValidQosLevel(2);

        $this->expectNotToPerformAssertions();
    }

    public function test_throws_exception_for_negative_qos_level(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidQosLevel(-1);
    }

    public function test_throws_exception_for_qos_level_too_high(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidQosLevel(3);
    }

    public function test_throws_custom_exception_for_invalid_qos_level(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_ALTERNATIVE);

        Validator::assertValidQosLevel(5, self::EXCEPTION_CLASS_ALTERNATIVE);
    }

    public function test_accepts_valid_identifier_minimum(): void
    {
        Validator::assertValidIdentifier(1);

        $this->expectNotToPerformAssertions();
    }

    public function test_accepts_valid_identifier_maximum(): void
    {
        Validator::assertValidIdentifier(65535);

        $this->expectNotToPerformAssertions();
    }

    public function test_accepts_valid_identifier_middle(): void
    {
        Validator::assertValidIdentifier(32768);

        $this->expectNotToPerformAssertions();
    }

    public function test_throws_exception_for_identifier_zero(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidIdentifier(0);
    }

    public function test_throws_exception_for_negative_identifier(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidIdentifier(-1);
    }

    public function test_throws_exception_for_identifier_too_high(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidIdentifier(0xFFFF + 1);
    }

    public function test_throws_custom_exception_for_invalid_identifier(): void
    {
        $this->expectException(self::EXCEPTION_CLASS_ALTERNATIVE);

        Validator::assertValidIdentifier(100000, self::EXCEPTION_CLASS_ALTERNATIVE);
    }

    public function test_truncates_long_string_in_error_message(): void
    {
        $value = str_repeat('a', 1000);

        $this->expectException(self::EXCEPTION_CLASS_DEFAULT);

        Validator::assertValidString($value . "\x00\x00");
    }
}
