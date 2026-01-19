<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;

/**
 * Represents a flow starting with an incoming CONNECT packet.
 */
class IncomingConnectFlow extends AbstractFlow
{
    /**
     * Constructs an instance of this class.
     *
     * @param int<0, 255> $returnCode
     */
    public function __construct(
        PacketFactory $packetFactory,
        private readonly Connection $connection,
        private readonly int $returnCode,
        private readonly bool $sessionPresent
    ) {
        parent::__construct($packetFactory);
    }

    public function getCode(): string
    {
        return 'connect';
    }

    /**
     * @return ConnectResponsePacket
     */
    public function start(): ?Packet
    {
        $this->succeed($this->connection);

        /** @var ConnectResponsePacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_CONNACK);
        $packet->setSessionPresent($this->sessionPresent);
        $packet->setReturnCode($this->returnCode);

        return $packet;
    }
}
