<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\AccountRemoved as AccountRemovedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountRemoved as AccountRemovedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class AccountRemoved extends FuelTankSubstrateEvent
{
    /**
     * Handle the account removed event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        ray($event);

        if (!$event instanceof AccountRemovedPolkadart) {
            return;
        }

        throw new \Exception('Account rules are not supported yet');
        // Fails if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($event->tankId);
        $account = $this->firstOrStoreAccount($event->userId);

        $fuelTankAccount = FuelTankAccount::where([
            'fuel_tank_id' => $fuelTank->id,
            'wallet_id' => $account->id,
        ])?->delete();

        Log::info(
            sprintf(
                'FuelTankAccount %s of FuelTank %s (id: %s) was removed.',
                $account->public_key,
                $fuelTank->public_key,
                $fuelTank->id,
            )
        );

        AccountRemovedEvent::safeBroadcast(
            $fuelTankAccount,
            $this->getTransaction($block, $event->extrinsicIndex),
        );
    }
}
