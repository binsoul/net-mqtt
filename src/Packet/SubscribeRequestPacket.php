<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;

/**
 * Represents the SUBSCRIBE packet.
 */
class SubscribeRequestPacket extends BasePacket
{
    use IdentifiablePacket;

    protected static int $packetType = Packet::TYPE_SUBSCRIBE;

    protected int $packetFlags = 2;

    private string $topic;

    private int $qosLevel;

    public function read(PacketStream $stream): void
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

    public function write(PacketStream $stream): void
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
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * Sets the topic.
     *
     * @throws InvalidArgumentException
     */
    public function setTopic(string $value): void
    {
        if ($value === '') {
            throw new InvalidArgumentException('The topic must not be empty.');
        }

        try {
            $this->assertValidString($value);
        } catch (MalformedPacketException $malformedPacketException) {
            throw new InvalidArgumentException($malformedPacketException->getMessage(), $malformedPacketException->getCode(), $malformedPacketException);
        }

        $this->topic = $value;
    }

    /**
     * Returns the quality of service level.
     */
    public function getQosLevel(): int
    {
        return $this->qosLevel;
    }

    /**
     * Sets the quality of service level.
     *
     * @throws InvalidArgumentException
     */
    public function setQosLevel(int $value): void
    {
        try {
            $this->assertValidQosLevel($value);
        } catch (MalformedPacketException $malformedPacketException) {
            throw new InvalidArgumentException($malformedPacketException->getMessage(), $malformedPacketException->getCode(), $malformedPacketException);
        }

        $this->qosLevel = $value;
    }
}
