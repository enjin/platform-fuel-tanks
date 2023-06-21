<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Types;

use Enjin\Platform\Traits\HasSelectFields;
use Rebing\GraphQL\Support\Facades\GraphQL;

class FuelTankType extends Type
{
    use HasSelectFields;

    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'FuelTank',
            'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.description'),
        ];
    }

    /**
     * Get the type's fields.
     */
    public function fields(): array
    {
        return [
            'tankId' => [
                'type' => GraphQL::type('Account'),
                'description' => __('enjin-platform-fuel-tanks::mutation.destroy_fuel_tank.args.tankId'),
                'resolve' => fn ($tank) => ['publicKey' => $tank->public_key, 'address' => $tank->address],
                'is_relation' => false,
                'selectable' => false,
            ],
            'name' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.name'),
            ],
            'reservesExistentialDeposit' => [
                'type' => GraphQL::type('Boolean'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.reservesExistentialDeposit'),
                'alias' => 'reserves_existential_deposit',
            ],
            'reservesAccountCreationDeposit' => [
                'type' => GraphQL::type('Boolean'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.reservesAccountCreationDeposit'),
                'alias' => 'reserves_account_creation_deposit',
            ],
            'providesDeposit' => [
                'type' => GraphQL::type('Boolean!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.providesDeposit'),
                'alias' => 'provides_deposit',
            ],
            'isFrozen' => [
                'type' => GraphQL::type('Boolean!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.isFrozen'),
                'alias' => 'is_frozen',
            ],
            'accountCount' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.accountCount'),
                'alias' => 'account_count',
            ],
            'owner' => [
                'type' => GraphQL::type('Wallet!'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.wallet'),
                'is_relation' => true,
            ],
            'accounts' => [
                'type' => GraphQL::type('[Wallet]'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.accounts'),
                'is_relation' => true,
            ],
            'accountRules' => [
                'type' => GraphQL::type('[AccountRule]'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.accountRules'),
                'is_relation' => true,
            ],
            'dispatchRules' => [
                'type' => GraphQL::type('[DispatchRule]'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.dispatchRules'),
                'is_relation' => true,
            ],
        ];
    }
}
