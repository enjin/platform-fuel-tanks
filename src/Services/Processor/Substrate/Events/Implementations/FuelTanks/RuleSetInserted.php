<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\RuleSetInserted as RuleSetInsertedEvent;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\RuleSetInserted as RuleSetInsertedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class RuleSetInserted extends FuelTankSubstrateEvent
{
    /** @var RuleSetInsertedPolkadart */
    protected Event $event;

    /**
     * Handle the rule set inserted event.
     *
     * @throws PlatformException
     */
    public function run(): void
    {
        $extrinsic = $this->block->extrinsics[$this->event->extrinsicIndex];
        $params = $extrinsic->params;
        $rules = Arr::get($params, 'rule_set.rules', []);

        // Fail if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($this->event->tankId);

        // Removes rules from that rule set id
        DispatchRule::where([
            'fuel_tank_id' => $fuelTank->id,
            'rule_set_id' => $this->event->ruleSetId,
        ])?->delete();

        $insertDispatchRules = [];
        $dispatchRule = (new DispatchRulesParams())->fromEncodable($this->event->ruleSetId, ['rules' => $rules])->toArray();

        foreach ($dispatchRule as $rule) {
            $insertDispatchRules[] = [
                'fuel_tank_id' => $fuelTank->id,
                'rule_set_id' => $this->event->ruleSetId,
                'rule' => array_key_first($rule),
                'value' => $rule[array_key_first($rule)],
                'is_frozen' => false,
            ];
        }

        $fuelTank->dispatchRules()->createMany($insertDispatchRules);
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'RuleSetInserted at FuelTank %s from transaction %s.',
                $this->event->tankId,
                $transaction?->transaction_chain_hash ?? 'unknown',
            )
        );
    }

    public function broadcast(): void
    {
        RuleSetInsertedEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
        );
    }
}
