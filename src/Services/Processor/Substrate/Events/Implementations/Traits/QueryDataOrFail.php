<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail as QueryDataOrFailBase;
use Enjin\Platform\Support\SS58Address;

trait QueryDataOrFail
{
    use QueryDataOrFailBase;

    /**
     * Get the fuel tank by the public key.
     */
    protected function getFuelTank(string $publicKey): FuelTank
    {
        if (!$fuelTank = FuelTank::where(['public_key' => SS58Address::getPublicKey($publicKey)])->first()) {
            throw new PlatformException(__('enjin-platform::traits.query_data_or_fail.unable_to_find_fuel_tank', ['class' => __CLASS__, 'publicKey' => $publicKey]));
        }

        return $fuelTank;
    }
}
