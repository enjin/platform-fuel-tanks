<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Closure;
use Enjin\Platform\FuelTanks\Services\TransactionService;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Models\Transaction;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\DB;

class DispatchAndTouchMutation extends DispatchMutation implements PlatformBlockchainTransaction
{
    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'DispatchAndTouch',
            'description' => __('enjin-platform-fuel-tanks::mutation.dispatch_and_touch.description'),
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
            DB::transaction(fn () => $transaction->dispatchAndTouch($args)),
            $resolveInfo
        );
    }
}
