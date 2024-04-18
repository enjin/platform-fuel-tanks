<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\Enums\DispatchCall;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\DispatchAndTouchMutation;
use Enjin\Platform\Models\Collection;
use Enjin\Platform\Providers\Faker\SubstrateProvider;

class DispatchAndTouchTest extends DispatchTest
{
    /**
     * The graphql method.
     */
    protected string $method = 'DispatchAndTouch';

    public function test_it_can_dispatch(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateParams()
        );

        $encodedCall = DispatchAndTouchMutation::getEncodedCall($params);

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DispatchAndTouchMutation::getEncodableParams(...$params)) . $encodedCall . '00'
        );
    }

    public function test_it_can_skip_validation(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = ['tankId' => resolve(SubstrateProvider::class)->public_key(),
                'ruleSetId' => fake()->numberBetween(10000, 20000),
                'dispatch' => [
                    'call' => DispatchCall::MULTI_TOKENS->name,
                    'query' => static::$queries['SetCollectionAttribute'],
                    'variables' => [
                        'collectionId' => Collection::factory()->create(['owner_wallet_id' => $this->wallet])->collection_chain_id,
                        'key' => 'key',
                        'value' => 'value',
                    ],
                ],
                'skipValidation' => true,
            ],
        );

        $encodedCall = DispatchAndTouchMutation::getEncodedCall($params);

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DispatchAndTouchMutation::getEncodableParams(...$params)) . $encodedCall . '00'
        );
    }
}
