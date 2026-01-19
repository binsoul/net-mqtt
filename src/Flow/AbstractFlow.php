<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Exception\UnknownPacketTypeException;
use BinSoul\Net\Mqtt\Flow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketFactory;

/**
 * Provides an abstract implementation of the {@see Flow} interface.
 */
abstract class AbstractFlow implements Flow
{
    private bool $isFinished = false;

    private bool $isSuccess = false;

    private mixed $result = null;

    private string $error = '';

    private PacketFactory $packetFactory;

    /**
     * Constructs an instance of this class.
     */
    public function __construct(PacketFactory $packetFactory)
    {
        $this->packetFactory = $packetFactory;
    }

    public function accept(Packet $packet): bool
    {
        return false;
    }

    public function next(Packet $packet): ?Packet
    {
        return null;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function isSuccess(): bool
    {
        return $this->isFinished && $this->isSuccess;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getErrorMessage(): string
    {
        return $this->error;
    }

    /**
     * Marks the flow as successful and sets the result.
     */
    protected function succeed(mixed $result = null): void
    {
        $this->isFinished = true;
        $this->isSuccess = true;
        $this->result = $result;
    }

    /**
     * Marks the flow as failed and sets the error message.
     */
    protected function fail(string $error = ''): void
    {
        $this->isFinished = true;
        $this->isSuccess = false;
        $this->error = $error;
    }

    /**
     * @phpstan-param Packet::TYPE_* $type
     *
     * @throws UnknownPacketTypeException
     */
    protected function generatePacket(int $type): Packet
    {
        return $this->packetFactory->build($type);
    }
}
