<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketFactory;

/**
 * Represents a flow starting with an outgoing DISCONNECT packet.
 */
class OutgoingDisconnectFlow extends AbstractFlow
{
    private Connection $connection;

    /**
     * Constructs an instance of this class.
     */
    public function __construct(PacketFactory $packetFactory, Connection $connection)
    {
        parent::__construct($packetFactory);

        $this->connection = $connection;
    }

    public function getCode(): string
    {
        return 'disconnect';
    }

    public function start(): ?Packet
    {
        $this->succeed($this->connection);

        return $this->generatePacket(Packet::TYPE_DISCONNECT);
    }
}
