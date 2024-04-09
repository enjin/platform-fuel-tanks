<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\CallDispatched as CallDispatchedEvent;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\CallDispatched as CallDispatchedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class CallDispatched extends FuelTankSubstrateEvent
{
    /**
     * Handle the call dispatched event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        ray($event);

        if (!$event instanceof CallDispatchedPolkadart) {
            return;
        }

        // Fail if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($event->tankId);
        $account = $this->firstOrStoreAccount($event->caller);

        Log::info(
            sprintf(
                'The caller %s (id: %s) has dispatched a call through FuelTank %s (id: %s).',
                $account->public_key,
                $account->id,
                $fuelTank->public_key,
                $fuelTank->id,
            )
        );

        CallDispatchedEvent::safeBroadcast(
            $fuelTank,
            $account,
            $this->getTransaction($block, $event->extrinsicIndex),
        );
    }
}
