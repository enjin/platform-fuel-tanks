<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\RuleSetRemoved as RuleSetRemovedEvent;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\RuleSetRemoved as RuleSetRemovedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Illuminate\Support\Facades\Log;

class RuleSetRemoved implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the rule set removed event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof RuleSetRemovedPolkadart) {
            return;
        }

        $fuelTank = $this->getFuelTank($event->tankId);
        $rules = DispatchRule::where([
            'fuel_tank_id' => $fuelTank->id,
            'rule_set_id' => $event->ruleSetId,
        ])->delete();

        Log::info(
            sprintf(
                'The rule set %s from FuelTank %s (id %s) with %s rules was removed.',
                $event->ruleSetId,
                $fuelTank->tankId,
                $fuelTank->id,
                $rules
            )
        );

        RuleSetRemovedEvent::safeBroadcast($fuelTank, $event->ruleSetId);
    }
}
