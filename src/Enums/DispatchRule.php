<?php

namespace Enjin\Platform\FuelTanks\Enums;

use Enjin\Platform\FuelTanks\Models\Substrate\FuelTankRules;
use Enjin\Platform\FuelTanks\Models\Substrate\MaxFuelBurnPerTransactionParams;
use Enjin\Platform\FuelTanks\Models\Substrate\RequireTokenParams;
use Enjin\Platform\FuelTanks\Models\Substrate\TankFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\UserFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCallersParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCollectionsParams;
use Enjin\Platform\Traits\EnumExtensions;

enum DispatchRule: string
{
    use EnumExtensions;

    case WHITELISTED_CALLERS = 'WhitelistedCallers';
    case WHITELISTED_COLLECTIONS = 'WhitelistedCollections';
    case MAX_FUEL_BURN_PER_TRANSACTION = 'MaxFuelBurnPerTransaction';
    case USER_FUEL_BUDGET = 'UserFuelBudget';
    case TANK_FUEL_BUDGET = 'TankFuelBudget';
    case REQUIRE_TOKEN = 'RequireToken';

    /**
     * Convert enum case to FuelTankRules.
     */
    public function toKind(): FuelTankRules
    {
        return match ($this) {
            self::WHITELISTED_CALLERS => new WhitelistedCallersParams(),
            self::WHITELISTED_COLLECTIONS => new WhitelistedCollectionsParams(),
            self::MAX_FUEL_BURN_PER_TRANSACTION => new MaxFuelBurnPerTransactionParams(''),
            self::USER_FUEL_BUDGET => new UserFuelBudgetParams('', ''),
            self::TANK_FUEL_BUDGET => new TankFuelBudgetParams('', ''),
            self::REQUIRE_TOKEN => new RequireTokenParams('', '')
        };
    }
}
