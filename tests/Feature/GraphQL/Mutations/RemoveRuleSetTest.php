<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\RemoveRuleSetMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Str;

class RemoveRuleSetTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'RemoveRuleSet';

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
            $this->method,
            $params = ['tankId' => $this->tank->public_key, 'ruleSetId' => $this->tank->dispatchRules->first()->rule_set_id],
        );
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, RemoveRuleSetMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $response = $this->graphql(
            $this->method,
            ['tankId' => $pubicKey, 'ruleSetId' => 1],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => Str::random(300), 'ruleSetId' => 1],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => 'Invalid', 'ruleSetId' => 1],
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
            ['tankId' => $tank->public_key, 'ruleSetId' => 1],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id provided is not owned by you.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_rule_set_id(): void
    {
        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'ruleSetId' => 'Invalid'],
            true
        );
        $this->assertEquals(
            'Variable "$ruleSetId" got invalid value "Invalid"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'ruleSetId' => null],
            true
        );
        $this->assertEquals(
            'Variable "$ruleSetId" of non-null type "BigInt!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'ruleSetId' => Hex::MAX_UINT128],
            true
        );
        $this->assertEquals(
            ['ruleSetId' => ['The rule set id is too large, the maximum value it can be is 4294967295.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'ruleSetId' => fake()->numberBetween(5000, 10000)],
            true
        );
        $this->assertArraySubset(
            ['ruleSetId' => ["The rule set ID doesn't exist."]],
            $response['error']
        );
    }
}
