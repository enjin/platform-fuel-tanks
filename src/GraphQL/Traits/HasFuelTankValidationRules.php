<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Traits;

use Enjin\Platform\FuelTanks\Rules\TokenExistsInCollection;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTokenIdFieldRules;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

trait HasFuelTankValidationRules
{
    use HasTokenIdFieldRules;

    /**
     * Get the common validation rules.
     */
    protected function commonRules(string $attribute, array $args = []): array
    {
        $isArray = str_contains($attribute, '.*');

        return match (true) {
            str_contains($attribute, 'FuelBudget') => [
                "{$attribute}.amount" => [
                    'bail',
                    new MinBigInt(),
                    new MaxBigInt(Hex::MAX_UINT128),
                ],
                "{$attribute}.resetPeriod" => [
                    'bail',
                    new MinBigInt(),
                    new MaxBigInt(Hex::MAX_UINT32),
                ],
            ],
            default => [
                "{$attribute}.whitelistedCallers.*" => ['bail', 'distinct', 'max:255', 'filled', new ValidSubstrateAddress()],
                "{$attribute}.whitelistedCallers" => ['nullable', 'array', 'min:1'],
                "{$attribute}.requireToken.collectionId" => $isArray
                        ? Rule::forEach(function ($value, $key) {
                            return [
                                'bail',
                                'required_with:' . str_replace('collectionId', 'tokenId', $key),
                                new MinBigInt(),
                                new MaxBigInt(Hex::MAX_UINT128),
                                Rule::exists('collections', 'collection_chain_id'),
                            ];
                        })
                        : [
                            "required_with:{$attribute}.requireToken.tokenId",
                            Rule::exists('collections', 'collection_chain_id'),
                        ],
                ...$this->getOptionalTokenFieldRules("{$attribute}.requireToken"),
                "{$attribute}.requireToken"=> $isArray
                    ? Rule::forEach(fn ($value, $key) => new TokenExistsInCollection(Arr::get($args, "{$key}.collectionId")))
                    : new TokenExistsInCollection(Arr::get($args, "{$attribute}.requireToken.collectionId")),
            ]
        };
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function validationRules(array $args = [], array $except = [], string $attributePrefix = ''): array
    {
        $rules = [
            "{$attributePrefix}name" => [
                'bail',
                'filled',
                'max:32',
                Rule::unique('fuel_tanks', 'name'),
            ],
            ...$this->commonRules("{$attributePrefix}accountRules", $args),
            ...$this->dispatchRules($args, $attributePrefix),
        ];

        return Arr::except($rules, $except);
    }

    /**
     * Get the dispatch rules validation rules.
     */
    protected function dispatchRules(array $args = [], string $attributePrefix = '', $isArray = true): array
    {
        $array = $isArray ? '.*' : '';

        return [
            ...$this->commonRules("{$attributePrefix}dispatchRules{$array}", $args),
            "{$attributePrefix}dispatchRules{$array}.whitelistedCollections.*" => [
                'bail',
                'distinct',
                'max:255',
                Rule::exists('collections', 'collection_chain_id'),
            ],
            "{$attributePrefix}dispatchRules{$array}.whitelistedCollections" => [
                'nullable',
                'array',
                'min:1',
            ],
            "{$attributePrefix}dispatchRules{$array}.maxFuelBurnPerTransaction" => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
            ],
            ...$this->commonRules("{$attributePrefix}dispatchRules{$array}.userFuelBudget"),
            ...$this->commonRules("{$attributePrefix}dispatchRules{$array}.tankFuelBudget"),
        ];
    }
}
