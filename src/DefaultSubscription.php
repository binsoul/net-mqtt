<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use InvalidArgumentException;

/**
 * Provides a default implementation of the {@see Subscription} interface.
 */
class DefaultSubscription implements Subscription
{
    private string $filter;

    /**
     * @var int<0, 2>
     */
    private int $qosLevel;

    /**
     * Constructs an instance of this class.
     *
     * @param int<0, 2> $qosLevel
     */
    public function __construct(string $filter, int $qosLevel = 0)
    {
        $this->assertValidQosLevel($qosLevel);

        $this->filter = $filter;
        $this->qosLevel = $qosLevel;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function getQosLevel(): int
    {
        return $this->qosLevel;
    }

    public function withFilter(string $filter): self
    {
        $result = clone $this;
        $result->filter = $filter;

        return $result;
    }

    public function withQosLevel(int $level): self
    {
        $result = clone $this;
        $result->qosLevel = $level;

        return $result;
    }

    /**
     * Asserts that the given quality of service level is valid.
     *
     * @throws InvalidArgumentException
     */
    private function assertValidQosLevel(int $level): void
    {
        if ($level < 0 || $level > 2) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected a quality of service level between 0 and 2 but got %d.',
                    $level
                )
            );
        }
    }
}
