<?php

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Flow\IncomingPingFlow;
use BinSoul\Net\Mqtt\Flow\IncomingPublishFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingConnectFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingDisconnectFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingPingFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingPublishFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingSubscribeFlow;
use BinSoul\Net\Mqtt\Flow\OutgoingUnsubscribeFlow;

/**
 * Builds instances of the {@see Flow} interface.
 */
interface FlowFactory
{
    /**
     * @return IncomingPingFlow
     */
    public function buildIncomingPingFlow();

    /**
     * @param Message  $message
     * @param int|null $identifier
     *
     * @return IncomingPublishFlow
     */
    public function buildIncomingPublishFlow(Message $message, $identifier = null);

    /**
     * @param Connection $connection
     *
     * @return OutgoingConnectFlow
     */
    public function buildOutgoingConnectFlow(Connection $connection);

    /**
     * @param Connection $connection
     *
     * @return OutgoingDisconnectFlow
     */
    public function buildOutgoingDisconnectFlow(Connection $connection);

    /**
     * @return OutgoingPingFlow
     */
    public function buildOutgoingPingFlow();

    /**
     * @param Message $message
     *
     * @return OutgoingPublishFlow
     */
    public function buildOutgoingPublishFlow(Message $message);

    /**
     * @param Subscription[] $subscriptions
     *
     * @return OutgoingSubscribeFlow
     */
    public function buildOutgoingSubscribeFlow(array $subscriptions);

    /**
     * @param Subscription[] $subscriptions
     *
     * @return OutgoingUnsubscribeFlow
     */
    public function buildOutgoingUnsubscribeFlow(array $subscriptions);
}
