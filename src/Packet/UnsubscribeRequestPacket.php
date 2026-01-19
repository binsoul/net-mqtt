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
    private array $filters = [];

    #[Override]
    public function read(PacketStream $stream): void
    {
        parent::read($stream);

        $this->assertPacketFlags(2);
        $this->assertRemainingPacketLength();

        $originalPosition = $stream->getPosition();
        $identifier = $stream->readWord();
        Validator::assertValidIdentifier($identifier, MalformedPacketException::class);
        $this->identifier = $identifier;
        $this->filters = [];

        do {
            $filter = $stream->readString();
            Validator::assertValidNonEmptyString($filter, MalformedPacketException::class);
            $this->filters[] = $filter;
        } while (($stream->getPosition() - $originalPosition) < $this->remainingPacketLength);
    }

    #[Override]
    public function write(PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeWord($this->generateIdentifier());

        foreach ($this->filters as $filter) {
            $data->writeString($filter);
        }

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the filters.
     *
     * @return array<int, non-empty-string>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Sets the filters.
     *
     * @param array<int, non-empty-string> $values
     *
     * @throws InvalidArgumentException
     */
    public function setFilters(array $values): void
    {
        foreach ($values as $index => $value) {
            try {
                Validator::assertValidNonEmptyString($value);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(sprintf('Filter %s: ' . $e->getMessage(), $index), $e->getCode(), $e);
            }
        }

        $this->filters = $values;
    }
}
