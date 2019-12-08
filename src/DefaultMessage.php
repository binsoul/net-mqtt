<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use InvalidArgumentException;

/**
 * Provides a default implementation of the {@see Message} interface.
 */
class DefaultMessage implements Message
{
    /** @var string */
    private $topic;
    /** @var string */
    private $payload;
    /** @var bool */
    private $isRetained;
    /** @var bool */
    private $isDuplicate;
    /** @var int */
    private $qosLevel;

    /**
     * Constructs an instance of this class.
     *
     * @param string $topic
     * @param string $payload
     * @param int    $qosLevel
     * @param bool   $retain
     * @param bool   $isDuplicate
     */
    public function __construct(string $topic, string $payload = '', int $qosLevel = 0, bool $retain = false, bool $isDuplicate = false)
    {
        $this->assertValidQosLevel($qosLevel);

        $this->topic = $topic;
        $this->payload = $payload;
        $this->isRetained = $retain;
        $this->qosLevel = $qosLevel;
        $this->isDuplicate = $isDuplicate;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getQosLevel(): int
    {
        return $this->qosLevel;
    }

    public function isDuplicate(): bool
    {
        return $this->isDuplicate;
    }

    public function isRetained(): bool
    {
        return $this->isRetained;
    }

    public function withTopic(string $topic): Message
    {
        $result = clone $this;
        $result->topic = $topic;

        return $result;
    }

    public function withPayload(string $payload): Message
    {
        $result = clone $this;
        $result->payload = $payload;

        return $result;
    }

    public function withQosLevel(int $level): Message
    {
        $result = clone $this;
        $result->qosLevel = $level;

        return $result;
    }

    public function retain(): Message
    {
        $result = clone $this;
        $result->isRetained = true;

        return $result;
    }

    public function release(): Message
    {
        $result = clone $this;
        $result->isRetained = false;

        return $result;
    }

    public function duplicate(): Message
    {
        $result = clone $this;
        $result->isDuplicate = true;

        return $result;
    }

    public function original(): Message
    {
        $result = clone $this;
        $result->isDuplicate = false;

        return $result;
    }

    /**
     * Asserts that the given quality of service level is valid.
     *
     * @param int  $level
     *
     * @return void
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
