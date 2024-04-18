<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\Enums\DispatchRule;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\RemoveAccountRuleDataMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Str;

class RemoveAccountRuleDataTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'RemoveAccountRuleData';

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

    public function test_it_can_remove_account_rule_data(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateAccountRuleData()
        );
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, RemoveAccountRuleDataMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_can_skip_validation(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = array_merge($this->generateAccountRuleData(), ['skipValidation' => true])
        );
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, RemoveAccountRuleDataMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $data = $this->generateAccountRuleData();
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
        $data = $this->generateAccountRuleData();

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
        $data = $this->generateAccountRuleData();
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
    }

    /**
     * Generate a an account rule data.
     */
    protected function generateAccountRuleData(): array
    {
        return [
            'tankId' => $this->tank->public_key,
            'userId' => $this->account,
            'ruleSetId' => fake()->numberBetween(1, 100),
            'rule' => collect(DispatchRule::caseNamesAsArray())->random(),
        ];
    }
}
