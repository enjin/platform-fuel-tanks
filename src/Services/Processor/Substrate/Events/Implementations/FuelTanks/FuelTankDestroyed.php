<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FuelTankDestroyed as FuelTankDestroyedEvent;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FuelTankDestroyed as FuelTankDestroyedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Illuminate\Support\Facades\Log;

class FuelTankDestroyed implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the fuel tank destroyed event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof FuelTankDestroyedPolkadart) {
            return;
        }

        $fuelTank = $this->getFuelTank(
            $event->tankId
        );
        $fuelTank->delete();

        Log::info(
            sprintf(
                'FuelTank %s (id: %s) was destroyed.',
                $fuelTank->public_key,
                $fuelTank->id,
            )
        );

        FuelTankDestroyedEvent::safeBroadcast($fuelTank);
    }
}
