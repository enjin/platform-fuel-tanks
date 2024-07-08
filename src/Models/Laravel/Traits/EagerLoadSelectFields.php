<?php

namespace Enjin\Platform\FuelTanks\Models\Laravel\Traits;

use Enjin\Platform\FuelTanks\GraphQL\Types\DispatchRuleType;
use Enjin\Platform\FuelTanks\GraphQL\Types\FuelTankType;
use Enjin\Platform\GraphQL\Types\Substrate\WalletType;
use Enjin\Platform\Models\Laravel\Traits\EagerLoadSelectFields as EagerLoadSelectFieldsBase;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait EagerLoadSelectFields
{
    use EagerLoadSelectFieldsBase {
        getRelationQuery as parentGetRelationQuery;
        loadWallet as parentLoadWallet;
    }

    /**
     * Load select and relationship fields.
     */
    public static function selectFields(ResolveInfo $resolveInfo, string $query): array
    {
        $select = ['*'];
        $with = [];
        $withCount = [];
        static::$query = $query;
        $queryPlan = $resolveInfo->lookAhead()->queryPlan();

        [$select, $with, $withCount] = match ($query) {
            'GetFuelTanks', 'GetFuelTank' => static::loadFuelTank(
                $queryPlan,
                $query == 'GetFuelTanks' ? 'edges.fields.node.fields' : '',
                [],
                null,
                true
            ),
            default => [$select, $with, $withCount],
        };


        return [$select, $with, $withCount];
    }

    /**
     * Load fuel tank's select and relationship fields.
     */
    public static function loadFuelTank(
        array $selections,
        string $attribute,
        array $args = [],
        ?string $key = null,
        bool $isParent = false
    ): array {
        $fields = Arr::get($selections, $attribute, $selections);
        $select = array_filter([
            'id',
            isset($fields['owner']) ? 'owner_wallet_id' : null,
            isset($fields['tankId']) ? 'public_key' : null,
            ...FuelTankType::getSelectFields($fieldKeys = array_keys($fields)),
        ]);

        $with = [];
        $withCount = [];

        if (!$isParent) {
            $with = [
                $key => function ($query) use ($select, $args): void {
                    $query->select(array_unique($select))
                        ->when($cursor = Cursor::fromEncoded(Arr::get($args, 'after')), fn ($q) => $q->where('id', '>', $cursor->parameter('id')))
                        ->orderBy('fuel_tanks.id');
                    // This must be done this way to load eager limit correctly.
                    if ($limit = Arr::get($args, 'first')) {
                        $query->limit($limit + 1);
                    }
                },
            ];
        }

        foreach (FuelTankType::getRelationFields($fieldKeys) as $relation) {
            if ($isParent && in_array($relation, ['accountRules', 'dispatchRules', 'accounts'])) {
                $withCount[] = $relation;
            }

            $with = array_merge(
                $with,
                static::getRelationQuery(
                    FuelTankType::class,
                    $relation,
                    $fields,
                    $key,
                    $with
                )
            );
        }

        return [$select, $with, $withCount];
    }

    /**
     * Get relationship query.
     */
    public static function getRelationQuery(
        string $parentType,
        string $attribute,
        array $selections,
        ?string $parent = null,
        array $withs = []
    ): array {
        $key = $parent ? "{$parent}.{$attribute}" : $attribute;
        $alias = static::getAlias($attribute, $parentType);
        $args = Arr::get($selections, $attribute . '.args', []);
        switch ($alias) {
            case 'accounts':
                $relations = static::loadWallet(
                    $selections,
                    $attribute == 'royaltyBeneficiary'
                        ? 'royalty.fields.beneficiary.fields'
                        : $attribute . '.fields',
                    $args,
                    $key
                );
                $withs = array_merge($withs, $relations[1]);

                break;
            case 'accountRules':
            case 'dispatchRules':
                $fields = Arr::get($selections, $attribute . '.fields', $selections);
                $select = collect(['id', 'fuel_tank_id', ...DispatchRuleType::getSelectFields(array_keys($fields))])
                    ->filter()
                    ->unique()
                    ->toArray();
                $withs = array_merge(
                    $withs,
                    [$key => fn ($query) => $query->select($select)]
                );

                break;
            default:
                return static::parentGetRelationQuery($parentType, $attribute, $selections, $parent, $withs);
        }

        return $withs;
    }

    /**
     * Load wallet's select and relationship fields.
     */
    public static function loadWallet(
        array $selections,
        string $attribute,
        array $args = [],
        ?string $key = null,
        bool $isParent = false
    ): array {
        $fields = Arr::get($selections, $attribute, $selections);
        $select = collect(['id', 'public_key', ...WalletType::getSelectFields($fieldKeys = array_keys($fields))])
            ->filter()
            ->unique()
            ->map(fn ($field) => "wallets.{$field}")
            ->toArray();

        $with = [];
        $withCount = [];

        if (!$isParent) {
            $with = [
                $key => function ($query) use ($select, $args): void {
                    $query->select($select)
                        ->when(Arr::get($args, 'transactionIds'), fn ($q) => $q->whereIn('transaction_chain_id', $args['transactionIds']))
                        ->when(Arr::get($args, 'transactionHashes'), fn ($q) => $q->whereIn('transaction_chain_hash', $args['transactionIds']))
                        ->when(Arr::get($args, 'methods'), fn ($q) => $q->whereIn('method', $args['methods']))
                        ->when(Arr::get($args, 'states'), fn ($q) => $q->whereIn('state', $args['states']))
                        ->when($cursor = Cursor::fromEncoded(Arr::get($args, 'after')), fn ($q) => $q->where('id', '>', $cursor->parameter('id')))
                        ->orderBy('wallets.id');

                    // This must be done this way to load eager limit correctly.
                    if ($limit = Arr::get($args, 'first')) {
                        $query->limit($limit + 1);
                    }
                },
            ];
        }

        foreach (WalletType::getRelationFields($fieldKeys) as $relation) {
            switch ($relation) {
                case 'collectionAccounts':
                    $withCount[$relation] = fn ($query) => $query->when(
                        Arr::get($args, 'collectionIds'),
                        fn ($q) => $q->whereIn(
                            'collection_id',
                            DB::table('collections')->select('id')->whereIn('collection_chain_id', $args['collectionIds'])
                        )
                    );

                    break;
                case 'tokenAccounts':
                    $withCount[$relation] = fn ($query) => $query->when(
                        Arr::get($args, 'collectionIds'),
                        fn ($q) => $q->whereIn(
                            'collection_id',
                            DB::table('collections')->select('id')->whereIn('collection_chain_id', $args['collectionIds'])
                        )
                    )->when(
                        Arr::get($args, 'tokenIds'),
                        fn ($q) => $q->whereIn(
                            'token_id',
                            DB::table('tokens')->select('id')->whereIn('token_chain_id', $args['tokenIds'])
                        )
                    );

                    break;
                case 'transactions':
                    $withCount[$relation] = fn ($query) => $query->when(Arr::get($args, 'transactionIds'), fn ($q) => $q->whereIn('transaction_chain_id', $args['transactionIds']))
                        ->when(Arr::get($args, 'transactionHashes'), fn ($q) => $q->whereIn('transaction_chain_hash', $args['transactionIds']))
                        ->when(Arr::get($args, 'methods'), fn ($q) => $q->whereIn('method', $args['methods']))
                        ->when(Arr::get($args, 'states'), fn ($q) => $q->whereIn('state', $args['states']));

                    break;
                case 'ownedCollections':
                    $withCount[$relation] = fn ($query) => $query->when(
                        Arr::get($args, 'collectionIds'),
                        fn ($q) => $q->whereIn('collection_id', $args['collectionIds'])
                    );

                    break;
                case 'tokenAccountApprovals':
                case 'collectionAccountApprovals':
                    $withCount[] = $relation;

                    break;
            }

            $with = array_merge(
                $with,
                static::getRelationQuery(
                    WalletType::class,
                    $relation,
                    $fields,
                    $key,
                    $with
                )
            );
        }

        return [$select, $with, $withCount];
    }
}
