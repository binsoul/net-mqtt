<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Provides a default implementation of the {@see Message} interface.
 */
class DefaultMessage implements Message
{
    /**
     * @var non-empty-string
     */
    private string $topic;

    private string $payload;

    private bool $isRetained;

    private bool $isDuplicate;

    /**
     * @var int<0, 2>
     */
    private int $qosLevel;

    /**
     * Constructs an instance of this class.
     *
     * @param non-empty-string $topic
     * @param int<0, 2>        $qosLevel
     */
    public function __construct(string $topic, string $payload = '', int $qosLevel = 0, bool $retain = false, bool $isDuplicate = false)
    {
        Validator::assertValidTopic($topic);
        Validator::assertValidQosLevel($qosLevel);

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

    public function withTopic(string $topic): self
    {
        Validator::assertValidTopic($topic);

        $result = clone $this;
        $result->topic = $topic;

        return $result;
    }

    public function withPayload(string $payload): self
    {
        $result = clone $this;
        $result->payload = $payload;

        return $result;
    }

    public function withQosLevel(int $level): self
    {
        Validator::assertValidQosLevel($level);

        $result = clone $this;
        $result->qosLevel = $level;

        return $result;
    }

    public function retain(): self
    {
        $result = clone $this;
        $result->isRetained = true;

        return $result;
    }

    public function release(): self
    {
        $result = clone $this;
        $result->isRetained = false;

        return $result;
    }

    public function duplicate(): self
    {
        $result = clone $this;
        $result->isDuplicate = true;

        return $result;
    }

    public function original(): self
    {
        $result = clone $this;
        $result->isDuplicate = false;

        return $result;
    }
}
