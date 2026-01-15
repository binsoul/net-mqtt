<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\DefaultIdentifierGenerator;
use InvalidArgumentException;

/**
 * Provides methods for packets with an identifier.
 */
trait IdentifiablePacket
{
    /**
     * @var int<1, 65535>|null
     */
    protected ?int $identifier = null;

    /**
     * Returns the identifier.
     *
     * @return int<1,  65535>
     */
    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    /**
     * Sets the identifier.
     *
     * @param int<1, 65535>|null $value
     */
    public function setIdentifier(?int $value): void
    {
        if ($value !== null && ($value < 1 || $value > 0xFFFF)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected an identifier between 0x0001 and 0xFFFF but got %x',
                    $value
                )
            );
        }

        $this->identifier = $value;
    }

    /**
     * Returns the identifier or generates a new one.
     *
     * @return int<1, 65535>
     */
    protected function generateIdentifier(): int
    {
        if ($this->identifier === null) {
            $this->identifier = DefaultIdentifierGenerator::buildPacketIdentifier();
        }

        return $this->identifier;
    }
}
