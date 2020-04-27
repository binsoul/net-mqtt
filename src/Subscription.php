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
     */
    public function getQosLevel(): int;

    /**
     * Returns a new subscription with the given topic filter.
     *
     * @return self
     */
    public function withFilter(string $filter): Subscription;

    /**
     * Returns a new subscription with the given quality of service level.
     *
     * @return self
     */
    public function withQosLevel(int $level): Subscription;
}
