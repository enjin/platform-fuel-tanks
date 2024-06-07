<?php

namespace Enjin\Platform\FuelTanks\Events\Substrate\FuelTanks;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountAdded as AccountAddedPolkadart;

class AccountAdded extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(AccountAddedPolkadart $event, ?Model $transaction = null, ?array $extra = null)
    {
        parent::__construct();

        $this->broadcastData = $event->toBroadcast([
            'idempotencyKey' => $transaction?->idempotency_key,
        ]);

        $this->broadcastChannels = [
            new Channel("tank;{$event->tankId}"),
            new Channel($event->userId),
            new PlatformAppChannel(),
        ];
    }
}
