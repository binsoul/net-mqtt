<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Represents a message.
 */
interface Message
{
    /**
     * Returns the topic.
     */
    public function getTopic(): string;

    /**
     * Returns the payload.
     */
    public function getPayload(): string;

    /**
     * Returns the quality of service level.
     *
     * @return int<0, 2>
     */
    public function getQosLevel(): int;

    /**
     * Indicates if the message is a duplicate.
     */
    public function isDuplicate(): bool;

    /**
     * Indicates if the message is retained.
     */
    public function isRetained(): bool;

    /**
     * Returns a new message with the given topic.
     *
     * @return static
     */
    public function withTopic(string $topic): self;

    /**
     * Returns a new message with the given payload.
     *
     * @return static
     */
    public function withPayload(string $payload): self;

    /**
     * Returns a new message with the given quality of service level.
     *
     * @param int<0, 2> $level
     *
     * @return static
     */
    public function withQosLevel(int $level): self;

    /**
     * Returns a new message flagged as retained.
     *
     * @return static
     */
    public function retain(): self;

    /**
     * Returns a new message flagged as not retained.
     *
     * @return static
     */
    public function release(): self;

    /**
     * Returns a new message flagged as duplicate.
     *
     * @return static
     */
    public function duplicate(): self;

    /**
     * Returns a new message flagged as original.
     *
     * @return static
     */
    public function original(): self;
}
