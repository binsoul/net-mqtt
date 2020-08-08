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

    /** @var int[] */
    private $topics = [];

    protected static $packetType = Packet::TYPE_SUBSCRIBE;
    protected $packetFlags = 2;

    public function read(PacketStream $stream): void
    {
        parent::read($stream);

        $this->assertPacketFlags(2);
        $this->assertRemainingPacketLength();

        $originalPosition = $stream->getPosition();
        $this->identifier = $stream->readWord();
        $this->topics = [];

        do {
            $topic = $stream->readString();
            $qosLevel = $stream->readByte();

            $this->assertValidString($topic);
            $this->assertValidQosLevel($qosLevel);

            $this->topics[$topic] = $qosLevel;
        } while (($stream->getPosition() - $originalPosition) < $this->remainingPacketLength);
    }

    public function write(PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeWord($this->generateIdentifier());

        foreach ($this->topics as $topic => $qosLevel) {
            $data->writeString($topic);
            $data->writeByte($qosLevel);
        }

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the topics as an array using topics as keys and corresponding quality of service levels as values.
     *
     * @return int[]
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addTopic(string $topic, int $qosLevel): void
    {
        if ($topic === '') {
            throw new InvalidArgumentException('The topic must not be empty.');
        }

        try {
            $this->assertValidString($topic);
            $this->assertValidQosLevel($qosLevel);
        } catch (MalformedPacketException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        $this->topics[$topic] = $qosLevel;
    }
}
