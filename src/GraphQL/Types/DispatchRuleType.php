<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Types;

use Enjin\Platform\Traits\HasSelectFields;
use Rebing\GraphQL\Support\Facades\GraphQL;

class DispatchRuleType extends Type
{
    use HasSelectFields;

    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'DispatchRule',
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
            'ruleSetId' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-fuel-tanks::mutation.schedule_mutate_freeze_state.args.ruleSetId'),
                'alias' => 'rule_set_id',
            ],
            'value' => [
                'type' => GraphQL::type('Object!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank_rule.field.value'),
            ],
            'isFrozen' => [
                'type' => GraphQL::type('Boolean'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank_rule.field.value'),
                'alias' => 'is_frozen',
            ],
        ];
    }
}
