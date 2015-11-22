<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the PUBLISH packet.
 */
class PublishRequestPacket extends BasePacket
{
    use IdentifiablePacket;

    /** @var string */
    private $topic;
    /** @var string */
    private $payload;
    /** @var bool */
    private $isDuplicate;
    /** @var bool */
    private $isRetained;
    /** @var int */
    private $qosLevel;

    protected $packetType = Packet::TYPE_PUBLISH;

    public function read(PacketStream $stream)
    {
        parent::read($stream);
        $this->assertRemainingPacketLength();

        $this->isDuplicate = $this->packetFlags & 8;
        $this->isRetained = $this->packetFlags & 1;
        $this->qosLevel = ($this->packetFlags & 6) >> 1;
        $originalPosition = $stream->getPosition();
        $this->topic = $stream->readString();
        $this->identifier = null;
        if ($this->qosLevel > 0) {
            $this->identifier = $stream->readWord();
        }

        $payloadLength = $this->remainingPacketLength - ($stream->getPosition() - $originalPosition);
        $this->payload = $stream->read($payloadLength);

        $this->assertValidQosLevel($this->qosLevel);
        $this->assertValidString($this->topic);
    }

    /**
     * Returns the topic.
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Sets the topic.
     *
     * @param string $value
     */
    public function setTopic($value)
    {
        $this->assertValidString($value);
        if (strlen($value) == 0) {
            throw new \InvalidArgumentException('The topic must not be empty.');
        }

        $this->topic = $value;
    }

    /**
     * Returns the payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Sets the payload.
     *
     * @param string $value
     */
    public function setPayload($value)
    {
        $this->payload = $value;
    }

    /**
     * Indicates if the packet is a duplicate.
     *
     * @return bool
     */
    public function isDuplicate()
    {
        return $this->isDuplicate;
    }

    /**
     * Marks the packet as duplicate.
     *
     * @param bool $value
     */
    public function setDuplicate($value)
    {
        $this->isDuplicate = $value;
    }

    /**
     * Indicates if the packet is retained.
     *
     * @return bool
     */
    public function isRetained()
    {
        return $this->isRetained;
    }

    /**
     * Marks the packet as retained.
     *
     * @param bool $value
     */
    public function setRetained($value)
    {
        $this->isRetained = $value;
    }

    /**
     * Returns the quality of service level.
     *
     * @return int
     */
    public function getQosLevel()
    {
        return $this->qosLevel;
    }

    /**
     * Sets the quality of service level.
     *
     * @param int $value
     */
    public function setQosLevel($value)
    {
        $this->assertValidQosLevel($value);

        $this->qosLevel = $value;
    }
}
