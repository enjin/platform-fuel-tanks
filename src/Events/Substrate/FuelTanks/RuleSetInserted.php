<?php

namespace Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class RuleSetInserted extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(Model $fuelTank, array $rules, ?Model $transaction = null)
    {
        parent::__construct();

        $this->broadcastData = [
            'idempotencyKey' => $transaction?->idempotency_key,
            'tankId' => $fuelTank->address,
            'name' => $fuelTank->name,
            'owner' => $fuelTank->owner->address,
            'rules' => $rules,
        ];

        $this->broadcastChannels = [
            new Channel("tank;{$this->broadcastData['tankId']}"),
            new Channel($this->broadcastData['owner']),
            new PlatformAppChannel(),
        ];
    }
}
