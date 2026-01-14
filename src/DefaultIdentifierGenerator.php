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
    private int $currentIdentifier = 0;

    /**
     * @return int<1, 65535>
     */
    public function generatePacketIdentifier(): int
    {
        $this->currentIdentifier = ($this->currentIdentifier + 1) & 0xFFFF;

        if ($this->currentIdentifier === 0) {
            $this->currentIdentifier = 1;
        }

        return $this->currentIdentifier;
    }

    public function generateClientIdentifier(): string
    {
        try {
            $data = random_bytes(9);
        } catch (Exception $exception) {
            $hash = md5(uniqid((string) microtime(true), true));
            $bytes = hex2bin($hash);

            if ($bytes === false) {
                $bytes = $hash;
            }

            $data = substr($bytes, 0, 9);
        }

        return 'BNMCR' . bin2hex($data);
    }
}
