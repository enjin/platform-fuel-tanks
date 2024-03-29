<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\ForceSetConsumptionMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Str;

class SetConsumptionTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'SetConsumption';

    /**
     * The fuel tank.
     */
    protected FuelTank $tank;

    /**
     * The wallet account.
     */
    protected string $account;

    /**
     * Setup test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tank = $this->createFuelTank();
        $this->account = resolve(SubstrateProvider::class)->public_key();
        FuelTankAccount::create([
            'fuel_tank_id' => $this->tank->id,
            'wallet_id' => Wallet::create(['public_key' => $this->account, 'network' => 'developer'])->id,
        ]);
    }

    public function test_it_can_set_consumption(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateConsumption()
        );
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode('ForceSetConsumption', ForceSetConsumptionMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $data = $this->generateConsumption();
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

    public function test_it_will_fail_with_invalid_parameter_user_id(): void
    {
        $data = $this->generateConsumption();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['userId' => 'Invalid']),
            true
        );
        $this->assertArraySubset(
            ['userId' => ['The user id is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['userId' => null]),
            true
        );
        $this->assertNotEmpty($response);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['userId' => Str::random(300)]),
            true
        );
        $this->assertArraySubset(
            ['userId' => ['The user id field must not be greater than 255 characters.']],
            $response['error']
        );

        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['userId' => $pubicKey]),
            true
        );
        $this->assertArraySubset(
            ['userId' => ["The user id contains an account that doesn't exist in the fuel tank."]],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_rule_set_id(): void
    {
        $data = $this->generateConsumption();
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

    public function test_it_will_fail_with_invalid_parameter_total_consumed(): void
    {
        $data = $this->generateConsumption();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['totalConsumed' => 'Invalid']),
            true
        );
        $this->assertEquals(
            'Variable "$totalConsumed" got invalid value "Invalid"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['totalConsumed' => null]),
            true
        );
        $this->assertEquals(
            'Variable "$totalConsumed" of non-null type "BigInt!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['totalConsumed' => Hex::MAX_UINT256 + 1]),
            true
        );
        $this->assertEquals(
            'Variable "$totalConsumed" got invalid value 1.1579208923732E+77; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_last_reset_block(): void
    {
        $data = $this->generateConsumption();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['lastResetBlock' => 'Invalid']),
            true
        );
        $this->assertEquals(
            'Variable "$lastResetBlock" got invalid value "Invalid"; Int cannot represent non-integer value: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['lastResetBlock' => null]),
            true
        );
        $this->assertNotEmpty($response);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['lastResetBlock' => Hex::MAX_UINT256]),
            true
        );
        $this->assertStringContainsString(
            'got invalid value "115792089237316195423570985008687907853269984665640564039457584007913129639935"; Int cannot represent non-integer value',
            $response['error']
        );
    }

    /**
     * Generate a valid consumption data.
     */
    protected function generateConsumption(): array
    {
        return [
            'tankId' => $this->tank->public_key,
            'userId' => $this->account,
            'ruleSetId' => $this->tank->dispatchRules->first()->rule_set_id,
            'totalConsumed' => fake()->numberBetween(1, 100),
            'lastResetBlock' => fake()->numberBetween(1, 100),
        ];
    }
}
