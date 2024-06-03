<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks\AccountRemoved as AccountRemovedEvent;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\FuelTankSubstrateEvent;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountRemoved as AccountRemovedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class AccountRemoved extends FuelTankSubstrateEvent
{
    /** @var AccountRemovedPolkadart */
    protected Event $event;

    /**
     * Handle the account removed event.
     *
     * @throws PlatformException
     */
    public function run(): void
    {
        // Fails if it doesn't find the fuel tank
        $fuelTank = $this->getFuelTank($this->event->tankId);
        $account = $this->firstOrStoreAccount($this->event->userId);

        FuelTankAccount::where([
            'fuel_tank_id' => $fuelTank->id,
            'wallet_id' => $account->id,
        ])?->delete();
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'FuelTankAccount %s of FuelTank %s was removed.',
                $this->event->userId,
                $this->event->tankId,
            )
        );
    }

    public function broadcast(): void
    {
        AccountRemovedEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
        );
    }
}
