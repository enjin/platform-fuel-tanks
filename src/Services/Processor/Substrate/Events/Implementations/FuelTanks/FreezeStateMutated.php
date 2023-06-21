<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FreezeStateMutated as FreezeStateMutatedEvent;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FreezeStateMutated as FreezeStateMutatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Illuminate\Support\Facades\Log;

class FreezeStateMutated implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the freeze state mutated event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof FreezeStateMutatedPolkadart) {
            return;
        }

        $fuelTank = $this->getFuelTank($event->tankId);

        if (!$event->ruleSetId) {
            $fuelTank->is_frozen = $event->isFrozen;
            $fuelTank->save();

            Log::info(
                sprintf(
                    'FuelTank %s (id: %s) was %s.',
                    $fuelTank->public_key,
                    $fuelTank->id,
                    $event->isFrozen ? 'frozen' : 'thawed',
                )
            );
        } else {
            DispatchRule::where([
                'fuel_tank_id' => $fuelTank->id,
                'rule_set_id' => $event->ruleSetId,
            ])->update(['is_frozen' => $event->isFrozen]);

            Log::info(
                sprintf(
                    'The rule set id %s of fuel tank %s (id: %s) was %s.',
                    $event->ruleSetId,
                    $fuelTank->public_key,
                    $fuelTank->id,
                    $event->isFrozen ? 'frozen' : 'thawed',
                )
            );
        }

        FreezeStateMutatedEvent::safeBroadcast($fuelTank);
    }
}
