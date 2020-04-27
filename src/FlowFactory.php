<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

/**
 * Builds instances of the {@see Flow} interface.
 */
interface FlowFactory
{
    public function buildIncomingPingFlow(): Flow;

    public function buildIncomingPublishFlow(Message $message, int $identifier = null): Flow;

    public function buildOutgoingConnectFlow(Connection $connection): Flow;

    public function buildOutgoingDisconnectFlow(Connection $connection): Flow;

    public function buildOutgoingPingFlow(): Flow;

    public function buildOutgoingPublishFlow(Message $message): Flow;

    /**
     * @param Subscription[] $subscriptions
     */
    public function buildOutgoingSubscribeFlow(array $subscriptions): Flow;

    /**
     * @param Subscription[] $subscriptions
     */
    public function buildOutgoingUnsubscribeFlow(array $subscriptions): Flow;
}
