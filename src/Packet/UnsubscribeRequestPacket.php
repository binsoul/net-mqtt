<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;

/**
 * Represents the UNSUBSCRIBE packet.
 */
class UnsubscribeRequestPacket extends BasePacket
{
    use IdentifiablePacket;

    /** @var string[] */
    private $topics = [];

    protected static $packetType = Packet::TYPE_UNSUBSCRIBE;
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
            $this->topics[] = $stream->readString();
        } while (($stream->getPosition() - $originalPosition) < $this->remainingPacketLength);
    }

    public function write(PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeWord($this->generateIdentifier());
        foreach($this->topics as $topic) {
            $data->writeString($topic);
        }

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the topics.
     *
     * @return string[]
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * Sets the topics.
     *
     * @param string[] $values
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function setTopics(array $values): void
    {
        foreach ($values as $index => $value) {
            if ($value === '') {
                throw new InvalidArgumentException(sprintf('The topic %s must not be empty.', $index));
            }

            try {
                $this->assertValidString($value);
            } catch (MalformedPacketException $e) {
                throw new InvalidArgumentException(sprintf('Topic %s: '.$e->getMessage(), $index));
            }

        }

        $this->topics = $values;
    }
}
