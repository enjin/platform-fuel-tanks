<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Closure;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\FuelTanks\Enums\DispatchCall;
use Enjin\Platform\FuelTanks\Exceptions\FuelTanksException;
use Enjin\Platform\FuelTanks\Rules\IsFuelTankOwner;
use Enjin\Platform\FuelTanks\Rules\RuleSetExists;
use Enjin\Platform\FuelTanks\Rules\ValidMutation;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\StoresTransactions;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasSkippableRules;
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
use Enjin\Platform\Support\SS58Address;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\Facades\GraphQL;

class DispatchMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;
    use HasSigningAccountField;
    use HasSimulateField;
    use HasSkippableRules;
    use HasTransactionDeposit;
    use StoresTransactions;

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Dispatch',
            'description' => __('enjin-platform-fuel-tanks::mutation.dispatch.description'),
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
            'ruleSetId' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-fuel-tanks::mutation.schedule_mutate_freeze_state.args.ruleSetId'),
            ],
            'dispatch' => [
                'type' => GraphQL::type('DispatchInputType!'),
                'description' => __('enjin-platform-fuel-tanks::input_type.dispatch.description'),
            ],
            'paysRemainingFee' => [
                'type' => GraphQL::type('Boolean'),
                'description' => __('enjin-platform-fuel-tanks::mutation.dispatch.args.paysRemainingFee'),
                'defaultValue' => false,
            ],
            ...$this->getSigningAccountField(),
            ...$this->getIdempotencyField(),
            ...$this->getSimulateField(),
            ...$this->getSkipValidationField(),
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
        $paysRemainingFee = Arr::get($args, 'paysRemainingFee') ? '01' : '00';
        $encodedCall = $this->getEncodedCall($args);
        $encodedData = $serializationService->encode($this->getMutationName(), static::getEncodableParams(...$args));
        $encodedData .= $encodedCall . $paysRemainingFee;

        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $this->storeTransaction($args, $encodedData)),
            $resolveInfo
        );
    }

    public static function getEncodedCall($args)
    {
        $result = GraphQL::queryAndReturnResult(
            Arr::get($args, 'dispatch.query'),
            (array) Arr::get($args, 'dispatch.variables'),
            ['schema' => DispatchCall::getEnumCase(Arr::get($args, 'dispatch.call'))?->value]
        )->toArray();

        if (Arr::get($result, 'errors.0.message')) {
            throw new FuelTanksException(__('enjin-platform-fuel-tanks::exception.dispatch_query_error'));
        }

        $encodedData = null;
        if ($data = Arr::get($result, 'data')) {
            $data = array_shift($data);
            $encodedData = Arr::get($data, 'encodedData');
            Transaction::destroy(Arr::get($data, 'id'));
        }

        return HexConverter::unPrefix($encodedData);
    }

    public static function getEncodableParams(...$params): array
    {
        $tankId = Arr::get($params, 'tankId', Account::daemonPublicKey());
        $ruleSetId = Arr::get($params, 'ruleSetId', 0);

        return [
            'tankId' => [
                'Id' => HexConverter::unPrefix(SS58Address::getPublicKey($tankId)),
            ],
            'ruleSetId' => $ruleSetId,
        ];
    }

    /**
     * Get the mutation's validation rules.
     */
    protected function rulesWithValidation(array $args): array
    {
        return [
            'tankId' => [
                'bail',
                'filled',
                'max:255',
                new ValidSubstrateAddress(),
                new IsFuelTankOwner(),
            ],
            'ruleSetId' => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
                new RuleSetExists(),
            ],
            'dispatch.query' => [
                'filled',
                new ValidMutation(),
            ],
        ];
    }

    /**
     * Get the mutation's validation rules without DB rules.
     */
    protected function rulesWithoutValidation(array $args): array
    {
        return [
            'tankId' => [
                'bail',
                'filled',
                'max:255',
                new ValidSubstrateAddress(),
            ],
            'ruleSetId' => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
            ],
            'dispatch.query' => [
                'filled',
                new ValidMutation(),
            ],
        ];
    }
}
