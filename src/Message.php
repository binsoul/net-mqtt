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
     */
    public function withTopic(string $topic): Message;

    /**
     * Returns a new message with the given payload.
     */
    public function withPayload(string $payload): Message;

    /**
     * Returns a new message with the given quality of service level.
     */
    public function withQosLevel(int $level): Message;

    /**
     * Returns a new message flagged as retained.
     */
    public function retain(): Message;

    /**
     * Returns a new message flagged as not retained.
     */
    public function release(): Message;

    /**
     * Returns a new message flagged as duplicate.
     */
    public function duplicate(): Message;

    /**
     * Returns a new message flagged as original.
     */
    public function original(): Message;
}
