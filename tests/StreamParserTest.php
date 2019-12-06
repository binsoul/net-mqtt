<?php

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\StreamParser;
use PHPUnit\Framework\TestCase;

class StreamParserTest extends TestCase
{
    private $packets;

    protected function setUp(): void
    {
        $packets = array_fill(1, 15, []);

        $packets[1][] = "\x10\x1a\x00\x04MQTT\x04\x02\x00<\x00\x0eBinSoul-000000";
        $packets[2][] = " \x02\x00\x00";
        $packets[9][] = "\x90\x03\x00\x01\x00";
        $packets[8][] = "\x82\x06\x00\x01\x00\x01#\x00";
        $packets[3][] = "0\x0d\x00\x06TopicAqos 0";
        $packets[3][] = "0\x0d\x00\x06TopicAqos 1";
        $packets[3][] = "0\x0d\x00\x06TopicAqos 2";
        $packets[3][] = "0\x0f\x00\x08TopicA/Bqos 0";
        $packets[3][] = "0\x0e\x00\x07Topic/Cqos 1";
        $packets[3][] = "0\x0f\x00\x08TopicA/Cqos 2";
        $packets[3][] = "0\x0a\x00\x08TopicA/B";
        $packets[3][] = "0\x09\x00\x07Topic/C";
        $packets[3][] = "0\x0a\x00\x08TopicA/C";
        $packets[3][] = "0\x0f\x00\x08TopicA/Bqos 0";
        $packets[3][] = "0\x0e\x00\x07Topic/Cqos 1";
        $packets[3][] = "0\x0f\x00\x08TopicA/Cqos 2";
        $packets[3][] = "0 \x00\x07Topic/Cclient not disconnected";
        $packets[3][] = "0#\x00\x08TopicA/Coverlapping topic filters";
        $packets[3][] = "0\x19\x00\x07/TopicAkeepalive expiry";
        $packets[3][] = "0\x0a\x00\x08TopicA/B";
        $packets[3][] = "0\x0a\x00\x08TopicA/C";
        $packets[13][] = "\xd0\x00";

        $this->packets = $packets;
    }

    public function test_returns_expected_packets()
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

    public function test_detects_unknown_packets()
    {
        $parser = new StreamParser();
        $called = 0;
        $parser->onError(function () use (&$called) {
            ++$called;
        });

        $parser->push("\x00");
        $parser->push("\xF0");

        $this->assertEquals(2, $called);
    }

    public function test_handles_malformed_packets()
    {
        $parser = new StreamParser();
        $called = 0;
        $parser->onError(function () use (&$called) {
            ++$called;
        });

        $parser->push("\x16\x00");
        $this->assertEquals(1, $called);
    }

    public function test_handles_fragmented_packets()
    {
        $parser = new StreamParser();
        $called = 0;
        $parser->onError(function () use (&$called) {
            ++$called;
        });

        $packets = $parser->push("0\x0d");
        $this->assertEquals(0, count($packets));
        $packets = $parser->push("\x00\x06TopicAqos 1");
        $this->assertEquals(1, count($packets));

        $packets = $parser->push("0\x0d\x00\x06TopicA");
        $this->assertEquals(0, count($packets));
        $packets = $parser->push('qos 1');
        $this->assertEquals(1, count($packets));
    }
}
