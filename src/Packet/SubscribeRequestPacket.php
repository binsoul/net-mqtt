<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Validator;
use InvalidArgumentException;

/**
 * Represents the SUBSCRIBE packet.
 */
class SubscribeRequestPacket extends BasePacket
{
    use IdentifiablePacket;

    protected static int $packetType = Packet::TYPE_SUBSCRIBE;

    protected int $packetFlags = 2;

    /**
     * @var array<int, non-empty-string>
     */
    private array $topics = [];

    /**
     * @var array<int, int<0, 2>>
     */
    private array $qosLevels = [];

    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(2);
        $this->assertRemainingPacketLength();

        $originalPosition = $stream->getPosition();
        $identifier = $stream->readWord();
        Validator::assertValidIdentifier($identifier, MalformedPacketException::class);
        $this->identifier = $identifier;
        $this->topics = [];
        $this->qosLevels = [];

        do {
            $topic = $stream->readString();
            $qosLevel = $stream->readByte();

            Validator::assertValidQosLevel($qosLevel, MalformedPacketException::class);
            Validator::assertValidNonEmptyString($topic, MalformedPacketException::class);

            $this->topics[] = $topic;
            $this->qosLevels[] = $qosLevel;
        } while (($stream->getPosition() - $originalPosition) < $this->remainingPacketLength);
    }

    public function write(PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeWord($this->generateIdentifier());

        foreach ($this->topics as $index => $topic) {
            $data->writeString($topic);
            $data->writeByte($this->qosLevels[$index] ?? 0);
        }

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the topics.
     *
     * @return array<int, non-empty-string>
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * Sets the topics.
     *
     * @param array<int, non-empty-string> $values
     *
     * @throws InvalidArgumentException
     */
    public function setTopics(array $values): void
    {
        if ($values === []) {
            throw new InvalidArgumentException('The array of topics is empty.');
        }

        foreach ($values as $index => $value) {
            try {
                Validator::assertValidNonEmptyString($value);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(
                    sprintf('Topic %s: ' . $e->getMessage(), $index),
                    $e->getCode(),
                    $e
                );
            }
        }

        $this->topics = $values;
    }

    /**
     * Returns the quality of service levels.
     *
     * @return array<int, int<0, 2>>
     */
    public function getQosLevels(): array
    {
        return $this->qosLevels;
    }

    /**
     * Sets the quality of service levels.
     *
     * @param array<int, int<0, 2>> $values
     *
     * @throws InvalidArgumentException
     */
    public function setQosLevels(array $values): void
    {
        foreach ($values as $index => $value) {
            try {
                Validator::assertValidQosLevel($value);
            } catch (InvalidArgumentException $malformedPacketException) {
                throw new InvalidArgumentException(
                    sprintf('QoS level %s: ' . $malformedPacketException->getMessage(), $index),
                    $malformedPacketException->getCode(),
                    $malformedPacketException
                );
            }
        }

        $this->qosLevels = $values;
    }
}
