<?php

namespace Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class CallDispatched extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(Model $fuelTank, Model $caller)
    {
        parent::__construct();

        $this->broadcastData = [
            'tankId' => $fuelTank->public_key,
            'owner' => $fuelTank->owner->address,
            'name' => $fuelTank->name,
            'caller' => $caller->address,
        ];

        $this->broadcastChannels = [
            new Channel("tank;{$this->broadcastData['tankId']}"),
            new Channel($this->broadcastData['owner']),
            new Channel($this->broadcastData['caller']),
            new PlatformAppChannel(),
        ];
    }
}
