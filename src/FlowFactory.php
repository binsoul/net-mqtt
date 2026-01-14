<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Builds instances of the {@see Flow} interface.
 */
interface FlowFactory
{
    /**
     * @param int<0, 255> $returnCode
     */
    public function buildIncomingConnectFlow(Connection $connection, int $returnCode, bool $sessionPresent): Flow;

    public function buildIncomingDisconnectFlow(Connection $connection): Flow;

    public function buildIncomingPingFlow(): Flow;

    /**
     * @param int<1, 65535>|null $identifier
     */
    public function buildIncomingPublishFlow(Message $message, int $identifier = null): Flow;

    /**
     * @param array<int, Subscription> $subscriptions
     * @param array<int, int<0, 255>>  $returnCodes
     * @param int<1, 65535>            $identifier
     */
    public function buildIncomingSubscribeFlow(array $subscriptions, array $returnCodes, int $identifier): Flow;

    /**
     * @param array<int, Subscription> $subscriptions
     * @param int<1, 65535>            $identifier
     */
    public function buildIncomingUnsubscribeFlow(array $subscriptions, int $identifier): Flow;

    public function buildOutgoingConnectFlow(Connection $connection): Flow;

    public function buildOutgoingDisconnectFlow(Connection $connection): Flow;

    public function buildOutgoingPingFlow(): Flow;

    public function buildOutgoingPublishFlow(Message $message): Flow;

    /**
     * @param array<int, Subscription> $subscriptions
     */
    public function buildOutgoingSubscribeFlow(array $subscriptions): Flow;

    /**
     * @param array<int, Subscription> $subscriptions
     */
    public function buildOutgoingUnsubscribeFlow(array $subscriptions): Flow;
}
