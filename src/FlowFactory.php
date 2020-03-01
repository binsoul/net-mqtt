<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Flow;

/**
 * Builds instances of the {@see Flow} interface.
 */
interface FlowFactory
{
    /**
     * @return Flow
     */
    public function buildIncomingPingFlow(): Flow;

    /**
     * @param Message  $message
     * @param int|null $identifier
     *
     * @return Flow
     */
    public function buildIncomingPublishFlow(Message $message, int $identifier = null): Flow;

    /**
     * @param Connection $connection
     *
     * @return Flow
     */
    public function buildOutgoingConnectFlow(Connection $connection): Flow;

    /**
     * @param Connection $connection
     *
     * @return Flow
     */
    public function buildOutgoingDisconnectFlow(Connection $connection): Flow;

    /**
     * @return Flow
     */
    public function buildOutgoingPingFlow(): Flow;

    /**
     * @param Message $message
     *
     * @return Flow
     */
    public function buildOutgoingPublishFlow(Message $message): Flow;

    /**
     * @param Subscription[] $subscriptions
     *
     * @return Flow
     */
    public function buildOutgoingSubscribeFlow(array $subscriptions): Flow;

    /**
     * @param Subscription[] $subscriptions
     *
     * @return Flow
     */
    public function buildOutgoingUnsubscribeFlow(array $subscriptions): Flow;
}
