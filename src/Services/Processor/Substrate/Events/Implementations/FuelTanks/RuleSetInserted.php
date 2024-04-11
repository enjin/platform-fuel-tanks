<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Carbon\Carbon;
use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\RuleSetInserted as RuleSetInsertedEvent;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
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
        ray($event);

        if (!$event instanceof RuleSetInsertedPolkadart) {
            return;
        }

        $extrinsic = $block->extrinsics[$event->extrinsicIndex];
        $params = $extrinsic->params;
        $rules = Arr::get($params, 'rules', []);

        // Fail if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($event->tankId);

        ray($extrinsic);
        ray($params);
        throw new \Exception('Account rules are not supported yet');

//        throw new \Exception('Account rules are not supported yet');
        $insertRules = [];
        foreach ($rules as $rule) {
            $ruleName = array_key_first($rule);
            $ruleData = $rule[$ruleName];
            $insertRules[] = [
                'fuel_tank_id' => $fuelTank->id,
                'rule_set_id' => $event->ruleSetId,
                'rule' => $ruleName,
                'value' => is_string($ruleData) ? $ruleData : json_encode($ruleData),
                'is_frozen' => false,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ];
        }
        DispatchRule::insert($insertRules);

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
            $insertRules,
            $transaction
        );
    }
}
