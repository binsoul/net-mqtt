<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Validator;
use InvalidArgumentException;
use Override;

/**
 * Represents the PUBLISH packet.
 */
class PublishRequestPacket extends BasePacket
{
    use IdentifiablePacket;

    protected static int $packetType = Packet::TYPE_PUBLISH;

    /**
     * @var non-empty-string
     */
    private string $topic = 'topic';

    private string $payload = '';

    #[Override]
    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertRemainingPacketLength();

        $originalPosition = $stream->getPosition();
        $topic = $stream->readString();
        Validator::assertValidTopic($topic, MalformedPacketException::class);
        $this->topic = $topic;

        $qosLevel = ($this->packetFlags & 6) >> 1;
        Validator::assertValidQosLevel($qosLevel, MalformedPacketException::class);

        if ($qosLevel === 0 && $this->isDuplicate()) {
            throw new MalformedPacketException('The duplicate flag must be zero if the quality of service level is zero.');
        }

        $this->identifier = null;

        if ($qosLevel > 0) {
            $identifier = $stream->readWord();
            Validator::assertValidIdentifier($identifier, MalformedPacketException::class);
            $this->identifier = $identifier;
        }

        $payloadLength = $this->remainingPacketLength - ($stream->getPosition() - $originalPosition);
        $this->payload = $stream->read($payloadLength);
    }

    #[Override]
    public function write(PacketStream $stream): void
    {
        if ($this->getQosLevel() === 0) {
            $this->setDuplicate(false);
        }

        $data = new PacketStream();

        $data->writeString($this->topic);

        if ($this->getQosLevel() > 0) {
            $data->writeWord($this->generateIdentifier());
        }

        $data->write($this->payload);

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the topic.
     *
     * @return non-empty-string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * Sets the topic.
     *
     * @param non-empty-string $value
     *
     * @throws InvalidArgumentException
     */
    public function setTopic(string $value): void
    {
        Validator::assertValidTopic($value);

        $this->topic = $value;
    }

    /**
     * Returns the payload.
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Sets the payload.
     */
    public function setPayload(string $value): void
    {
        $this->payload = $value;
    }

    /**
     * Indicates if the packet is a duplicate.
     */
    public function isDuplicate(): bool
    {
        return ($this->packetFlags & 8) === 8;
    }

    /**
     * Marks the packet as duplicate.
     */
    public function setDuplicate(bool $value): void
    {
        $this->packetFlags = $value ? ($this->packetFlags | 8) & 0x0F : ($this->packetFlags & ~8) & 0x0F;
    }

    /**
     * Indicates if the packet is retained.
     */
    public function isRetained(): bool
    {
        return ($this->packetFlags & 1) === 1;
    }

    /**
     * Marks the packet as retained.
     */
    public function setRetained(bool $value): void
    {
        $this->packetFlags = $value ? ($this->packetFlags | 1) & 0x0F : ($this->packetFlags & ~1) & 0x0F;
    }

    /**
     * Returns the quality of service level.
     *
     * @return int<0, 2>
     */
    public function getQosLevel(): int
    {
        $qosLevel = ($this->packetFlags & 6) >> 1;

        return $qosLevel > 2 ? 0 : $qosLevel;
    }

    /**
     * Sets the quality of service level.
     *
     * @param int<0, 2> $value
     *
     * @throws InvalidArgumentException
     */
    public function setQosLevel(int $value): void
    {
        Validator::assertValidQosLevel($value);

        $this->packetFlags = ($this->packetFlags | ($value & 3) << 1) & 0x0F;
    }
}
