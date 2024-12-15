<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Types\Input;

use Rebing\GraphQL\Support\Facades\GraphQL;

class DispatchInputType extends InputType
{
    /**
     * Get the input type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'DispatchInputType',
            'description' => __('enjin-platform-fuel-tanks::input_type.dispatch.description'),
        ];
    }

    /**
     * Get the input type's fields.
     */
    public function fields(): array
    {
        return [
            'call' => [
                'type' => GraphQL::type('DispatchCall!'),
                'description' => __('enjin-platform-fuel-tanks::enum.dispatch_call.description'),
            ],
            'query' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch.field.query'),
            ],
            'variables' => [
                'type' => GraphQL::type('Object'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch.field.variables'),
            ],
            'settings' => [
                'type' => GraphQL::type('DispatchSettingsInputType'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch.field.settings'),
            ],
        ];
    }
}
