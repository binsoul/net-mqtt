<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Represents the connection of a MQTT client.
 */
interface Connection
{
    /**
     * @return int<3, 4>
     */
    public function getProtocol(): int;

    public function getClientID(): string;

    public function isCleanSession(): bool;

    public function getUsername(): string;

    public function getPassword(): string;

    public function getWill(): ?Message;

    /**
     * @return int<0, 65535>
     */
    public function getKeepAlive(): int;

    /**
     * Returns a new connection with the given protocol.
     *
     * @param int<3, 4> $protocol
     *
     * @return static
     */
    public function withProtocol(int $protocol): self;

    /**
     * Returns a new connection with the given client id.
     *
     * @return static
     */
    public function withClientID(string $clientID): self;

    /**
     * Returns a new connection with the given credentials.
     *
     * @return static
     */
    public function withCredentials(string $username, string $password): self;

    /**
     * Returns a new connection with the given will.
     *
     * @return static
     */
    public function withWill(Message $will): self;

    /**
     * Returns a new connection with the given keep alive timeout.
     *
     * @param int<0, 65535> $timeout
     *
     * @return static
     */
    public function withKeepAlive(int $timeout): self;
}
