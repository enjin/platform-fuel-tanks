<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FuelTankDestroyed as FuelTankDestroyedEvent;
use Enjin\Platform\FuelTanks\Models\Laravel\FuelTank;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FuelTankDestroyed as FuelTankDestroyedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Enjin\Platform\Support\Account;
use Illuminate\Support\Facades\Log;

class FuelTankDestroyed extends FuelTankSubstrateEvent
{
    /**
     * Handle the fuel tank destroyed event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof FuelTankDestroyedPolkadart) {
            return;
        }

        FuelTank::where([
            'public_key' => Account::parseAccount($event->tankId),
        ])?->delete();

        Log::info(
            sprintf(
                'FuelTank %s was destroyed.',
                $event->tankId,
            )
        );

        //        FuelTankDestroyedEvent::safeBroadcast(
        //            $fuelTank,
        //            $this->getTransaction($block, $event->extrinsicIndex),
        //        );
    }
}
