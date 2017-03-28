<?php

namespace BinSoul\Test\Net\Mqtt;

use BinSoul\Net\Mqtt\DefaultIdentifierGenerator;

class DefaultIdentifierGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function test_generates_packet_id()
    {
        $generator = new DefaultIdentifierGenerator();

        $previous = '';
        for ($i = 1; $i < 10; ++$i) {
            $current = $generator->generatePacketID();
            $this->assertLessThanOrEqual(0xFFFF, $current);
            if ($i === 1) {
                $previous = $generator->generatePacketID();
                $this->assertLessThanOrEqual(0xFFFF, $previous);
            }

            $this->assertNotEquals($current, $previous);
            $previous = $current;
        }
    }

    public function test_wraps_packet_ids()
    {
        $generator = new DefaultIdentifierGenerator();

        for ($i = 1; $i <= 0xFFFF; ++$i) {
            $generator->generatePacketID();
        }

        $this->assertEquals(1, $generator->generatePacketID());
    }

    public function test_generates_client_id()
    {
        $generator = new DefaultIdentifierGenerator();

        $previous = '';
        for ($i = 1; $i < 10; ++$i) {
            $current = $generator->generateClientID();
            $this->assertLessThanOrEqual(23, strlen($current));
            if ($i === 1) {
                $previous = $generator->generateClientID();
                $this->assertLessThanOrEqual(23, strlen($previous));
            }

            $this->assertNotEquals($current, $previous);
            $previous = $current;
        }
    }
}
