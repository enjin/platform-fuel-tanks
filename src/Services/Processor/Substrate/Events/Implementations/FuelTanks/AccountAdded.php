<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\AccountAdded as AccountAddedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountAdded as AccountAddedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class AccountAdded extends FuelTankSubstrateEvent
{
    /** @var AccountAddedPolkadart */
    protected Event $event;

    /**
     * Handle the account added event.
     *
     * @throws PlatformException
     */
    public function run(): void
    {
        // Fails if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($this->event->tankId);
        $account = $this->firstOrStoreAccount($this->event->userId);

        FuelTankAccount::create([
            'fuel_tank_id' => $fuelTank->id,
            'wallet_id' => $account->id,
            'tank_deposit' => $this->event->tankDeposit,
            'user_deposit' => $this->event->userDeposit,
            'total_received' => $this->event->totalReceived,
        ]);
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'FuelTankAccount %s of FuelTank %s was created from transaction %s.',
                $this->event->userId,
                $this->event->tankId,
                $transaction?->transaction_chain_hash ?? 'unknown',
            )
        );
    }

    public function broadcast(): void
    {
        AccountAddedEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
        );
    }
}
