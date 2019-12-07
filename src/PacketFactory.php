<?php

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
     * @param int $type
     *
     * @throws UnknownPacketTypeException
     *
     * @return Packet
     */
    public function build($type);
}
