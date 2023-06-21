<?php

namespace Enjin\Platform\FuelTanks\Enums;

use Enjin\Platform\FuelTanks\Models\Substrate\FuelTankRules;
use Enjin\Platform\FuelTanks\Models\Substrate\RequireTokenParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCallersParams;
use Enjin\Platform\Traits\EnumExtensions;

enum AccountRule: string
{
    use EnumExtensions;

    case WHITELISTED_CALLERS = 'WhitelistedCallers';
    case REQUIRE_TOKEN = 'RequireToken';

    /**
     * Convert enum case to FuelTankRules.
     */
    public function toKind(): FuelTankRules
    {
        return match ($this) {
            self::WHITELISTED_CALLERS => new WhitelistedCallersParams(),
            self::REQUIRE_TOKEN => new RequireTokenParams('', '')
        };
    }
}
