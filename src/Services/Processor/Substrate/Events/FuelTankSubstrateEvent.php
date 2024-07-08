<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\SS58Address;
use Illuminate\Database\Eloquent\Model;

abstract class FuelTankSubstrateEvent extends SubstrateEvent
{
    /**
     * Get the fuel tank by the public key.
     *
     * @throws PlatformException
     */
    protected function getFuelTank(string $publicKey): Model
    {
        if (!$fuelTank = FuelTank::where(['public_key' => SS58Address::getPublicKey($publicKey)])->first()) {
            throw new PlatformException(__('enjin-platform::traits.query_data_or_fail.unable_to_find_fuel_tank', ['class' => self::class, 'publicKey' => $publicKey]));
        }

        return $fuelTank;
    }
}
