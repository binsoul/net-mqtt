<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use InvalidArgumentException;

/**
 * Provides a default implementation of the {@see Connection} interface.
 */
class DefaultConnection implements Connection
{
    /**
     * Constructs an instance of this class.
     *
     * @param int<0, 65535> $keepAlive
     * @param int<3, 4>     $protocol
     */
    public function __construct(
        private string $username = '',
        private string $password = '',
        private ?Message $will = null,
        private string $clientID = '',
        private int $keepAlive = 60,
        private int $protocol = 4,
        private bool $clean = true
    ) {
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

    public function withCleanSession(bool $clean): self
    {
        $result = clone $this;
        $result->clean = $clean;

        return $result;
    }

    public function withCredentials(string $username, string $password): self
    {
        $result = clone $this;
        $result->username = $username;
        $result->password = $password;

        return $result;
    }

    public function withWill(?Message $will = null): self
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
