<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Provides a default implementation of the {@see Subscription} interface.
 */
class DefaultSubscription implements Subscription
{
    /**
     * Constructs an instance of this class.
     *
     * @param non-empty-string $filter
     * @param int<0, 2>        $qosLevel
     */
    public function __construct(
        private string $filter,
        private int $qosLevel = 0
    ) {
        Validator::assertValidNonEmptyString($filter);
        Validator::assertValidQosLevel($qosLevel);
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
        Validator::assertValidNonEmptyString($filter);

        $result = clone $this;
        $result->filter = $filter;

        return $result;
    }

    public function withQosLevel(int $level): self
    {
        Validator::assertValidQosLevel($level);

        $result = clone $this;
        $result->qosLevel = $level;

        return $result;
    }
}
