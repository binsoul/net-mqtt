<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Exception\UnknownPacketTypeException;

/**
 * Builds instances of the {@see Packet} interface.
 */
interface PacketFactory
{
    /**
     * Builds a packet object for the given type.
     *
     * @throws UnknownPacketTypeException
     */
    public function build(int $type): Packet;
}
