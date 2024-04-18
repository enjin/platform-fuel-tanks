<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Closure;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\FuelTanks\GraphQL\Traits\HasFuelTankValidationRules;
use Enjin\Platform\FuelTanks\Rules\FuelTankExists;
use Enjin\Platform\FuelTanks\Services\Blockchain\Implemetations\Substrate;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\StoresTransactions;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasSkippableRules;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTransactionDeposit;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSigningAccountField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSimulateField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Services\Serialization\Interfaces\SerializationServiceInterface;
use Enjin\Platform\Support\Account;
use Enjin\Platform\Support\SS58Address;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\Facades\GraphQL;

class MutateFuelTankMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasFuelTankValidationRules;
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
        SerializationServiceInterface $serializationService,
        Substrate $blockchainService
    ) {
        $encodedData = $serializationService->encode($this->getMutationName(), static::getEncodableParams(
            userAccount: $blockchainService->getUserAccountManagementParams(Arr::get($args, 'mutation')),
            providesDeposit: Arr::get($args, 'mutation.providesDeposit'),
            accountRules: $blockchainService->getAccountRulesParams(Arr::get($args, 'mutation')),
            tankId: $args['tankId'],
        ));

        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $this->storeTransaction($args, $encodedData)),
            $resolveInfo
        );
    }

    public static function getEncodableParams(...$params): array
    {
        $tankId = Arr::get($params, 'tankId', Account::daemonPublicKey());
        $userAccount = Arr::get($params, 'userAccount', null);
        $providesDeposit = Arr::get($params, 'providesDeposit', null);
        $accountRules = Arr::get($params, 'accountRules', null);

        return [
            'tankId' => [
                'Id' => HexConverter::unPrefix(SS58Address::getPublicKey($tankId)),
            ],
            'mutation' => [
                'userAccountManagement' => is_array($userAccount) ? ['NoMutation' => null] : ['SomeMutation' => $userAccount?->toEncodable()],
                'providesDeposit' => $providesDeposit,
                'accountRules' => $accountRules?->toEncodable(),
            ],
        ];
    }

    /**
     * Get the mutation's validation rules.
     */
    protected function rulesWithValidation(array $args): array
    {
        return [
            'tankId' => [
                'filled',
                'max:255',
                new FuelTankExists(),
            ],
            'mutation' => 'required',
            ...$this->validationRulesExist($args, ['name', 'account'], 'mutation.'),
        ];
    }

    /**
     * Get the mutation's validation rules without DB rules.
     */
    protected function rulesWithoutValidation(array $args): array
    {
        return [
            'tankId' => [
                'filled',
                'max:255',
            ],
            'mutation' => 'required',
            ...$this->validationRulesExist($args, ['name', 'account'], 'mutation.'),
        ];
    }
}
