<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Exception\EndOfStreamException;
use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Exception\UnknownPacketTypeException;
use Throwable;

/**
 * Provides methods to parse a stream of bytes into packets.
 */
class StreamParser
{
    private PacketStream $buffer;

    private PacketFactory $factory;

    /**
     * @var callable
     */
    private $errorCallback;

    /**
     * Constructs an instance of this class.
     */
    public function __construct(PacketFactory $packetFactory = null)
    {
        $this->buffer = new PacketStream();
        $this->factory = $packetFactory ?? new DefaultPacketFactory();
    }

    /**
     * Registers an error callback.
     */
    public function onError(callable $callback): void
    {
        $this->errorCallback = $callback;
    }

    /**
     * Appends the given data to the internal buffer and parses it.
     *
     * @return Packet[]
     */
    public function push(string $data): array
    {
        $this->buffer->write($data);

        $result = [];

        while ($this->buffer->getRemainingBytes() > 0) {
            try {
                $type = $this->buffer->readByte() >> 4;
                $packet = $this->factory->build($type);
            } catch (UnknownPacketTypeException $e) {
                $this->handleError($e);

                continue;
            } catch (EndOfStreamException $e) {
                break;
            }

            $this->buffer->seek(-1);
            $position = $this->buffer->getPosition();

            try {
                $packet->read($this->buffer);
                $result[] = $packet;
                $this->buffer->cut();
            } catch (EndOfStreamException $e) {
                $this->buffer->setPosition($position);

                break;
            } catch (MalformedPacketException $e) {
                $this->handleError($e);
            }
        }

        return $result;
    }

    /**
     * Executes the registered error callback.
     */
    private function handleError(Throwable $exception): void
    {
        if ($this->errorCallback !== null) {
            $callback = $this->errorCallback;
            $callback($exception);
        }
    }
}
