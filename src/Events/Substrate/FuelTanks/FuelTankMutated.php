<?php

namespace Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class FuelTankMutated extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(Model $fuelTank, ?Model $transaction = null)
    {
        parent::__construct();

        $this->broadcastData = [
            'tankId' => $fuelTank->address,
            'name' => $fuelTank->name,
            'owner' => $fuelTank->owner->address,
            'reservesExistentialDeposit' => $fuelTank->reserves_existential_deposit,
            'reservesAccountCreationDeposit' => $fuelTank->reserves_account_creation_deposit,
            'providesDeposit' => $fuelTank->provides_deposit,
        ];

        $this->broadcastChannels = [
            new Channel("tank;{$this->broadcastData['tankId']}"),
            new Channel($this->broadcastData['owner']),
            new PlatformAppChannel(),
        ];
    }
}
