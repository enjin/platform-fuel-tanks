<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\Enums\DispatchCall;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\DispatchMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Collection;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DispatchTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'Dispatch';

    /**
     * The fuel tank.
     */
    protected FuelTank $tank;

    /**
     * Setup test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tank = $this->createFuelTank();
    }

    public function test_it_can_dispatch(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateParams()
        );

        $encodedCall = DispatchMutation::getEncodedCall($params);

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DispatchMutation::getEncodableParams(...$params)) . $encodedCall . '00'
        );
    }

    public function test_it_can_dispatch_multi_token(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateParams(DispatchCall::MULTI_TOKENS)
        );

        $encodedCall = DispatchMutation::getEncodedCall($params);

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DispatchMutation::getEncodableParams(...$params)) . $encodedCall . '00'
        );
    }

    public function test_it_can_skip_validation(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = [
                'tankId' => resolve(SubstrateProvider::class)->public_key(),
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
            ]
        );

        $encodedCall = DispatchMutation::getEncodedCall($params);

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DispatchMutation::getEncodableParams(...$params)) . $encodedCall . '00'
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId' => $pubicKey]),
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId' => Str::random(300)]),
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId' => 'Invalid']),
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id is not a valid substrate address.']],
            $response['error']
        );

        $tank = $this->createFuelTank();
        $wallet = Wallet::create(
            [
                'public_key' => $pubicKey,
                'external_id' => fake()->unique()->uuid(),
                'managed' => fake()->boolean(),
                'verification_id' => fake()->unique()->uuid(),
                'network' => 'developer',
            ]
        );
        $tank->forceFill(['owner_wallet_id' => $wallet->id])->save();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId' => $tank->public_key]),
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id provided is not owned by you.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_rule_set_id(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['ruleSetId' => 'Invalid']),
            true
        );
        $this->assertEquals(
            'Variable "$ruleSetId" got invalid value "Invalid"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['ruleSetId' => null]),
            true
        );
        $this->assertEquals(
            'Variable "$ruleSetId" of non-null type "BigInt!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['ruleSetId' => Hex::MAX_UINT128]),
            true
        );
        $this->assertEquals(
            ['ruleSetId' => ['The rule set id is too large, the maximum value it can be is 4294967295.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['ruleSetId' => fake()->numberBetween(5000, 10000)]),
            true
        );
        $this->assertArraySubset(
            ['ruleSetId' => ["The rule set ID doesn't exist."]],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch(): void
    {
        $data = $this->generateParams();

        $invalidData = $data;
        Arr::set($invalidData, 'dispatch.call', 'Invalid');
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString(
            'invalid value "Invalid" at "dispatch.call"; Value "Invalid" does not exist in "DispatchCall" enum',
            $response['error']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'dispatch.call', null);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString(
            'Variable "$dispatch" got invalid value null at "dispatch.call"; Expected non-nullable type "DispatchCall!" not to be null.',
            $response['error']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'dispatch.query', str_replace('id', '', static::$queries['AddAccount']));
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            ['dispatch.query' => ['The id and encodedData attribute must be queried from the result.']],
            $response['error']
        );

        Arr::set($invalidData, 'dispatch.query', str_replace('encodedData', '', static::$queries['AddAccount']));
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            ['dispatch.query' => ['The id and encodedData attribute must be queried from the result.']],
            $response['error']
        );

        Arr::set($invalidData, 'dispatch.query', null);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString(
            'Variable "$dispatch" got invalid value null at "dispatch.query"; Expected non-nullable type "String!" not to be null',
            $response['error']
        );

        Arr::set($invalidData, 'dispatch.query', 'Invalid');
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString(
            'Syntax Error: Unexpected Name "Invalid"',
            $response['error']
        );


        $invalidData = $data;
        Arr::set($invalidData, 'dispatch.variables', 'Invalid');
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString(
            'Variable "$dispatch" got invalid value "Invalid" at "dispatch.variables"; Expected type "Object".',
            $response['error']
        );

        Arr::set($invalidData, 'dispatch.variables', null);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertEquals(
            "There's an error with the query. Please check the query and try again.",
            $response['error']
        );
    }

    /**
     * Generate parameters.
     */
    protected function generateParams(?DispatchCall $schema = null): array
    {
        $dispatch = match ($schema) {
            DispatchCall::MULTI_TOKENS => [
                'call' => DispatchCall::MULTI_TOKENS->name,
                'query' => static::$queries['SetCollectionAttribute'],
                'variables' => [
                    'collectionId' => Collection::factory()->create(['owner_wallet_id' => $this->wallet])->collection_chain_id,
                    'key' => 'key',
                    'value' => 'value',
                ],
            ],
            default => [
                'call' => DispatchCall::FUEL_TANKS->name,
                'query' => static::$queries['AddAccount'],
                'variables' => [
                    'tankId' => $this->tank->public_key,
                    'userId' => resolve(SubstrateProvider::class)->public_key(),
                ],
            ],
        };

        return [
            'tankId' => $this->tank->public_key,
            'ruleSetId' => $this->tank->dispatchRules->first()->rule_set_id,
            'dispatch' => $dispatch,
        ];
    }
}
