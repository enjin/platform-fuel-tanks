<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Closure;
use Enjin\Platform\FuelTanks\GraphQL\Traits\HasFuelTankValidationRules;
use Enjin\Platform\FuelTanks\Rules\FuelTankExists;
use Enjin\Platform\FuelTanks\Services\TransactionService;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Models\Transaction;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\Facades\GraphQL;

class MutateFuelTankMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;
    use HasFuelTankValidationRules;

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'MutateFuelTank',
            'description' => __('enjin-platform-fuel-tanks::mutation.mutate_fuel_tank.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    public function type(): Type
    {
        return GraphQL::type('Transaction!');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return [
            'tankId' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-fuel-tanks::mutation.destroy_fuel_tank.args.tankId'),
            ],
            'mutation' => [
                'type' => GraphQL::type('FuelTankMutationInputType!'),
                'description' => __('enjin-platform-fuel-tanks::input_type.fuel_tank_mutation.description'),
            ],
            ...$this->getIdempotencyField(),
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
        Closure $getSelectFields,
        TransactionService $transaction
    ) {
        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $transaction->updateFuelTank($args)),
            $resolveInfo
        );
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'tankId' => [
                'filled',
                'max:255',
                new FuelTankExists(),
            ],
            'mutation' => 'required',
            ...$this->validationRules($args, ['name', 'account'], 'mutation.'),
        ];
    }
}
