<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\InsertRuleSetMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Services\Blockchain\Implemetations\Substrate;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class InsertRuleSetTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'InsertRuleSet';

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

    public function test_it_can_insert_rule_set(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateRuleSet()
        );

        $params['dispatchRules'] = resolve(Substrate::class)->getDispatchRulesParams($params['dispatchRules']);

        $encodedData = TransactionSerializer::encode($this->method, InsertRuleSetMutation::getEncodableParams(...$params));
        $requireAccount = Str::take($encodedData, -2);
        $encodedData = Str::take($encodedData, Str::length($encodedData) - 6);
        $encodedData .= Arr::get($params['dispatchRules']->permittedExtrinsics->toEncodable(), 'PermittedExtrinsics.extrinsics');
        $encodedData .= $requireAccount;

        $this->assertEquals(
            $encodedData,
            $response['encodedData'],
        );
    }

    public function test_it_can_insert_rule_set_with_require_account(): void
    {
        $params = $this->generateRuleSet();
        $params['requireAccount'] = true;

        $response = $this->graphql(
            $this->method,
            $params
        );

        $params['dispatchRules'] = resolve(Substrate::class)->getDispatchRulesParams($params['dispatchRules']);

        $encodedData = TransactionSerializer::encode($this->method, InsertRuleSetMutation::getEncodableParams(...$params));
        $requireAccount = Str::take($encodedData, -2);
        $encodedData = Str::take($encodedData, Str::length($encodedData) - 6);
        $encodedData .= Arr::get($params['dispatchRules']->permittedExtrinsics->toEncodable(), 'PermittedExtrinsics.extrinsics');
        $encodedData .= $requireAccount;

        $this->assertEquals(
            $encodedData,
            $response['encodedData'],
        );
    }

    public function test_it_can_skip_validation(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = [
                'tankId' => resolve(SubstrateProvider::class)->public_key(),
                'ruleSetId' => fake()->numberBetween(50000, 100000),
                ...Arr::only($this->generateData(false), 'dispatchRules'),
                'requireAccount' => true,
                'skipValidation' => true,
            ]
        );

        $params['dispatchRules'] = resolve(Substrate::class)->getDispatchRulesParams($params['dispatchRules']);

        $encodedData = TransactionSerializer::encode($this->method, InsertRuleSetMutation::getEncodableParams(...$params));
        $requireAccount = Str::take($encodedData, -2);
        $encodedData = Str::take($encodedData, Str::length($encodedData) - 6);
        $encodedData .= Arr::get($params['dispatchRules']->permittedExtrinsics->toEncodable(), 'PermittedExtrinsics.extrinsics');
        $encodedData .= $requireAccount;

        $this->assertEquals(
            $encodedData,
            $response['encodedData'],
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $data = $this->generateRuleSet();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId' => $pubicKey]),
            true
        );
        $this->assertArrayContainsArray(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId' => Str::random(300)]),
            true
        );
        $this->assertArrayContainsArray(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
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
            $this->method,
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
        $data = $this->generateRuleSet();
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

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_whitelisted_callers(): void
    {
        $data = $this->generateRuleSet();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCallers' => ['Invalid']]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCallers.0' => ['The dispatchRules.whitelistedCallers.0 is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCallers' => [$data['tankId'], $data['tankId']]]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCallers.0' => ['The dispatchRules.whitelistedCallers.0 field has a duplicate value.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCallers' => [Str::random(300)]]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCallers.0' => ['The dispatchRules.whitelistedCallers.0 field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCallers' => []]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCallers' => ['The dispatch rules.whitelisted callers field must have at least 1 items.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCallers' => null]]),
            true
        );
        $this->assertNotEmpty($response['data']);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCallers' => '']]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCallers.0' => ['The dispatchRules.whitelistedCallers.0 field must have a value.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_require_token(): void
    {
        $data = $this->generateRuleSet();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['requireToken' => ['collectionId' => 1, 'tokenId' => ['integer' => 1]]]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.requireToken.collectionId' => ['The selected dispatch rules.require token.collection id is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['requireToken' => ['collectionId' => 1, 'tokenId' => null]]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$dispatchRules" got invalid value null at "dispatchRules.requireToken.tokenId"; Expected non-nullable type "EncodableTokenIdInput!" not to be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['requireToken' => ['collectionId' => null, 'tokenId' => ['integer' => 1]]]]),
            true
        );
        $this->assertStringContainsString(
            'invalid value null at "dispatchRules.requireToken.collectionId"; Expected non-nullable type "BigInt!" not to be null',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['requireToken' => ['collectionId' => Hex::MAX_UINT256 + 1, 'tokenId' => ['integer' => Hex::MAX_UINT256 + 1]]]]),
            true
        );
        $this->assertArrayContainsArray(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.requireToken.collectionId"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.requireToken.tokenId.integer"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
            ],
            $response['errors']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_whitelisted_collections(): void
    {
        $data = $this->generateRuleSet();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCollections' => ['Invalid']]]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules.whitelistedCollections[0]"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCollections' => [1, 1]]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCollections.0' => ['The dispatchRules.whitelistedCollections.0 field has a duplicate value.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCollections' => [5000]]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCollections.0' => ['The selected dispatchRules.whitelistedCollections.0 is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCollections' => []]]),
            true
        );
        $this->assertArrayContainsArray(
            ['dispatchRules.whitelistedCollections' => ['The dispatch rules.whitelisted collections field must have at least 1 items.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCollections' => null]]),
            true
        );
        $this->assertNotEmpty($response);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['whitelistedCollections' => [Hex::MAX_UINT256 + 1]]]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.whitelistedCollections[0]"; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_max_fuel_burn_per_transaction(): void
    {
        $data = $this->generateRuleSet();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['maxFuelBurnPerTransaction' => 'Invalid']]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules.maxFuelBurnPerTransaction"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['maxFuelBurnPerTransaction' => Hex::MAX_UINT256 + 1]]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.maxFuelBurnPerTransaction"; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_user_fuel_budget(): void
    {
        $data = $this->generateRuleSet();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['userFuelBudget' => ['amount' => 'Invalid', 'resetPeriod' => 'Invalid']]]),
            true
        );
        $this->assertArrayContainsArray(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules.userFuelBudget.amount"; Cannot represent following value as uint256: "Invalid"',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules.userFuelBudget.resetPeriod"; Cannot represent following value as uint256: "Invalid"',
                ],
            ],
            $response['errors']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['userFuelBudget' => ['amount' => Hex::MAX_UINT256 + 1, 'resetPeriod' => Hex::MAX_UINT256 + 1]]]),
            true
        );
        $this->assertArrayContainsArray(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.userFuelBudget.amount"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.userFuelBudget.resetPeriod"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
            ],
            $response['errors']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_tank_fuel_budget(): void
    {
        $data = $this->generateRuleSet();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['tankFuelBudget' => ['amount' => 'Invalid', 'resetPeriod' => 'Invalid']]]),
            true
        );
        $this->assertArrayContainsArray(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules.tankFuelBudget.amount"; Cannot represent following value as uint256: "Invalid"',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules.tankFuelBudget.resetPeriod"; Cannot represent following value as uint256: "Invalid"',
                ],
            ],
            $response['errors']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => ['tankFuelBudget' => ['amount' => Hex::MAX_UINT256 + 1, 'resetPeriod' => Hex::MAX_UINT256 + 1]]]),
            true
        );
        $this->assertArrayContainsArray(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.tankFuelBudget.amount"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules.tankFuelBudget.resetPeriod"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
            ],
            $response['errors']
        );
    }

    /**
     * Generate a valid consumption data.
     */
    protected function generateRuleSet(): array
    {
        return [
            'tankId' => $this->tank->public_key,
            'ruleSetId' => fake()->numberBetween(50000, 100000),
            ...Arr::only($this->generateData(false), 'dispatchRules'),
            'requireAccount' => true,
        ];
    }
}
