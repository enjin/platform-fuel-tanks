<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FuelTankMutated as FuelTankMutatedEvent;
use Enjin\Platform\FuelTanks\Models\AccountRule;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FuelTankMutated as FuelTankMutatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;

class FuelTankMutated extends FuelTankSubstrateEvent
{
    /**
     * Handle the fuel tank mutated event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof FuelTankMutatedPolkadart) {
            return;
        }

        // Fail if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($event->tankId);

        if (!is_null($uac = $event->userAccountManagement)) {
            $fuelTank->reserves_existential_deposit = $this->getValue($uac, ['Some.tank_reserves_existential_deposit', 'tank_reserves_existential_deposit']);
            $fuelTank->reserves_account_creation_deposit = $this->getValue($uac, ['Some.tank_reserves_account_creation_deposit', 'tank_reserves_account_creation_deposit']);
        }

        if (!is_null($providesDeposit = $event->providesDeposit)) {
            $fuelTank->provides_deposit = $providesDeposit;
        }

        if (!is_null($accountRules = $event->accountRules)) {
            AccountRule::where('fuel_tank_id', $fuelTank->id)?->delete();

            $insertAccountRules = [];
            $rules = collect($accountRules)->collapse();
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

            $fuelTank->accountRules()->createMany($insertAccountRules);
        }

        $fuelTank->save();

        FuelTankMutatedEvent::safeBroadcast(
            $fuelTank,
            $this->getTransaction($block, $event->extrinsicIndex),
        );
    }
}
