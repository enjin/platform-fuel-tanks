<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Types\Input;

use Rebing\GraphQL\Support\Facades\GraphQL;

class DispatchRuleInputType extends InputType
{
    /**
     * Get the input type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'DispatchRuleInputType',
            'description' => __('enjin-platform-fuel-tanks::input_type.dispatch_rule.description'),
        ];
    }

    /**
     * Get the input type's fields.
     */
    public function fields(): array
    {
        return [
            'whitelistedCallers' => [
                'type' => GraphQL::type('[String!]'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch_rule.field.whitelistedCallers'),
            ],
            'requireToken' => [
                'type' => GraphQL::type('MultiTokenIdInput'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch_rule.field.requireToken'),
            ],
            'whitelistedCollections' => [
                'type' => GraphQL::type('[BigInt!]'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch_rule.field.whitelistedCollections'),
            ],
            'maxFuelBurnPerTransaction' => [
                'type' => GraphQL::type('BigInt'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch_rule.field.maxFuelBurnPerTransaction'),
            ],
            'userFuelBudget' => [
                'type' => GraphQL::type('FuelBudgetInputType'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch_rule.field.userFuelBudget'),
            ],
            'tankFuelBudget' => [
                'type' => GraphQL::type('FuelBudgetInputType'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch_rule.field.tankFuelBudget'),
            ],
            'permittedExtrinsics' => [
                'type' => GraphQL::type('[TransactionMethod!]'),
                'description' => __('enjin-platform-fuel-tanks::input_type.permitted_extrinsics.description'),
            ],
            'requireSignature' => [
                'type' => GraphQL::type('String'),
                'description' => __('enjin-platform-fuel-tanks::input_type.require_signature.description'),
            ],
        ];
    }
}
