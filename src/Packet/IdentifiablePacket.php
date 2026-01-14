<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use InvalidArgumentException;

/**
 * Provides methods for packets with an identifier.
 */
trait IdentifiablePacket
{
    protected ?int $identifier = null;

    private static int $nextIdentifier = 0;

    /**
     * Returns the identifier.
     */
    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    /**
     * Sets the identifier.
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
     */
    protected function generateIdentifier(): int
    {
        if ($this->identifier === null) {
            self::$nextIdentifier = (self::$nextIdentifier + 1) & 0xFFFF;

            if (self::$nextIdentifier === 0) {
                self::$nextIdentifier = 1;
            }

            $this->identifier = self::$nextIdentifier;
        }

        return $this->identifier;
    }
}
