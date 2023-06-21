<?php

namespace Enjin\Platform\FuelTanks\Enums\Substrate;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\Traits\EnumExtensions;

enum StorageKey: string
{
    use EnumExtensions;

    case TANKS = '0xb8ed204d2f9b209f43a0487b80cceca11dff2785cc2c6efead5657dc32a2065e';
    case ACCOUNTS = '0xb8ed204d2f9b209f43a0487b80cceca18ee7418a6531173d60d1f6a82d8f4d51';

    /**
     * Get the parser for this storage key.
     */
    public function parser(): string
    {
        return match ($this) {
            self::TANKS => 'tanksStorages',
            self::ACCOUNTS => 'accountsStorages',
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
