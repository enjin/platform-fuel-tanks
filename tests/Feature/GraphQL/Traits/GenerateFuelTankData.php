<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Traits;

use Enjin\Platform\FuelTanks\Enums\CoveragePolicy;
use Enjin\Platform\Providers\Faker\SubstrateProvider;

trait GenerateFuelTankData
{
    /**
     * Generate data.
     */
    protected function generateData($isArray = true, $value = null): array
    {
        $provider = resolve(SubstrateProvider::class);

        $dispatchRules = [
            'whitelistedCallers' => [$provider->public_key()],
            'requireToken' => [
                'collectionId' => $this->collection->collection_chain_id,
                'tokenId' => ['integer' => $this->token->token_chain_id],
            ],
            'whitelistedCollections' => [$this->collection->collection_chain_id],
            'maxFuelBurnPerTransaction' => $value ?? fake()->numberBetween(1, 1000),
            'userFuelBudget' => ['amount' => $value ?? fake()->numberBetween(1, 1000), 'resetPeriod' => fake()->numberBetween(1, 1000)],
            'tankFuelBudget' => ['amount' => $value ?? fake()->numberBetween(1, 1000), 'resetPeriod' => fake()->numberBetween(1, 1000)],
            'permittedExtrinsics' => ['CreateCollection', 'ApproveCollection', 'SimpleTransferToken', 'OperatorTransferToken'],
        ];

        return [
            'name' => fake()->text(32),
            'account' => $this->wallet->address,
            'reservesAccountCreationDeposit' => fake()->boolean(),
            'coveragePolicy' => fake()->randomElement(CoveragePolicy::caseNamesAsArray()),
            'accountRules' => [
                'whitelistedCallers' => [$provider->public_key()],
                'requireToken' => [
                    'collectionId' => $this->collection->collection_chain_id,
                    'tokenId' => ['integer' => $this->token->token_chain_id],
                ],
            ],
            'dispatchRules' => $isArray ? [$dispatchRules] : $dispatchRules,
            'requireAccount' => true,
        ];
    }
}
