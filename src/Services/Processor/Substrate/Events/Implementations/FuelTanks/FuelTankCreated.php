<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FuelTankCreated as FuelTankCreatedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FuelTankCreated as FuelTankCreatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class FuelTankCreated implements SubstrateEvent
{
    /**
     * Handle the fuel tank created event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof FuelTankCreatedPolkadart) {
            return;
        }

        $extrinsic = $block->extrinsics[$event->extrinsicIndex];
        $params = $extrinsic->params;

        $providesDeposit = Arr::get($params, 'descriptor.provides_deposit');
        $reservesExistentialDeposit = Arr::get($params, 'descriptor.user_account_management.Some.tank_reserves_existential_deposit');
        $reservesAccountCreationDeposit = Arr::get($params, 'descriptor.user_account_management.Some.tank_reserves_account_creation_deposit');

        $owner = WalletService::firstOrStore(['account' => Account::parseAccount($event->owner)]);
        $fuelTank = FuelTank::create([
            'public_key' => Account::parseAccount($event->tankId),
            'name' => HexConverter::hexToString($event->tankName),
            'owner_wallet_id' => $owner->id,
            'reserves_existential_deposit' => $reservesExistentialDeposit,
            'reserves_account_creation_deposit' => $reservesAccountCreationDeposit,
            'provides_deposit' => $providesDeposit,
            'is_frozen' => false,
        ]);

        $accountRules = Arr::get($params, 'descriptor.account_rules', []);
        $insertAccountRules = [];
        foreach ($accountRules as $rule) {
            $ruleName = array_key_first($rule);
            $ruleData = $rule[$ruleName];
            $insertAccountRules[] = [
                'rule' => $ruleName,
                'value' => $ruleData,
            ];
        }
        $fuelTank->accountRules()->createMany($insertAccountRules);

        $dispatchRules = Arr::get($params, 'descriptor.rule_sets', []);
        $insertDispatchRules = [];
        foreach ($dispatchRules as $ruleSet) {
            $ruleSetId = $ruleSet[0];
            foreach ($ruleSet[1] as $rule) {
                $ruleName = array_key_first($rule);
                $ruleData = $rule[$ruleName];
                $insertDispatchRules[] = [
                    'rule_set_id' => $ruleSetId,
                    'rule' => $ruleName,
                    'value' => $ruleData,
                    'is_frozen' => false,
                ];
            }
        }
        $fuelTank->dispatchRules()->createMany($insertDispatchRules);

        Log::info(
            sprintf(
                'FuelTank %s (id: %s) was created.',
                $fuelTank->public_key,
                $fuelTank->id
            )
        );

        FuelTankCreatedEvent::safeBroadcast($fuelTank);
    }
}
