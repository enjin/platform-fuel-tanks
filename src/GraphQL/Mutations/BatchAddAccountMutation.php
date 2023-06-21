<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Closure;
use Enjin\Platform\FuelTanks\Rules\AccountsNotInFuelTank;
use Enjin\Platform\FuelTanks\Rules\IsFuelTankOwner;
use Enjin\Platform\FuelTanks\Services\TransactionService;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\Facades\GraphQL;

class BatchAddAccountMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'BatchAddAccount',
            'description' => __('enjin-platform-fuel-tanks::mutation.batch_add_account.description'),
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
            'userIds' => [
                'type' => GraphQL::type('[String!]!'),
                'description' => __('enjin-platform-fuel-tanks::mutation.batch_add_account.args.userIds'),
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
            DB::transaction(fn () => $transaction->batchAddAccount($args)),
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
                'bail',
                'filled',
                'max:255',
                new ValidSubstrateAddress(),
                new IsFuelTankOwner(),
            ],
            'userIds' => ['array', 'min:1', 'max:100', new AccountsNotInFuelTank(Arr::get($args, 'tankId'))],
            'userIds.*' => ['bail', 'filled', 'distinct', 'max:255', new ValidSubstrateAddress()],
        ];
    }
}
