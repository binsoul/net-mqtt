<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use InvalidArgumentException;

/**
 * Represents the SUBACK packet.
 */
class SubscribeResponsePacket extends BasePacket
{
    use IdentifiablePacket;

    protected static int $packetType = Packet::TYPE_SUBACK;

    /**
     * @var string[][]
     */
    private static array $qosLevels = [
        0 => ['Maximum QoS 0'],
        1 => ['Maximum QoS 1'],
        2 => ['Maximum QoS 2'],
        128 => ['Failure'],
    ];

    /**
     * @var int[]
     */
    private array $returnCodes = [];

    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength();

        $this->identifier = $stream->readWord();

        $returnCodeLength = $this->remainingPacketLength - 2;

        for ($n = 0; $n < $returnCodeLength; $n++) {
            $returnCode = $stream->readByte();
            $this->assertValidReturnCode($returnCode);

            $this->returnCodes[] = $returnCode;
        }
    }

    public function write(PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeWord($this->generateIdentifier());

        foreach ($this->returnCodes as $returnCode) {
            $data->writeByte($returnCode);
        }

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Indicates if the given return code is an error.
     */
    public function isError(int $returnCode): bool
    {
        return $returnCode === 128;
    }

    /**
     * Indicates if the given return code is an error.
     */
    public function getReturnCodeName(int $returnCode): string
    {
        if (isset(self::$qosLevels[$returnCode])) {
            return self::$qosLevels[$returnCode][0];
        }

        return 'Unknown ' . $returnCode;
    }

    /**
     * Returns the return codes.
     *
     * @return int[]
     */
    public function getReturnCodes(): array
    {
        return $this->returnCodes;
    }

    /**
     * Sets the return codes.
     *
     * @param int[] $value
     *
     * @throws InvalidArgumentException
     */
    public function setReturnCodes(array $value): void
    {
        foreach ($value as $index => $returnCode) {
            try {
                $this->assertValidReturnCode($returnCode);
            } catch (MalformedPacketException $e) {
                throw new InvalidArgumentException(sprintf('Return code index %s: %s', $index, $e->getMessage()), $e->getCode(), $e);
            }
        }

        $this->returnCodes = $value;
    }

    /**
     * Asserts that a return code is valid.
     *
     * @throws MalformedPacketException
     */
    private function assertValidReturnCode(int $returnCode): void
    {
        if (! in_array($returnCode, [0, 1, 2, 128], true)) {
            throw new MalformedPacketException(sprintf('Malformed return code %02x.', $returnCode));
        }
    }
}
