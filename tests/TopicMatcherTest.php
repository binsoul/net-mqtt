<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\TopicMatcher;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the TopicMatcher class.
 *
 * @author      Alin Eugen Deac <ade@vestergaardcompany.com>
 */
final class TopicMatcherTest extends TestCase
{
    /**
     * Instance of the topic matcher.
     */
    private TopicMatcher $matcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->matcher = new TopicMatcher();
    }

    /*************************************************
     * Data Providers
     ************************************************/

    /**
     * Data provider for canMatchTopic test.
     *
     * @see test_can_match_topic()
     *
     * @return Iterator<int, array<int, (bool | string)>>
     */
    public static function patternsAndTopics(): Iterator
    {
        // Test cases inspired by (https://github.com/eclipse/mosquitto) package
        // @see https://github.com/eclipse/mosquitto/blob/master/test/broker/03-pattern-matching.py
        // pattern, topic, expected to match
        // 0
        yield ['foo/bar', 'foo/bar', true];

        // 1
        yield ['foo/+', 'foo/bar', true];

        // 2
        yield ['foo/+/baz', 'foo/bar/baz', true];

        // 3
        yield ['foo/+/#', 'foo/bar/baz', true];

        // 4
        yield ['#', 'foo/bar/baz', true];

        ////////////////////////////////////
        // 5
        yield ['foo/bar', 'foo', false];

        // 6
        yield ['foo/+', 'foo/bar/baz', false];

        // 7
        yield ['foo/+/baz', 'foo/bar/bar', false];

        // 8
        yield ['foo/+/#', 'fo2/bar/baz', false];

        ////////////////////////////////////
        // 9
        yield ['#', '/foo/bar', true];

        // 10
        yield ['/#', '/foo/bar', true];

        // 11
        yield ['/#', 'foo/bar', false];

        ////////////////////////////////////
        // 12
        yield ['foo//bar', 'foo//bar', true];

        // 13
        yield ['foo//+', 'foo//bar', true];

        // 14
        yield ['foo/+/+/baz', 'foo///baz', true];

        // 15
        yield ['foo/bar/+', 'foo/bar/', true];

        ////////////////////////////////////
        // 16
        yield ['foo/#', 'foo/', true];

        // 17
        yield ['foo#', 'foo', false];

        // 18
        yield ['fo#o/', 'foo', false];

        // 19
        yield ['foo#', 'fooa', false];

        // 20
        yield ['foo+', 'foo', false];

        // 21
        yield ['foo+', 'fooa', false];

        ////////////////////////////////////
        // 22
        yield ['test/6/#', 'test/3', false];

        // 23
        yield ['foo/bar', 'foo/bar', true];

        // 24
        yield ['foo/+', 'foo/bar', true];

        // 25
        yield ['foo/+/baz', 'foo/bar/baz', true];

        ////////////////////////////////////
        // 26
        yield ['A/B/+/#', 'A/B/B/C', true];

        ////////////////////////////////////
        // 27
        yield ['foo/+/#', 'foo/bar/baz', true];

        // 28
        yield ['#', 'foo/bar/baz', true];

        ////////////////////////////////////
        // 29
        yield ['$SYS/bar', '$SYS/bar', true];

        // 30
        yield ['#', '$SYS/bar', false];

        // 31
        yield ['$BOB/bar', '$SYS/bar', false];
    }

    /*************************************************
     * Actual tests
     ************************************************/

    #[DataProvider('patternsAndTopics')]
    public function test_can_match_topic(string $pattern, string $topic, bool $expectedResult): void
    {
        $result = $this->matcher->matches($pattern, $topic);

        $this->assertSame($expectedResult, $result);
    }
}
