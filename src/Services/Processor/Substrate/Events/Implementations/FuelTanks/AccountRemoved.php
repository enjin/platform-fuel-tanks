<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\AccountRemoved as AccountRemovedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountRemoved as AccountRemovedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Facades\Log;

class AccountRemoved implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handle the account removed event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof AccountRemovedPolkadart) {
            return;
        }

        $account = WalletService::firstOrStore(['account' => Account::parseAccount($event->userId)]);
        $fuelTank = $this->getFuelTank($event->tankId);
        $fuelTankAccount = FuelTankAccount::firstWhere([
            'fuel_tank_id' => $fuelTank->id,
            'wallet_id' => $account->id,
        ]);
        $fuelTankAccount->delete();

        Log::info(
            sprintf(
                'FuelTankAccount %s (id: %s) of FuelTank %s (id: %s) was removed.',
                $account->public_key,
                $fuelTankAccount->id,
                $fuelTank->public_key,
                $fuelTank->id,
            )
        );

        AccountRemovedEvent::safeBroadcast($fuelTankAccount);
    }
}
