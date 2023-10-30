<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;

class DispatchAndTouchMutation extends DispatchMutation implements PlatformBlockchainTransaction
{
    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'DispatchAndTouch',
            'description' => __('enjin-platform-fuel-tanks::mutation.dispatch_and_touch.description'),
        ];
    }
}
