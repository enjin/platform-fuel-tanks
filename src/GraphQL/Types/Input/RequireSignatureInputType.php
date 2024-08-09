<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Types\Input;

use Rebing\GraphQL\Support\Facades\GraphQL;

class RequireSignatureInputType extends InputType
{
    /**
     * Get the input type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'RequireSignatureInputType',
            'description' => __('enjin-platform-fuel-tanks::input_type.require_signature.description'),
        ];
    }

    /**
     * Get the input type's fields.
     */
    public function fields(): array
    {
        return [
            'signature' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-fuel-tanks::input_type.require_signature.field.signature'),
            ],
        ];
    }
}
