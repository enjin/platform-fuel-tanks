<?php

namespace Enjin\Platform\FuelTanks\Enums\Substrate;

use Enjin\Platform\Exceptions\PlatformException;

class StorageKey
{
    public function __construct(public StorageType $type, public string $value) {}

    public static function tanks(?string $value = null): self
    {
        return new self(StorageType::TANKS, $value ?? '0xb8ed204d2f9b209f43a0487b80cceca11dff2785cc2c6efead5657dc32a2065e');
    }

    public static function accounts(?string $value = null): self
    {
        return new self(StorageType::ACCOUNTS, $value ?? '0xb8ed204d2f9b209f43a0487b80cceca18ee7418a6531173d60d1f6a82d8f4d51');
    }

    /**
     * Get the parser for this storage key.
     */
    public function parser(): string
    {
        return match ($this->type) {
            StorageType::TANKS => 'tanksStorages',
            StorageType::ACCOUNTS => 'accountsStorages',
            default => throw new PlatformException('No parser for this storage key.'),
        };
    }

    /**
     * Get the parser facade for this storage key.
     */
    public function parserFacade(): string
    {
        return '\Facades\Enjin\Platform\FuelTanks\Services\Processor\Substrate\Parser';
    }
}
