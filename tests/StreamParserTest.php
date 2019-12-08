<?php

declare(strict_types=1);

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\StreamParser;
use PHPUnit\Framework\TestCase;

class StreamParserTest extends TestCase
{
    private $packets;

    protected function setUp(): void
    {
        $packets = array_fill(1, 15, []);

        $packets[Packet::TYPE_CONNECT][] = "\x10\x1a\x00\x04MQTT\x04\x02\x00<\x00\x0eBinSoul-000000";
        $packets[Packet::TYPE_CONNACK][] = "\x20\x02\x00\x00";
        $packets[Packet::TYPE_SUBACK][] = "\x90\x03\x00\x01\x00";
        $packets[Packet::TYPE_SUBSCRIBE][] = "\x82\x06\x00\x01\x00\x01#\x00";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0d\x00\x06TopicAqos 0";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0d\x00\x06TopicAqos 1";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0d\x00\x06TopicAqos 2";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0f\x00\x08TopicA/Bqos 0";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0e\x00\x07Topic/Cqos 1";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0f\x00\x08TopicA/Cqos 2";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0a\x00\x08TopicA/B";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x09\x00\x07Topic/C";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0a\x00\x08TopicA/C";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0f\x00\x08TopicA/Bqos 0";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0e\x00\x07Topic/Cqos 1";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0f\x00\x08TopicA/Cqos 2";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x20\x00\x07Topic/Cclient not disconnected";
        $packets[Packet::TYPE_PUBLISH][] = "\x30#\x00\x08TopicA/Coverlapping topic filters";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x19\x00\x07/TopicAkeepalive expiry";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0a\x00\x08TopicA/B";
        $packets[Packet::TYPE_PUBLISH][] = "\x30\x0a\x00\x08TopicA/C";
        $packets[Packet::TYPE_PINGREQ][] = "\xc0\x00";
        $packets[Packet::TYPE_PINGRESP][] = "\xd0\x00";
        $packets[Packet::TYPE_PUBACK][] = "\x40\x02\x00\x00";
        $packets[Packet::TYPE_PUBCOMP][] = "\x70\x02\x00\x00";
        $packets[Packet::TYPE_PUBREL][] = "\x60\x02\x00\x00";

        $this->packets = $packets;
    }

    public function test_returns_expected_packets(): void
    {
        $parser = new StreamParser();
        foreach ($this->packets as $type => $packets) {
            foreach ($packets as $data) {
                $result = $parser->push($data);
                foreach ($result as $packet) {
                    $this->assertEquals($type, $packet->getPacketType());
                }
            }
        }
    }

    public function test_detects_unknown_packets(): void
    {
        $parser = new StreamParser();
        $called = 0;
        $parser->onError(static function () use (&$called) {
            ++$called;
        });

        $parser->push("\x00");
        $parser->push("\xF0");

        $this->assertEquals(2, $called);
    }

    public function test_handles_malformed_packets(): void
    {
        $parser = new StreamParser();
        $called = 0;
        $parser->onError(static function () use (&$called) {
            ++$called;
        });

        $parser->push("\x16\x00");
        $this->assertEquals(1, $called);
    }

    public function test_handles_fragmented_packets(): void
    {
        $parser = new StreamParser();
        $called = 0;
        $parser->onError(static function () use (&$called) {
            ++$called;
        });

        $packets = $parser->push("0\x0d");
        $this->assertCount(0, $packets);
        $packets = $parser->push("\x00\x06TopicAqos 1");
        $this->assertCount(1, $packets);

        $packets = $parser->push("0\x0d\x00\x06TopicA");
        $this->assertCount(0, $packets);
        $packets = $parser->push('qos 1');
        $this->assertCount(1, $packets);
    }
}
