<?php

namespace BinSoul\Test\Net\Mqtt\Flow;

use BinSoul\Net\Mqtt\DefaultConnection;
use BinSoul\Net\Mqtt\DefaultIdentifierGenerator;
use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Exception\UnknownFlowCodeException;
use BinSoul\Net\Mqtt\Flow;
use BinSoul\Net\Mqtt\FlowFactory;

class FlowFactoryTest extends \PHPUnit_Framework_TestCase
{
    function test_default_flows()
    {
        $factory = new FlowFactory();

        $incomingPingFlow = $factory->build(Flow::CODE_PONG);
        $this->assertInstanceOf(Flow\IncomingPingFlow::class, $incomingPingFlow);

        $incomingPublishFlow = $factory->build(Flow::CODE_MESSAGE, new DefaultMessage('foo'));
        $this->assertInstanceOf(Flow\IncomingPublishFlow::class, $incomingPublishFlow);

        $outgoingConnectFlow = $factory->build(Flow::CODE_CONNECT, new DefaultConnection(), new DefaultIdentifierGenerator());
        $this->assertInstanceOf(Flow\OutgoingConnectFlow::class, $outgoingConnectFlow);

        $outgoingDisconnectFlow = $factory->build(Flow::CODE_DISCONNECT, new DefaultConnection());
        $this->assertInstanceOf(Flow\OutgoingDisconnectFlow::class, $outgoingDisconnectFlow);

        $outgoingPingFlow = $factory->build(Flow::CODE_PING);
        $this->assertInstanceOf(Flow\OutgoingPingFlow::class, $outgoingPingFlow);

        $outgoingPublishFlow = $factory->build(Flow::CODE_PUBLISH, new DefaultMessage('foo'), new DefaultIdentifierGenerator());
        $this->assertInstanceOf(Flow\OutgoingPublishFlow::class, $outgoingPublishFlow);

        $outgoingSubscribeFlow = $factory->build(Flow::CODE_SUBSCRIBE, ['foo'], new DefaultIdentifierGenerator());
        $this->assertInstanceOf(Flow\OutgoingSubscribeFlow::class, $outgoingSubscribeFlow);

        $outgoingUnsubscribeFlow = $factory->build(Flow::CODE_UNSUBSCRIBE, ['foo'], new DefaultIdentifierGenerator());
        $this->assertInstanceOf(Flow\OutgoingUnsubscribeFlow::class, $outgoingUnsubscribeFlow);
    }

    function test_unknown_code()
    {
        $factory = new FlowFactory();

        $this->expectException(UnknownFlowCodeException::class);
        $factory->build('unknown');
    }

    function test_mapping_override()
    {
        $factory = new FlowFactory(array_merge(FlowFactory::getDefaultMapping(), [
            Flow::CODE_PING => Flow\IncomingPingFlow::class,
        ]));

        $outgoingPingFlow = $factory->build(Flow::CODE_PING);
        $this->assertInstanceOf(Flow\IncomingPingFlow::class, $outgoingPingFlow);
    }
}
