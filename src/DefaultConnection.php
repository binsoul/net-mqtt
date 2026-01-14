<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use InvalidArgumentException;

/**
 * Provides a default implementation of the {@see Connection} interface.
 */
class DefaultConnection implements Connection
{
    private string $username;

    private string $password;

    private ?Message $will;

    private string $clientID;

    /**
     * @var int<0, 65535>
     */
    private int $keepAlive;

    private int $protocol;

    private bool $clean;

    /**
     * Constructs an instance of this class.
     *
     * @param int<0, 65535> $keepAlive
     */
    public function __construct(
        string $username = '',
        string $password = '',
        Message $will = null,
        string $clientID = '',
        int $keepAlive = 60,
        int $protocol = 4,
        bool $clean = true
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->will = $will;
        $this->clientID = $clientID;
        $this->keepAlive = $keepAlive;
        $this->protocol = $protocol;
        $this->clean = $clean;
    }

    public function getProtocol(): int
    {
        return $this->protocol;
    }

    public function getClientID(): string
    {
        return $this->clientID;
    }

    public function isCleanSession(): bool
    {
        return $this->clean;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getWill(): ?Message
    {
        return $this->will;
    }

    /**
     * @return int<0, 65535>
     */
    public function getKeepAlive(): int
    {
        return $this->keepAlive;
    }

    public function withProtocol(int $protocol): self
    {
        if ($protocol < 3 || $protocol > 4) {
            throw new InvalidArgumentException(sprintf('Expected protocol level 3 or 4 but got %d.', $protocol));
        }

        $result = clone $this;
        $result->protocol = $protocol;

        return $result;
    }

    public function withClientID(string $clientID): self
    {
        $result = clone $this;
        $result->clientID = $clientID;

        return $result;
    }

    public function withCredentials(string $username, string $password): self
    {
        $result = clone $this;
        $result->username = $username;
        $result->password = $password;

        return $result;
    }

    public function withWill(Message $will = null): self
    {
        $result = clone $this;
        $result->will = $will;

        return $result;
    }

    public function withKeepAlive(int $timeout): self
    {
        if ($timeout < 0 || $timeout > 0xFFFF) {
            throw new InvalidArgumentException(sprintf('Expected keep alive between 0 and 65535 but got %d.', $timeout));
        }

        $result = clone $this;
        $result->keepAlive = $timeout;

        return $result;
    }
}
