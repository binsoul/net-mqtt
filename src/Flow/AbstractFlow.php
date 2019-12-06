<?php

namespace BinSoul\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\Flow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketFactory;

/**
 * Provides an abstract implementation of the {@see Flow} interface.
 */
abstract class AbstractFlow implements Flow
{
    /** @var bool */
    private $isFinished = false;
    /** @var bool */
    private $isSuccess = false;
    /** @var mixed */
    private $result;
    /** @var string */
    private $error = '';
    /** @var PacketFactory */
    private $packetFactory;

    /**
     * Constructs an instance of this class.
     *
     * @param PacketFactory $packetFactory
     */
    public function __construct(PacketFactory $packetFactory)
    {
        $this->packetFactory = $packetFactory;
    }

    public function accept(Packet $packet)
    {
        return false;
    }

    public function next(Packet $packet)
    {
    }

    public function isFinished()
    {
        return $this->isFinished;
    }

    public function isSuccess()
    {
        return $this->isFinished && $this->isSuccess;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getErrorMessage()
    {
        return $this->error;
    }

    /**
     * Marks the flow as successful and sets the result.
     *
     * @param mixed|null $result
     */
    protected function succeed($result = null)
    {
        $this->isFinished = true;
        $this->isSuccess = true;
        $this->result = $result;
    }

    /**
     * Marks the flow as failed and sets the error message.
     *
     * @param string $error
     */
    protected function fail($error = '')
    {
        $this->isFinished = true;
        $this->isSuccess = false;
        $this->error = $error;
    }

    /**
     * @param int $type
     *
     * @return Packet
     */
    protected function generatePacket($type)
    {
        return $this->packetFactory->build($type);
    }
}
