<?php

namespace BinSoul\Net\Mqtt;

use BinSoul\Net\Mqtt\Exception\UnknownFlowCodeException;

/**
 * Builds instances of the {@see Flow} interface.
 */
class FlowFactory
{
    /**
     * Map of flow codes to flow classes.
     *
     * @var string[]
     */
    private $mapping;

    /**
     * Returns a default flows map.
     *
     * @return string[]
     */
    public static function getDefaultMapping()
    {
        return [
            Flow::CODE_CONNECT => Flow\OutgoingConnectFlow::class,
            Flow::CODE_DISCONNECT => Flow\OutgoingDisconnectFlow::class,
            Flow::CODE_PONG => Flow\IncomingPingFlow::class,
            Flow::CODE_PING => Flow\OutgoingPingFlow::class,
            Flow::CODE_PUBLISH => Flow\OutgoingPublishFlow::class,
            Flow::CODE_MESSAGE => Flow\IncomingPublishFlow::class,
            Flow::CODE_SUBSCRIBE => Flow\OutgoingSubscribeFlow::class,
            Flow::CODE_UNSUBSCRIBE => Flow\OutgoingUnsubscribeFlow::class,
        ];
    }

    /**
     * Constructs an instance of this class.
     *
     * @param array|null $mapping
     */
    public function __construct(array $mapping = null)
    {
        if ($mapping === null) {
            $this->mapping = static::getDefaultMapping();
        } else {
            $this->mapping = $mapping;
        }
    }

    /**
     * Builds a flow object for the given code.
     *
     * @param string $code
     * @param array ...$args
     *
     * @throws UnknownFlowCodeException
     *
     * @return Flow
     */
    public function build($code, ...$args)
    {
        if (!isset($this->mapping[$code])) {
            throw new UnknownFlowCodeException(sprintf('Unknown packet code "%s".', $code));
        }

        $class = $this->mapping[$code];

        return new $class(...$args);
    }
}
