<?php

namespace Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class MutateFreezeStateScheduled extends PlatformBroadcastEvent
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
        ];

        $this->broadcastChannels = [
            new Channel("tank;{$this->broadcastData['tankId']}"),
            new Channel($this->broadcastData['owner']),
            new PlatformAppChannel(),
        ];
    }
}
