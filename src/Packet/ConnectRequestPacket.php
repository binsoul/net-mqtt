<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\DefaultIdentifierGenerator;
use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use BinSoul\Net\Mqtt\Validator;
use InvalidArgumentException;

/**
 * Represents the CONNECT packet.
 */
class ConnectRequestPacket extends BasePacket
{
    protected static int $packetType = Packet::TYPE_CONNECT;

    protected ?string $clientID = null;

    /**
     * @var int<3, 4>
     */
    private int $protocolLevel = 4;

    private string $protocolName = 'MQTT';

    /**
     * @var int<0, 255>
     */
    private int $flags = 2;

    /**
     * @var int<0, 65535>
     */
    private int $keepAlive = 60;

    /**
     * @var non-empty-string|null
     */
    private ?string $willTopic = null;

    private ?string $willMessage = null;

    private ?string $username = null;

    private ?string $password = null;

    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength();

        $this->protocolName = $stream->readString();
        $protocolLevel = $stream->readByte();

        if ($protocolLevel < 3 || $protocolLevel > 4) {
            throw new MalformedPacketException(sprintf('Expected protocol level 3 or 4 but got %d.', $protocolLevel));
        }

        $this->protocolLevel = $protocolLevel;

        $this->flags = $stream->readByte();
        $this->keepAlive = $stream->readWord();
        $this->clientID = $stream->readString();
        Validator::assertValidString($this->clientID, MalformedPacketException::class);

        $this->assertValidWill();
        $this->willTopic = null;
        $this->willMessage = null;

        if ($this->hasWill()) {
            $willTopic = $stream->readString();
            Validator::assertValidTopic($willTopic, MalformedPacketException::class);
            $this->willTopic = $willTopic;

            $this->willMessage = $stream->readString();
        }

        $this->username = null;

        if ($this->hasUsername()) {
            $this->username = $stream->readString();
            Validator::assertValidString($this->username, MalformedPacketException::class);
        }

        $this->password = null;

