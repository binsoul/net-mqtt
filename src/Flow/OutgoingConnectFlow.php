<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\ConnectRequestPacket;
use BinSoul\Net\Mqtt\Packet\ConnectResponsePacket;
use BinSoul\Net\Mqtt\PacketFactory;
use Override;

/**
 * Represents a flow starting with an outgoing CONNECT packet.
 */
class OutgoingConnectFlow extends AbstractFlow
{
    /**
     * Constructs an instance of this class.
     */
    public function __construct(
        PacketFactory $packetFactory,
        private Connection $connection,
        ClientIdentifierGenerator $generator
    ) {
        parent::__construct($packetFactory);

        if ($this->connection->getClientID() === '') {
            $this->connection = $this->connection->withClientID($generator->generateClientIdentifier());
        }
    }

    public function getCode(): string
    {
        return 'connect';
    }

    /**
     * @return ConnectRequestPacket
     */
    public function start(): ?Packet
    {
        /** @var ConnectRequestPacket $packet */
        $packet = $this->generatePacket(Packet::TYPE_CONNECT);
        $packet->setProtocolLevel($this->connection->getProtocol());
        $packet->setKeepAlive($this->connection->getKeepAlive());
        $packet->setClientID($this->connection->getClientID());
        $packet->setCleanSession($this->connection->isCleanSession());
        $packet->setUsername($this->connection->getUsername());
        $packet->setPassword($this->connection->getPassword());

        $will = $this->connection->getWill();

        if ($will !== null) {
            $packet->setWill($will->getTopic(), $will->getPayload(), $will->getQosLevel(), $will->isRetained());
        }

        return $packet;
    }

    #[Override]
    public function accept(Packet $packet): bool
    {
        return $packet->getPacketType() === Packet::TYPE_CONNACK;
    }

    #[Override]
    public function next(Packet $packet): ?Packet
    {
        /** @var ConnectResponsePacket $packet */
        if ($packet->isSuccess()) {
            $this->succeed($this->connection);
        } else {
            $this->fail($packet->getErrorName());
        }

        return null;
    }
}
