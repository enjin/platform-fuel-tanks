<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Enums\CoveragePolicy;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FuelTankCreated as FuelTankCreatedEvent;
use Enjin\Platform\FuelTanks\Models\Laravel\FuelTank;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FuelTankCreated as FuelTankCreatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Enjin\Platform\Support\Account;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class FuelTankCreated extends FuelTankSubstrateEvent
{
    /** @var FuelTankCreatedPolkadart */
    protected Event $event;

    protected ?FuelTank $fuelTankCreated = null;

    /**
     * Handle the fuel tank created event.
     */
    public function run(): void
    {
        $extrinsic = $this->block->extrinsics[$this->event->extrinsicIndex];
        $params = $extrinsic->params;

        $reservesAccountCreationDeposit = $this->getValue($params, 'descriptor.user_account_management.tank_reserves_account_creation_deposit');
        $owner = $this->firstOrStoreAccount($this->event->owner);
        $this->extra = ['tank_owner' => $this->event->owner];

        $this->fuelTankCreated = FuelTank::updateOrCreate(
            [
                'public_key' => Account::parseAccount($this->event->tankId),
            ],
            [
                'name' => $this->event->tankName,
                'owner_wallet_id' => $owner->id,
                'coverage_policy' => CoveragePolicy::from($this->getValue($params, 'descriptor.coverage_policy'))->name,
                'reserves_account_creation_deposit' => $reservesAccountCreationDeposit,
                'is_frozen' => false,
            ]
        );

        $insertAccountRules = [];
        $accountRules = Arr::get($params, 'descriptor.account_rules', []);
        $rules = collect($accountRules)->collapse();

        if ($rules->isNotEmpty()) {
            $accountRules = (new AccountRulesParams())->fromEncodable($rules->toArray())->toArray();

            if (!empty($accountRules['WhitelistedCallers'])) {
                $insertAccountRules[] = [
                    'rule' => 'WhitelistedCallers',
                    'value' => $accountRules['WhitelistedCallers'],
                ];
            }

            if (!empty($accountRules['RequireToken'])) {
                $insertAccountRules[] = [
                    'rule' => 'RequireToken',
                    'value' => $accountRules['RequireToken'],
                ];
            }
        }

        $this->fuelTankCreated->accountRules()->createMany($insertAccountRules);

        $dispatchRules = Arr::get($params, 'descriptor.rule_sets', []);
        $insertDispatchRules = [];

        foreach ($dispatchRules as $ruleSet) {
            $ruleSetId = $ruleSet[0];
            $rules = collect($ruleSet[1])->toArray();

            $dispatchRule = (new DispatchRulesParams())->fromEncodable($ruleSetId, ['rules' => $rules])->toArray();
            foreach ($dispatchRule as $rule) {
                $insertDispatchRules[] = [
                    'rule_set_id' => $ruleSetId,
                    'rule' => array_key_first($rule),
                    'value' => $rule[array_key_first($rule)],
                    'is_frozen' => false,
                ];
            }
        }

        $this->fuelTankCreated->dispatchRules()->createMany($insertDispatchRules);
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'FuelTank %s was created.',
                $this->event->tankId,
            )
        );
    }

    public function broadcast(): void
    {
        FuelTankCreatedEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
            $this->fuelTankCreated,
        );
    }
}
