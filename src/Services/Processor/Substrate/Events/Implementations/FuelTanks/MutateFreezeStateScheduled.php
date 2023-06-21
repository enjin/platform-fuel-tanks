<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\MutateFreezeStateScheduled as MutateFreezeStateScheduledEvent;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\MutateFreezeStateScheduled as MutateFreezeStateScheduledPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;

class MutateFreezeStateScheduled implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the mutate freeze state scheduled event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof MutateFreezeStateScheduledPolkadart) {
            return;
        }

        $fuelTank = $this->getFuelTank($event->tankId);

        MutateFreezeStateScheduledEvent::safeBroadcast($fuelTank);
    }
}
