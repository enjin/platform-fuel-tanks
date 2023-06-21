<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Enums;

use Enjin\Platform\FuelTanks\Enums\DispatchCall;
use Enjin\Platform\Interfaces\PlatformGraphQlEnum;
use Rebing\GraphQL\Support\EnumType;

class DispatchCallEnum extends EnumType implements PlatformGraphQlEnum
{
    /**
     * Get the enum's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'DispatchCall',
            'values' => DispatchCall::caseNamesAsArray(),
            'description' => __('enjin-platform-fuel-tanks::enum.dispatch_call.description'),
        ];
    }
}
