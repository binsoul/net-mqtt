<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the UNSUBACK packet.
 */
class UnsubscribeResponsePacket extends IdentifierOnlyPacket
{
    protected static int $packetType = Packet::TYPE_UNSUBACK;
}
