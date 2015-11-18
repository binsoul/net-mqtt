<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the CONNECT packet.
 */
class ConnectRequestPacket extends BasePacket
{
    /** @var int */
    private $protocolLevel = 4;
    /** @var string */
    private $protocolName = 'MQTT';
    /** @var int */
    private $flags = 2;
    /** @var string */
    private $clientID = '';
    /** @var int */
    private $keepAlive = 60;

    protected $packetType = Packet::TYPE_CONNECT;

    public function read(PacketStream $stream)
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength();

        $this->protocolName = $stream->readString();
        $this->protocolLevel = $stream->readByte();
        $this->flags = $stream->readByte();
        $this->keepAlive = $stream->readWord();
        $this->clientID = $stream->readString();

        $this->assertValidString($this->protocolName);
        $this->assertValidString($this->clientID);
    }

    public function write(PacketStream $stream)
    {
        if ($this->clientID == '') {
            $this->clientID = 'binsoul-'.rand(100000, 999999);
        }

        $data = new PacketStream();

        $data->writeString($this->protocolName);
        $data->writeByte($this->protocolLevel);
        $data->writeByte($this->flags);
        $data->writeWord($this->keepAlive);
        $data->writeString($this->clientID);

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Changes the clean session flag.
     *
     * @param bool $value
     */
    public function setCleanSession($value)
    {
        if ($value) {
            $this->flags = $this->flags | 2;
        } else {
            $this->flags = $this->flags & 13;
        }
    }

    /**
     * Returns the protocol level.
     *
     * @return int
     */
    public function getProtocolLevel()
    {
        return $this->protocolLevel;
    }

    /**
     * Sets the protocol level.
     *
     * @param int $value
     */
    public function setProtocolLevel($value)
    {
        $this->protocolLevel = $value;
        if ($this->protocolLevel == 3) {
            $this->protocolName = 'MQIsdp';
        } elseif ($this->protocolLevel == 4) {
            $this->protocolName = 'MQTT';
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid protocol level %d.', $this->protocolLevel));
        }
    }

    /**
     * Returns the client id.
     *
     * @return string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * Sets the client id.
     *
     * @param string $value
     */
    public function setClientID($value)
    {
        $this->clientID = $value;

        if (strlen($this->clientID) < 1 || strlen($this->clientID) > 23) {
            throw new \InvalidArgumentException(sprintf('Invalid client id "%s".', $value));
        }
    }

    /**
     * Returns the keep alive time in seconds.
     *
     * @return int
     */
    public function getKeepAlive()
    {
        return $this->keepAlive;
    }

    /**
     * Sets the keep alive time in seconds.
     *
     * @param int $value
     */
    public function setKeepAlive($value)
    {
        $this->keepAlive = $value;
    }
}
