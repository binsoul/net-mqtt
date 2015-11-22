<?php

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Packet;

/**
 * Represents the SUBSCRIBE packet.
 */
class SubscribeRequestPacket extends BasePacket
{
    use IdentifiablePacket;

    /** @var string */
    private $topic;
    /** @var int */
    private $qosLevel;

    protected $packetType = Packet::TYPE_SUBSCRIBE;
    protected $packetFlags = 2;

    public function read(PacketStream $stream)
    {
        parent::read($stream);
        $this->assertPacketFlags(2);
        $this->assertRemainingPacketLength();

        $this->identifier = $stream->readWord();
        $this->topic = $stream->readString();
        $this->qosLevel = $stream->readByte();

        $this->assertValidQosLevel($this->qosLevel);
        $this->assertValidString($this->topic);
    }

    public function write(PacketStream $stream)
    {
        $data = new PacketStream();

        $data->writeWord($this->generateIdentifier());
        $data->writeString($this->topic);
        $data->writeByte($this->qosLevel);

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
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
