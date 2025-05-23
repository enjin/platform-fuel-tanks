<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Queries;

use Closure;
use Enjin\Platform\FuelTanks\Models\Wallet;
use Enjin\Platform\FuelTanks\Rules\FuelTankExists;
use Enjin\Platform\GraphQL\Middleware\ResolvePage;
use Enjin\Platform\GraphQL\Types\Pagination\ConnectionInput;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use Enjin\Platform\Support\SS58Address;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class GetAccountsQuery extends Query
{
    protected $middleware = [
        ResolvePage::class,
    ];

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'GetAccounts',
            'description' => __('enjin-platform-fuel-tanks::query.get_fuel_accounts.description'),
        ];
    }

    /**
     * Get the query's return type.
     */
    public function type(): Type
    {
        return GraphQL::paginate('Wallet', 'WalletConnection');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return ConnectionInput::args([
            'tankId' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-fuel-tanks::mutation.fuel_tank.args.tankId'),
            ],
        ]);
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
        return Wallet::loadSelectFields($resolveInfo, $this->name)
            ->whereHas('fuelTanks', fn ($query) => $query->where('public_key', SS58Address::getPublicKey($args['tankId'])))
            ->cursorPaginateWithTotalDesc('id', $args['first']);
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'tankId' => [
                'bail',
                'filled',
                'max:255',
                new ValidSubstrateAddress(),
                new FuelTankExists(),
            ],
        ];
    }
}
