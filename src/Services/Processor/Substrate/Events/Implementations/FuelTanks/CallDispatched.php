<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\CallDispatched as CallDispatchedEvent;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\CallDispatched as CallDispatchedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Facades\Log;

class CallDispatched implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the call dispatched event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof CallDispatchedPolkadart) {
            return;
        }

        $account = WalletService::firstOrStore(['account' => $event->caller]);
        $fuelTank = $this->getFuelTank($event->tankId);

        Log::info(
            sprintf(
                'The caller %s (id: %s) has dispatched a call through FuelTank %s (id: %s).',
                $account->public_key,
                $account->id,
                $fuelTank->public_key,
                $fuelTank->id,
            )
        );

        CallDispatchedEvent::safeBroadcast($fuelTank, $account);
    }
}
