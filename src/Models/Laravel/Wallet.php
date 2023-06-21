<?php

namespace Enjin\Platform\FuelTanks\Models\Laravel;

use Enjin\Platform\Models\Laravel\Wallet as WalletBase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wallet extends WalletBase
{
    /**
     * The fuel tanks relationship.
     */
    public function fuelTanks(): BelongsToMany
    {
        return $this->belongsToMany(FuelTank::class, 'fuel_tank_accounts');
    }
}
