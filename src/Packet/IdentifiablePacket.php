<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

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
     * @var int<0, 65535>
     */
    private static int $nextIdentifier = 0;

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
            self::$nextIdentifier = (self::$nextIdentifier + 1) & 0xFFFF;

            if (self::$nextIdentifier === 0) {
                self::$nextIdentifier = 1;
            }

            $this->identifier = self::$nextIdentifier;
        }

        return $this->identifier;
    }
}
