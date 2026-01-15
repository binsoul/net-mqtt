<?php

declare(strict_types=1);

namespace BinSoul\Net\Mqtt;

use Exception;

/**
 * Provides a default implementation of the {@see PacketIdentifierGenerator} and the {@see ClientIdentifierGenerator} interface.
 */
class DefaultIdentifierGenerator implements PacketIdentifierGenerator, ClientIdentifierGenerator
{
    /**
     * @var int<0, 65535>
     */
    private static int $currentIdentifier = 0;

    public function __construct()
    {
        // Reset the current identifier if an instance is created
        self::$currentIdentifier = 0;
    }

    /**
     * @return int<1, 65535>
     */
    public function generatePacketIdentifier(): int
    {
        return self::buildPacketIdentifier();
    }

    public function generateClientIdentifier(): string
    {
        return self::buildClientIdentifier();
    }

    /**
     * @return int<1, 65535>
     */
    public static function buildPacketIdentifier(): int
    {
        self::$currentIdentifier = (self::$currentIdentifier + 1) & 0xFFFF;

        if (self::$currentIdentifier === 0) {
            self::$currentIdentifier = 1;
        }

        return self::$currentIdentifier;
    }

    public static function buildClientIdentifier(): string
    {
        try {
            $data = random_bytes(8);
        } catch (Exception $exception) {
            $hash = md5(uniqid((string) microtime(true), true));
            $bytes = hex2bin($hash);

            if ($bytes === false) {
                $bytes = $hash;
            }

            $data = substr($bytes, 0, 8);
        }

        return 'binsoul' . bin2hex($data);
    }
}
