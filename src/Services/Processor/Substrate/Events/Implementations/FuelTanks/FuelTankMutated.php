<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\FuelTankMutated as FuelTankMutatedEvent;
use Enjin\Platform\FuelTanks\Models\AccountRule;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\FuelTankMutated as FuelTankMutatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Arr;

class FuelTankMutated extends FuelTankSubstrateEvent
{
    /**
     * Handle the fuel tank mutated event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        ray($event);

        if (!$event instanceof FuelTankMutatedPolkadart) {
            return;
        }

        // Fail if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($event->tankId);

        if (!is_null($uac = $event->userAccountManagement)) {
            $fuelTank->reserves_existential_deposit = Arr::get($uac, 'Some.tank_reserves_existential_deposit');
            $fuelTank->reserves_account_creation_deposit = Arr::get($uac, 'Some.tank_reserves_account_creation_deposit');
        }

        if (!is_null($providesDeposit = $event->providesDeposit)) {
            $fuelTank->provides_deposit = $providesDeposit;
        }

        if (!is_null($accountRules = $event->accountRules)) {
            AccountRule::where('fuel_tank_id', $fuelTank->id)?->delete();

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
        }

        $fuelTank->save();

        FuelTankMutatedEvent::safeBroadcast(
            $fuelTank,
            $this->getTransaction($block, $event->extrinsicIndex),
        );
    }
}
