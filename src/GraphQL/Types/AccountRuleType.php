<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Types;

use Enjin\Platform\Traits\HasSelectFields;
use Rebing\GraphQL\Support\Facades\GraphQL;

class AccountRuleType extends Type
{
    use HasSelectFields;

    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'AccountRule',
            'description' => __('enjin-platform-fuel-tanks::type.fuel_tank_rule.description'),
        ];
    }

    /**
     * Get the type's fields.
     */
    public function fields(): array
    {
        return [
            'rule' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank_rule.field.rule'),
            ],
            'value' => [
                'type' => GraphQL::type('Object!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank_rule.field.value'),
            ],
        ];
    }
}
