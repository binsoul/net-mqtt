<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Generates packet identifiers.
 */
interface PacketIdentifierGenerator
{
    /**
     * Generates a packet identifier between 1 and 0xFFFF.
     */
    public function generatePacketIdentifier(): int;
}
