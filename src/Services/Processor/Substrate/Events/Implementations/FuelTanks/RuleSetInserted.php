<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\RuleSetInserted as RuleSetInsertedEvent;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\RuleSetInserted as RuleSetInsertedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class RuleSetInserted extends FuelTankSubstrateEvent
{
    /**
     * Handle the rule set inserted event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof RuleSetInsertedPolkadart) {
            return;
        }

        $extrinsic = $block->extrinsics[$event->extrinsicIndex];
        $params = $extrinsic->params;
        $rules = Arr::get($params, 'rules', []);

        // Fail if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($event->tankId);

        // Removes rules from that rule set id
        DispatchRule::where([
            'fuel_tank_id' => $fuelTank->id,
            'rule_set_id' => $event->ruleSetId,
        ])?->delete();

        $insertDispatchRules = [];
        $dispatchRule = (new DispatchRulesParams())->fromEncodable($event->ruleSetId, ['rules' => $rules])->toArray();

        foreach ($dispatchRule as $rule) {
            $insertDispatchRules[] = [
                'fuel_tank_id' => $fuelTank->id,
                'rule_set_id' => $event->ruleSetId,
                'rule' => array_key_first($rule),
                'value' => $rule[array_key_first($rule)],
                'is_frozen' => false,
            ];
        }

        $fuelTank->dispatchRules()->createMany($insertDispatchRules);
        $transaction = $this->getTransaction($block, $event->extrinsicIndex);

        Log::info(
            sprintf(
                'RuleSetInserted at FuelTank %s (id: %s) from transaction %s (id: %s).',
                $fuelTank->public_key,
                $fuelTank->id,
                $transaction?->transaction_chain_hash ?? 'unknown',
                $transaction?->id ?? 'unknown',
            )
        );

        RuleSetInsertedEvent::safeBroadcast(
            $fuelTank,
            $insertDispatchRules,
            $transaction
        );
    }
}
