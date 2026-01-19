<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Validator;
use InvalidArgumentException;
use Override;

/**
 * Represents the SUBACK packet.
 */
class SubscribeResponsePacket extends BasePacket
{
    use IdentifiablePacket;

    /**
     * @var array<int, array<int, string>>
     */
    private const array QOS_LEVELS = [
        0 => ['Maximum QoS 0'],
        1 => ['Maximum QoS 1'],
        2 => ['Maximum QoS 2'],
        128 => ['Failure'],
    ];

    protected static int $packetType = Packet::TYPE_SUBACK;

    /**
     * @var array<int, int<0, 128>>
     */
    private array $returnCodes = [];

    #[Override]
    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength();

        $identifier = $stream->readWord();
        Validator::assertValidIdentifier($identifier, MalformedPacketException::class);
        $this->identifier = $identifier;

        $returnCodeLength = $this->remainingPacketLength - 2;

        for ($n = 0; $n < $returnCodeLength; $n++) {
            $returnCode = $stream->readByte();
            $this->assertValidReturnCode($returnCode);

            $this->returnCodes[] = $returnCode;
        }
    }

    #[Override]
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
     *
     * @param int<0, 128> $returnCode
     */
    public function isError(int $returnCode): bool
    {
        return $returnCode < 0 || $returnCode > 2;
    }

    /**
     * Indicates if the given return code is an error.
     *
     * @param int<0, 128> $returnCode
     */
    public function getReturnCodeName(int $returnCode): string
    {
        if (array_key_exists($returnCode, self::QOS_LEVELS)) {
            return self::QOS_LEVELS[$returnCode][0];
        }

        return sprintf('Unknown %02x', $returnCode);
    }

    /**
     * Returns the return codes.
     *
     * @return array<int, int<0, 128>>
     */
    public function getReturnCodes(): array
    {
        return $this->returnCodes;
    }

    /**
     * Sets the return codes.
     *
     * @param array<int, int<0, 128>> $value
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
     * @phpstan-return ($returnCode is 0|1|2|128 ? void : never)
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
