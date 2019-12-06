<?php

namespace BinSoul\Net\Mqtt;

/**
 * Generates packet identifiers.
 */
interface PacketIdentifierGenerator
{
    /**
     * Generates a packet identifier between 1 and 0xFFFF.
     *
     * @return int
     */
    public function generatePacketIdentifier();
}
