<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Enums;

use Enjin\Platform\FuelTanks\Enums\DispatchRule;
use Enjin\Platform\Interfaces\PlatformGraphQlEnum;
use Rebing\GraphQL\Support\EnumType;

class DispatchRuleEnum extends EnumType implements PlatformGraphQlEnum
{
    /**
     * Get the enum's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'DispatchRuleEnum',
            'values' => DispatchRule::caseNamesAsArray(),
            'description' => __('enjin-platform-fuel-tanks::enum.dispatch_rule.description'),
        ];
    }
}
