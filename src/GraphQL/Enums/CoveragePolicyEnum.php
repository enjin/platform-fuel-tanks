<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Enums;

use Enjin\Platform\FuelTanks\Enums\CoveragePolicy;
use Enjin\Platform\Interfaces\PlatformGraphQlEnum;
use Rebing\GraphQL\Support\EnumType;

class CoveragePolicyEnum extends EnumType implements PlatformGraphQlEnum
{
    /**
     * Get the enum's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'CoveragePolicy',
            'values' => CoveragePolicy::caseNamesAsArray(),
            'description' => __('enjin-platform-fuel-tanks::enum.coverage_policy.description'),
        ];
    }
}
