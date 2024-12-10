<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\ScheduleMutateFreezeStateMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Enjin\Platform\Support\Hex;
use Enjin\Platform\Support\SS58Address;
use Illuminate\Support\Str;

class ScheduleMutateFreezeStateTest extends TestCaseGraphQL
{
    /**
     * The graphql mutation.
     */
    protected string $mutation = 'ScheduleMutateFreezeState';

    /**
     * The graphql method.
     */
    protected string $method = 'MutateFreezeState';

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

    public function test_it_can_remove_rule_set(): void
    {
        $response = $this->graphql(
            $this->mutation,
            $params = $this->generateParams()
        );

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, ScheduleMutateFreezeStateMutation::getEncodableParams(...$params))
        );

        $params['tankId'] = SS58Address::encode($params['tankId']);
        $response = $this->graphql($this->mutation, $params);
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, ScheduleMutateFreezeStateMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_can_skip_validation(): void
    {
        $response = $this->graphql(
            $this->mutation,
            $params = [
                'tankId' => resolve(SubstrateProvider::class)->public_key(),
                'isFrozen' => fake()->boolean(),
                'ruleSetId' => fake()->numberBetween(10000, 20000),
                'skipValidation' => true,
            ]
        );
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, ScheduleMutateFreezeStateMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->mutation,
            array_merge($data, ['tankId' => $pubicKey]),
            true
        );
        $this->assertArrayContainsArray(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );


        $response = $this->graphql(
            $this->mutation,
            array_merge($data, ['tankId' => Str::random(300)]),
            true
        );
        $this->assertArrayContainsArray(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->mutation,
            array_merge($data, ['tankId' => 'Invalid']),
            true
        );
        $this->assertArrayContainsArray(
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
            $this->mutation,
            array_merge($data, ['tankId' => $tank->public_key]),
            true
        );
        $this->assertArrayContainsArray(
            ['tankId' => ['The tank id provided is not owned by you.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_rule_set_id(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->mutation,
            array_merge($data, ['ruleSetId' => 'Invalid']),
            true
        );
        $this->assertEquals(
            'Variable "$ruleSetId" got invalid value "Invalid"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->mutation,
            array_merge($data, ['ruleSetId' => null]),
            true
        );
        $this->assertNotEmpty($response);

        $response = $this->graphql(
            $this->mutation,
            array_merge($data, ['tankId' => $this->tank->public_key, 'ruleSetId' => Hex::MAX_UINT128]),
            true
        );
        $this->assertEquals(
            ['ruleSetId' => ['The rule set id is too large, the maximum value it can be is 4294967295.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->mutation,
            array_merge($data, ['ruleSetId' => fake()->numberBetween(5000, 10000)]),
            true
        );
        $this->assertArrayContainsArray(
            ['ruleSetId' => ["The rule set ID doesn't exist."]],
            $response['error']
        );
    }

    /**
     * Generate params.
     */
    protected function generateParams(): array
    {
        return [
            'tankId' => $this->tank->public_key,
            'isFrozen' => fake()->boolean(),
            'ruleSetId' => $this->tank->dispatchRules->first()->rule_set_id,
        ];
    }
}
