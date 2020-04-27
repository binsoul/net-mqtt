<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Represents the connection of a MQTT client.
 */
interface Connection
{
    public function getProtocol(): int;

    public function getClientID(): string;

    public function isCleanSession(): bool;

    public function getUsername(): string;

    public function getPassword(): string;

    public function getWill(): ?Message;

    public function getKeepAlive(): int;

    /**
     * Returns a new connection with the given protocol.
     *
     * @return self
     */
    public function withProtocol(int $protocol): Connection;

    /**
     * Returns a new connection with the given client id.
     *
     * @return self
     */
    public function withClientID(string $clientID): Connection;

    /**
     * Returns a new connection with the given credentials.
     *
     * @return self
     */
    public function withCredentials(string $username, string $password): Connection;

    /**
     * Returns a new connection with the given will.
     *
     * @return self
     */
    public function withWill(Message $will): Connection;

    /**
     * Returns a new connection with the given keep alive timeout.
     *
     * @return self
     */
    public function withKeepAlive(int $timeout): Connection;
}
