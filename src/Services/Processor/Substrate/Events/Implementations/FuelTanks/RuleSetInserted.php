<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Carbon\Carbon;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\RuleSetInserted as RuleSetInsertedEvent;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\RuleSetInserted as RuleSetInsertedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class RuleSetInserted implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the rule set inserted event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof RuleSetInsertedPolkadart) {
            return;
        }

        $extrinsic = $block->extrinsics[$event->extrinsicIndex];
        $params = $extrinsic->params;
        $rules = Arr::get($params, 'rules', []);
        $fuelTank = $this->getFuelTank($event->tankId);

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

        $daemonTransaction = Transaction::firstWhere(['transaction_chain_hash' => $extrinsic->hash]);

        if ($daemonTransaction) {
            Log::info(
                sprintf(
                    'RuleSetInserted at FuelTank %s (id: %s) from transaction %s (id: %s).',
                    $fuelTank->public_key,
                    $fuelTank->id,
                    $daemonTransaction->transaction_chain_hash,
                    $daemonTransaction->id
                )
            );
        } else {
            Log::info(
                sprintf(
                    'RuleSetInserted at FuelTank %s (id: %s) from unknown transaction.',
                    $fuelTank->public_key,
                    $fuelTank->id,
                )
            );
        }

        RuleSetInsertedEvent::safeBroadcast(
            $fuelTank,
            $insertRules,
            $daemonTransaction
        );
    }
}
