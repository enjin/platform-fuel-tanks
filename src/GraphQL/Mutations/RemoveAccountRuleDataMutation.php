<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Closure;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\FuelTanks\Enums\DispatchRule;
use Enjin\Platform\FuelTanks\GraphQL\Traits\HasFuelTankValidationRules;
use Enjin\Platform\FuelTanks\Rules\AccountsExistsInFuelTank;
use Enjin\Platform\FuelTanks\Rules\FuelTankExists;
use Enjin\Platform\FuelTanks\Rules\IsFuelTankOwner;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\StoresTransactions;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTransactionDeposit;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSigningAccountField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSimulateField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use Enjin\Platform\Services\Serialization\Interfaces\SerializationServiceInterface;
use Enjin\Platform\Support\Account;
use Enjin\Platform\Support\Hex;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\Facades\GraphQL;

class RemoveAccountRuleDataMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;
    use HasSigningAccountField;
    use HasSimulateField;
    use HasTransactionDeposit;
    use StoresTransactions;
    use HasFuelTankValidationRules;

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'RemoveAccountRuleData',
            'description' => __('enjin-platform-fuel-tanks::mutation.remove_account_rule_data.description'),
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
            'userId' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-fuel-tanks::mutation.remove_account_rule_data.args.userId'),
            ],
            'ruleSetId' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-fuel-tanks::mutation.schedule_mutate_freeze_state.args.ruleSetId'),
            ],
            'rule' => [
                'type' => GraphQL::type('DispatchRuleEnum!'),
                'description' => __('enjin-platform-fuel-tanks::enum.dispatch_rule.description'),
            ],
            ...$this->getSigningAccountField(),
            ...$this->getIdempotencyField(),
            ...$this->getSimulateField(),
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
        SerializationServiceInterface $serializationService
    ) {
        $encodedData = $serializationService->encode($this->getMutationName(), static::getEncodableParams(...$args));

        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $this->storeTransaction($args, $encodedData)),
            $resolveInfo
        );
    }

    public static function getEncodableParams(...$params): array
    {
        $tankId = Arr::get($params, 'tankId', Account::daemonPublicKey());
        $userId = Arr::get($params, 'userId', Account::daemonPublicKey());
        $ruleSetId = Arr::get($params, 'ruleSetId', 0);
        $ruleKind = DispatchRule::getEnumCase(Arr::get($params, 'rule'))->value;

        return [
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'userId' => [
                'Id' => HexConverter::unPrefix($userId),
            ],
            'ruleSetId' => $ruleSetId,
            'ruleKind' => $ruleKind,
        ];
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
                new IsFuelTankOwner(),
            ],
            'userId' => [
                'bail',
                'filled',
                'max:255',
                new ValidSubstrateAddress(),
                new AccountsExistsInFuelTank(Arr::get($args, 'tankId')),
            ],
            'ruleSetId' => [
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
            ],
        ];
    }
}
