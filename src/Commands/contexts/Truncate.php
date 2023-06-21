<?php

namespace Enjin\Platform\FuelTanks\Commands\contexts;

class Truncate
{
    /**
     * Returns the tables to truncate.
     */
    public static function tables(): array
    {
        return [
            'fuel_tanks',
            'fuel_tank_account_rules',
            'fuel_tank_dispatch_rules',
            'fuel_tank_accounts',
        ];
    }
}
