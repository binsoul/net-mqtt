<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Represents a subscription.
 */
interface Subscription
{
    /**
     * Returns the topic filter.
     */
    public function getFilter(): string;

    /**
     * Returns the quality of service level.
     *
     * @return int<0, 2>
     */
    public function getQosLevel(): int;

    /**
     * Returns a new subscription with the given topic filter.
     *
     * @return static
     */
    public function withFilter(string $filter): self;

    /**
     * Returns a new subscription with the given quality of service level.
     *
     * @param int<0, 2> $level
     *
     * @return static
     */
    public function withQosLevel(int $level): self;
}