        if ($this->hasPassword()) {
            $this->password = $stream->readString();
        }
    }

    public function write(PacketStream $stream): void
    {
        if ($this->clientID === null) {
            $this->clientID = DefaultIdentifierGenerator::buildClientIdentifier();
        }

        $data = new PacketStream();

        $data->writeString($this->protocolName);
        $data->writeByte($this->protocolLevel);
        $data->writeByte($this->flags);
        $data->writeWord($this->keepAlive);
        $data->writeString($this->clientID);

        if ($this->hasWill()) {
            $data->writeString($this->willTopic ?? '');
            $data->writeString($this->willMessage ?? '');
        }

        if ($this->hasUsername()) {
            $data->writeString($this->username ?? '');
        }

        if ($this->hasPassword()) {
            $data->writeString($this->password ?? '');
        }

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the protocol level.
     *
     * @return int<3, 4>
     */
    public function getProtocolLevel(): int
    {
        return $this->protocolLevel;
    }

    /**
     * Sets the protocol level.
     *
     * @param int<3, 4> $value
     *
     * @throws InvalidArgumentException
     */
    public function setProtocolLevel(int $value): void
    {
        if ($value < 3 || $value > 4) {
            throw new InvalidArgumentException(sprintf('Unknown protocol level %d.', $value));
        }

        $this->protocolLevel = $value;

        if ($this->protocolLevel === 3) {
            $this->protocolName = 'MQIsdp';
        } elseif ($this->protocolLevel === 4) {
            $this->protocolName = 'MQTT';
        }
    }

    /**
     * Returns the client id.
     */
    public function getClientID(): string
    {
        return $this->clientID ?? '';
    }

    /**
     * Sets the client id.
     */
    public function setClientID(string $value): void
    {
        $this->clientID = $value;

        if ($this->clientID === '') {
            $this->setCleanSession(true);
        }
    }

    /**
     * Returns the keep alive time in seconds.
     *
     * @return int<0, 65535>
     */
    public function getKeepAlive(): int
    {
        return $this->keepAlive;
    }

    /**
     * Sets the keep alive time in seconds.
     *
     * @param int<0, 65535> $value
     *
     * @throws InvalidArgumentException
     */
    public function setKeepAlive(int $value): void
    {
        if ($value < 0 || $value > 65535) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected a keep alive time between 0 and 65535 but got %d.',
                    $value
                )
            );
        }

        $this->keepAlive = $value;
    }

    /**
     * Indicates if the clean session flag is set.
     */
    public function isCleanSession(): bool
    {
        return ($this->flags & 2) === 2;
    }

    /**
     * Changes the clean session flag.
     */
    public function setCleanSession(bool $value): void
    {
        if ($this->clientID === '') {
            $value = true;
        }

        if ($value) {
            $this->flags = ($this->flags | 2) & 0xFF;
        } else {
            $this->flags = ($this->flags & ~2) & 0xFF;
        }
    }

    /**
     * Indicates if a will is set.
     */
    public function hasWill(): bool
    {
        return ($this->flags & 4) === 4;
    }

    /**
     * Returns the desired quality of service level of the will.
     *
     * @return int<0, 2>
     */
    public function getWillQosLevel(): int
    {
        $level = ($this->flags & 24) >> 3;

        return $level > 2 ? 0 : $level;
    }

    /**
     * Indicates if the will should be retained.
     */
    public function isWillRetained(): bool
    {
        return ($this->flags & 32) === 32;
    }

    /**
     * Returns the will topic.
     */
    public function getWillTopic(): string
    {
        return $this->willTopic ?? '';
    }

    /**
     * Returns the will message.
     */
    public function getWillMessage(): string
    {
        return $this->willMessage ?? '';
    }

    /**
     * Sets the will.
     *
     * @param int<0, 2> $qosLevel
     *
     * @throws InvalidArgumentException
     */
    public function setWill(string $topic, string $message, int $qosLevel = 0, bool $isRetained = false): void
    {
        Validator::assertValidTopic($topic);
        Validator::assertValidStringLength($message);
        Validator::assertValidQosLevel($qosLevel);

        $this->willTopic = $topic;
        $this->willMessage = $message;

        $this->flags = ($this->flags | 4) & 0xFF;
        $this->flags = ($this->flags | ($qosLevel << 3)) & 0xFF;

        if ($isRetained) {
            $this->flags = ($this->flags | 32) & 0xFF;
        } else {
            $this->flags = ($this->flags & ~32) & 0xFF;
        }
    }

    /**
     * Removes the will.
     */
    public function removeWill(): void
    {
        $this->flags = ($this->flags & ~60) & 0xFF;
        $this->willTopic = null;
        $this->willMessage = null;
    }

    /**
     * Indicates if a username is set.
     */
    public function hasUsername(): bool
    {
        return (bool) ($this->flags & 64);
    }

    /**
     * Returns the username.
     */
    public function getUsername(): string
    {
        return $this->username ?? '';
    }

    /**
     * Sets the username.
     */
    public function setUsername(string $value): void
    {
        Validator::assertValidString($value);

        $this->username = $value;

        if ($this->username !== '') {
            $this->flags = ($this->flags | 64) & 0xFF;
        } else {
            $this->flags = ($this->flags & ~64) & 0xFF;
        }
    }

    /**
     * Indicates if a password is set.
     */
    public function hasPassword(): bool
    {
        return (bool) ($this->flags & 128);
    }

    /**
     * Returns the password.
     */
    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    /**
     * Sets the password.
     *
     * @throws InvalidArgumentException
     */
    public function setPassword(string $value): void
    {
        Validator::assertValidStringLength($value);

        $this->password = $value;

        if ($this->password !== '') {
            $this->flags = ($this->flags | 128) & 0xFF;
        } else {
            $this->flags = ($this->flags & ~128) & 0xFF;
        }
    }

    /**
     * Asserts that all will flags and quality of service are correct.
     *
     * @throws MalformedPacketException
     */
    private function assertValidWill(): void
    {
        $willQosLevel = ($this->flags & 24) >> 3;

        if ($this->hasWill()) {
            Validator::assertValidQosLevel($willQosLevel, MalformedPacketException::class);
        } else {
            if ($willQosLevel > 0) {
                throw new MalformedPacketException(
                    sprintf(
                        'Expected a will quality of service level of zero but got %d.',
                        $willQosLevel
                    )
                );
            }

            if ($this->isWillRetained()) {
                throw new MalformedPacketException('There is not will but the will retain flag is set.');
            }
        }
    }
}
