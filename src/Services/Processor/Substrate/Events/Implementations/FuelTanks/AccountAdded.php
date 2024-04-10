<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\AccountAdded as AccountAddedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountAdded as AccountAddedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class AccountAdded extends FuelTankSubstrateEvent
{
    /**
     * Handle the account added event.
     *
     * @throws PlatformException
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof AccountAddedPolkadart) {
            return;
        }

        // Fails if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($event->tankId);
        $account = $this->firstOrStoreAccount($event->userId);

        $fuelTankAccount = FuelTankAccount::create([
            'fuel_tank_id' => $fuelTank->id,
            'wallet_id' => $account->id,
            'tank_deposit' => $event->tankDeposit,
            'user_deposit' => $event->userDeposit,
            'total_received' => $event->totalReceived,
        ]);

        $transaction = $this->getTransaction($block, $event->extrinsicIndex);

        Log::info(
            sprintf(
                'FuelTankAccount %s (id: %s) of FuelTank %s (id: %s) was created from transaction %s (id: %s).',
                $account->public_key,
                $fuelTankAccount->id,
                $fuelTank->public_key,
                $fuelTank->id,
                $transaction?->transaction_chain_hash ?? 'unknown',
                $transaction?->id ?? 'unknown'
            )
        );

        AccountAddedEvent::safeBroadcast(
            $fuelTankAccount,
            $transaction
        );
    }
}
