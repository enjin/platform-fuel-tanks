<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks;

use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\FuelTanks\AccountRuleDataRemoved as AccountRuleDataPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;

class AccountRuleDataRemoved implements SubstrateEvent
{
    /**
     * Handle the account rule data removed event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof AccountRuleDataPolkadart) {
            return;
        }

        $extrinsic = $block->extrinsics[$event->extrinsicIndex];
        $params = $extrinsic->params;

        // TODO: Removes tracking data associated to a dispatch rule (doesn't remove the rule)
        // Not sure how to do that yet, we would have to knew the rule structure on chain.
        // Maybe we should query the storage for this one? That would make it slower though
    }
}
