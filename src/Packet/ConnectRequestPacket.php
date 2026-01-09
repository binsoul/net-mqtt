<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt\Packet;

use BinSoul\Net\Mqtt\Exception\MalformedPacketException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketStream;
use Exception;
use InvalidArgumentException;

/**
 * Represents the CONNECT packet.
 */
class ConnectRequestPacket extends BasePacket
{
    protected static int $packetType = Packet::TYPE_CONNECT;

    protected string $clientID = '';

    private int $protocolLevel = 4;

    private string $protocolName = 'MQTT';

    private int $flags = 2;

    private int $keepAlive = 60;

    private string $willTopic = '';

    private string $willMessage = '';

    private string $username = '';

    private string $password = '';

    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength();

        $this->protocolName = $stream->readString();
        $this->protocolLevel = $stream->readByte();
        $this->flags = $stream->readByte();
        $this->keepAlive = $stream->readWord();
        $this->clientID = $stream->readString();

        if ($this->hasWill()) {
            $this->willTopic = $stream->readString();
            $this->willMessage = $stream->readString();
        }

        if ($this->hasUsername()) {
            $this->username = $stream->readString();
        }

        if ($this->hasPassword()) {
            $this->password = $stream->readString();
        }

        $this->assertValidWill();
        $this->assertValidString($this->clientID);
        $this->assertValidString($this->willTopic);
        $this->assertValidString($this->username);
    }

    public function write(PacketStream $stream): void
    {
        if ($this->clientID === '') {
            try {
                $this->clientID = 'BinSoul' . random_int(100000, 999999);
            } catch (Exception $e) {
                $this->clientID = 'BinSoul' . mt_rand(100000, 999999);
            }
        }

        $data = new PacketStream();

        $data->writeString($this->protocolName);
        $data->writeByte($this->protocolLevel);
        $data->writeByte($this->flags);
        $data->writeWord($this->keepAlive);
        $data->writeString($this->clientID);

        if ($this->hasWill()) {
            $data->writeString($this->willTopic);
            $data->writeString($this->willMessage);
        }

        if ($this->hasUsername()) {
            $data->writeString($this->username);
        }

        if ($this->hasPassword()) {
            $data->writeString($this->password);
        }

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the protocol level.
     */
    public function getProtocolLevel(): int
    {
        return $this->protocolLevel;
    }

    /**
     * Sets the protocol level.
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
        return $this->clientID;
    }

    /**
     * Sets the client id.
     */
    public function setClientID(string $value): void
    {
        $this->clientID = $value;
    }

    /**
     * Returns the keep alive time in seconds.
     */
    public function getKeepAlive(): int
    {
        return $this->keepAlive;
    }

    /**
     * Sets the keep alive time in seconds.
     *
     * @throws InvalidArgumentException
     */
    public function setKeepAlive(int $value): void
    {
        if ($value > 65535) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected a keep alive time lower than 65535 but got %d.',
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
        if ($value) {
            $this->flags |= 2;
        } else {
            $this->flags &= ~2;
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
     */
    public function getWillQosLevel(): int
    {
        return ($this->flags & 24) >> 3;
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
        return $this->willTopic;
    }

    /**
     * Returns the will message.
     */
    public function getWillMessage(): string
    {
        return $this->willMessage;
    }

    /**
     * Sets the will.
     *
     * @throws InvalidArgumentException
     */
    public function setWill(string $topic, string $message, int $qosLevel = 0, bool $isRetained = false): void
    {
        if ($topic === '') {
            throw new InvalidArgumentException('The topic must not be empty.');
        }

        if ($message === '') {
            throw new InvalidArgumentException('The message must not be empty.');
        }

        try {
            $this->assertValidString($topic);
            $this->assertValidStringLength($message);
            $this->assertValidQosLevel($qosLevel);
        } catch (MalformedPacketException $malformedPacketException) {
            throw new InvalidArgumentException($malformedPacketException->getMessage(), $malformedPacketException->getCode(), $malformedPacketException);
        }

        $this->willTopic = $topic;
        $this->willMessage = $message;

        $this->flags |= 4;
        $this->flags |= ($qosLevel << 3);

        if ($isRetained) {
            $this->flags |= 32;
        } else {
            $this->flags &= ~32;
        }
    }

    /**
     * Removes the will.
     */
    public function removeWill(): void
    {
        $this->flags &= ~60;
        $this->willTopic = '';
        $this->willMessage = '';
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
        return $this->username;
    }

    /**
     * Sets the username.
     */
    public function setUsername(string $value): void
    {
        try {
            $this->assertValidString($value);
        } catch (MalformedPacketException $malformedPacketException) {
            throw new InvalidArgumentException($malformedPacketException->getMessage(), $malformedPacketException->getCode(), $malformedPacketException);
        }

        $this->username = $value;

        if ($this->username !== '') {
            $this->flags |= 64;
        } else {
            $this->flags &= ~64;
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
        return $this->password;
    }

    /**
     * Sets the password.
     *
     * @throws InvalidArgumentException
     */
    public function setPassword(string $value): void
    {
        try {
            $this->assertValidStringLength($value);
        } catch (MalformedPacketException $malformedPacketException) {
            throw new InvalidArgumentException($malformedPacketException->getMessage(), $malformedPacketException->getCode(), $malformedPacketException);
        }

        $this->password = $value;

        if ($this->password !== '') {
            $this->flags |= 128;
        } else {
            $this->flags &= ~128;
        }
    }

    /**
     * Asserts that all will flags and quality of service are correct.
     *
     * @throws MalformedPacketException
     */
    private function assertValidWill(): void
    {
        if ($this->hasWill()) {
            $this->assertValidQosLevel($this->getWillQosLevel());
        } else {
            if ($this->getWillQosLevel() > 0) {
                throw new MalformedPacketException(
                    sprintf(
                        'Expected a will quality of service level of zero but got %d.',
                        $this->getWillQosLevel()
                    )
                );
            }

            if ($this->isWillRetained()) {
                throw new MalformedPacketException('There is not will but the will retain flag is set.');
            }
        }
    }
}
