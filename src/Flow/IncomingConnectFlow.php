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
    private Connection $connection;

    private int $returnCode;

    private bool $sessionPresent;

    /**
     * Constructs an instance of this class.
     */
    public function __construct(PacketFactory $packetFactory, Connection $connection, int $returnCode, bool $sessionPresent)
    {
        parent::__construct($packetFactory);

        $this->connection = $connection;
        $this->returnCode = $returnCode;
        $this->sessionPresent = $sessionPresent;
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
