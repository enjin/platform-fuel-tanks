<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FuelTankCreated as FuelTankCreatedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FuelTankCreated as FuelTankCreatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Enjin\Platform\Support\Account;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FuelTankCreated extends FuelTankSubstrateEvent
{
    /**
     * Handle the fuel tank created event.
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof FuelTankCreatedPolkadart) {
            return;
        }

        $extrinsic = $block->extrinsics[$event->extrinsicIndex];
        $params = $extrinsic->params;

        ray($event);
        ray($params);

        $providesDeposit = Arr::get($params, 'descriptor.provides_deposit');
        $reservesExistentialDeposit = $this->getValue($params, [
            'descriptor.user_account_management.Some.tank_reserves_existential_deposit',
            'descriptor.user_account_management.tank_reserves_existential_deposit',
        ]);

        $reservesAccountCreationDeposit = $this->getValue($params, [
            'descriptor.user_account_management.Some.tank_reserves_account_creation_deposit',
            'descriptor.user_account_management.tank_reserves_account_creation_deposit',
        ]);

        $owner = $this->firstOrStoreAccount($event->owner);
        $fuelTank = FuelTank::updateOrCreate(
            [
                'public_key' => Account::parseAccount($event->tankId),
            ],
            [
                'name' => $event->tankName,
                'owner_wallet_id' => $owner->id,
                'reserves_existential_deposit' => $reservesExistentialDeposit,
                'reserves_account_creation_deposit' => $reservesAccountCreationDeposit,
                'provides_deposit' => $providesDeposit,
                'is_frozen' => false,
            ]
        );

        $insertAccountRules = [];
        $accountRules = Arr::get($params, 'descriptor.account_rules', []);
        $rules = collect($accountRules)->collapse();

        if ($rules->isNotEmpty()) {
            $accountRules = (new AccountRulesParams())->fromEncodable($rules->toArray())->toArray();

            if (Arr::has($accountRules, 'WhitelistedCallers')) {
                $insertAccountRules[] = [
                    'rule' => 'WhitelistedCallers',
                    'value' => $accountRules['WhitelistedCallers'],
                ];
            }

            if (Arr::has($accountRules, 'RequireToken')) {
                $insertAccountRules[] = [
                    'rule' => 'RequireToken',
                    'value' => $accountRules['RequireToken'],
                ];
            }
        }

        ray($insertAccountRules);

        $fuelTank->accountRules()->createMany($insertAccountRules);



        $dispatchRules = Arr::get($params, 'descriptor.rule_sets', []);
        ray($dispatchRules);
        $insertDispatchRules = [];

        foreach ($dispatchRules as $ruleSet) {
            $ruleSetId = $ruleSet[0];
            $rules = collect($ruleSet[1])->toArray();
            ray($rules);

            $dispatchRule = (new DispatchRulesParams())->fromEncodable($ruleSetId, ['rules' => $rules])->toEncodable();
            foreach ($dispatchRule as $rule) {
                $insertDispatchRules[] = [
                    'rule_set_id' => $ruleSetId,
                    'rule' => array_key_first($rule),
                    'value' => $rule[array_key_first($rule)],
                    'is_frozen' => false,
                ];
                ray($insertDispatchRules);
            }


            ray($dispatchRule);
        }

        ray($insertDispatchRules);



//        foreach ($dispatchRules as $ruleSet) {
//            $ruleSetId = $ruleSet[0];
//            foreach ($ruleSet[1] as $rule) {
//                $ruleName = array_key_first($rule);
//                $ruleData = $rule[$ruleName];
//                $insertDispatchRules[] = [
//                    'rule_set_id' => $ruleSetId,
//                    'rule' => $ruleName,
//                    'value' => $ruleData,
//                    'is_frozen' => false,
//                ];
//                ray($ruleData);
//            }
//        }


//        throw new \Exception('Account rules are not supported yet');
        $fuelTank->dispatchRules()->createMany($insertDispatchRules);

        throw new \Exception('Account rules are not supported yet');


        $transaction = $this->getTransaction($block, $event->extrinsicIndex);

        Log::info(
            sprintf(
                'FuelTank %s (id: %s) was created from transaction %s (id: %s)',
                $fuelTank->public_key,
                $fuelTank->id,
                $transaction?->transaction_chain_hash ?? 'unknown',
                $transaction?->id ?? 'unknown'
            )
        );

        FuelTankCreatedEvent::safeBroadcast(
            $fuelTank,
            $transaction
        );
    }
}
