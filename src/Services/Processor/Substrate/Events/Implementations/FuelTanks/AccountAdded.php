<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\AccountAdded as AccountAddedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountAdded as AccountAddedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Facades\Log;

class AccountAdded implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the account added event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof AccountAddedPolkadart) {
            return;
        }

        $account = WalletService::firstOrStore(['account' => Account::parseAccount($event->userId)]);
        $fuelTank = $this->getFuelTank($event->tankId);
        $fuelTankAccount = FuelTankAccount::create([
            'fuel_tank_id' => $fuelTank->id,
            'wallet_id' => $account->id,
        ]);

        Log::info(
            sprintf(
                'FuelTankAccount %s (id: %s) of FuelTank %s (id: %s) was created.',
                $account->public_key,
                $fuelTankAccount->id,
                $fuelTank->public_key,
                $fuelTank->id,
            )
        );

        AccountAddedEvent::safeBroadcast($fuelTankAccount);
    }
}
