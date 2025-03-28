<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Queries;

use Closure;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Rules\FuelTankExists;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use Enjin\Platform\Support\SS58Address;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\Facades\GraphQL;

class GetFuelTankQuery extends Query
{
    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'GetFuelTank',
            'description' => __('enjin-platform-fuel-tanks::query.get_fuel_tank.description'),
        ];
    }

    /**
     * Get the query's return type.
     */
    public function type(): Type
    {
        return GraphQL::type('FuelTank!');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return [
            'name' => [
                'type' => GraphQL::type('String'),
                'description' => __('enjin-platform-fuel-tanks::type.fuel_tank.field.name'),
            ],
            'tankId' => [
                'type' => GraphQL::type('String'),
                'description' => __('enjin-platform-fuel-tanks::mutation.fuel_tank.args.tankId'),
            ],
        ];
    }

    /**
     * Resolve the mutation's request.
     */
    public function resolve(
        $root,
        array $args,
        $context,
        ResolveInfo $resolveInfo,
        Closure $getSelectFields
    ) {
        return FuelTank::loadSelectFields($resolveInfo, $this->name)
            ->when(
                $publicKey = Arr::get($args, 'tankId'),
                fn ($query) => $query->where('public_key', SS58Address::getPublicKey($publicKey))
            )->when(
                $name = Arr::get($args, 'name'),
                fn ($query) => $query->where('name', $name)
            )->first();
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'name' => [
                'bail',
                'required_without:tankId',
                'prohibits:tankId',
                'filled',
                'max:32',
                Rule::exists('fuel_tanks', 'name'),
            ],
            'tankId' => [
                'bail',
                'required_without:name',
                'prohibits:name',
                'filled',
                'max:255',
                new ValidSubstrateAddress(),
                new FuelTankExists(),
            ],
        ];
    }
}
