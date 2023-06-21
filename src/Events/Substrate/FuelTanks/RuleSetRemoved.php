<?php

namespace Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class RuleSetRemoved extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(Model $fuelTank, int $ruleSetId)
    {
        parent::__construct();

        $this->broadcastData = [
            'tankId' => $fuelTank->address,
            'name' => $fuelTank->name,
            'owner' => $fuelTank->owner->address,
            'ruleSet' => $ruleSetId,
        ];

        $this->broadcastChannels = [
            new Channel("tank;{$this->broadcastData['tankId']}"),
            new Channel($this->broadcastData['owner']),
            new PlatformAppChannel(),
        ];
    }
}
