<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Validator;
use InvalidArgumentException;

/**
 * Represents the UNSUBSCRIBE packet.
 */
class UnsubscribeRequestPacket extends BasePacket
{
    use IdentifiablePacket;

    protected static int $packetType = Packet::TYPE_UNSUBSCRIBE;

    protected int $packetFlags = 2;

    /**
     * @var array<int, non-empty-string>
     */
    private array $topics = [];

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

        do {
            $topic = $stream->readString();
            Validator::assertValidNonEmptyString($topic, MalformedPacketException::class);
            $this->topics[] = $topic;
        } while (($stream->getPosition() - $originalPosition) < $this->remainingPacketLength);
    }

    public function write(PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeWord($this->generateIdentifier());

        foreach ($this->topics as $topic) {
            $data->writeString($topic);
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
        foreach ($values as $index => $value) {
            try {
                Validator::assertValidNonEmptyString($value);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(sprintf('Topic %s: ' . $e->getMessage(), $index), $e->getCode(), $e);
            }
        }

        $this->topics = $values;
    }
}
