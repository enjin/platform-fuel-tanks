<?php

namespace Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class AccountRemoved extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(Model $fuelTankAccount, ?Model $transaction = null)
    {
        parent::__construct();

        $this->broadcastData = [
            'tankId' => $fuelTankAccount->fuelTank->public_key,
            'name' => $fuelTankAccount->fuelTank->name,
            'owner' => $fuelTankAccount->fuelTank->owner->address,
            'account' => $fuelTankAccount->wallet->address,
        ];

        $this->broadcastChannels = [
            new Channel("tank;{$this->broadcastData['tankId']}"),
            new Channel($this->broadcastData['owner']),
            new Channel($this->broadcastData['account']),
            new PlatformAppChannel(),
        ];
    }
}
