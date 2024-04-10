<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\RuleSetRemoved as RuleSetRemovedEvent;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\RuleSetRemoved as RuleSetRemovedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class RuleSetRemoved extends FuelTankSubstrateEvent
{
    /**
     * Handle the rule set removed event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        ray($event);

        if (!$event instanceof RuleSetRemovedPolkadart) {
            return;
        }

        throw new \Exception('Account rules are not supported yet');
        // Fail if it doesn't find the fuel tank
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

        RuleSetRemovedEvent::safeBroadcast(
            $fuelTank,
            $event->ruleSetId,
            $this->getTransaction($block, $event->extrinsicIndex),
        );
    }
}
