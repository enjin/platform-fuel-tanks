<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\CreateFuelTankMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Services\Blockchain\Implemetations\Substrate;
use Enjin\Platform\Services\Serialization\Implementations\Substrate as SubstrateEncoder;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Str;

class CreateFuelTankTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'CreateFuelTank';

    public function test_it_can_create_fuel_tank(): void
    {
        $substrate = new SubstrateEncoder();
        $response = $this->graphql($this->method, $data = $this->generateData());

        $blockchainService = resolve(Substrate::class);
        $data['userAccountManagement'] = $blockchainService->getUserAccountManagementParams($data);
        $data['dispatchRules'] = $blockchainService->getDispatchRulesParamsArray($data);
        $data['accountRules'] = $blockchainService->getAccountRulesParams($data);

        $this->assertEquals(
            $response['encodedData'],
            $substrate->encode($this->method, CreateFuelTankMutation::getEncodableParams(...$data))
        );
    }

    public function test_it_can_create_fuel_tank_with_zero_values(): void
    {
        $response = $this->graphql($this->method, $data = $this->generateData(true, 0));

        $blockchainService = resolve(Substrate::class);
        $data['userAccountManagement'] = $blockchainService->getUserAccountManagementParams($data);
        $data['dispatchRules'] = $blockchainService->getDispatchRulesParamsArray($data);
        $data['accountRules'] = $blockchainService->getAccountRulesParams($data);

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, CreateFuelTankMutation::getEncodableParams(...$data))
        );
    }

    public function test_it_will_fail_with_invalid_parameter_name(): void
    {
        $data = $this->generateData();
        FuelTank::create(['name' => $data['name'], 'public_key' => $data['account'], 'owner_wallet_id' => $this->wallet->id]);

        $response = $this->graphql($this->method, $data, true);
        $this->assertArraySubset(
            ['name' => ['The name has already been taken.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['name' => "This is a very long name that will fail because it's too long"]),
            true
        );
        $this->assertArraySubset(
            ['name' => ['The name field must not be greater than 32 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['name' => null]),
            true
        );
        $this->assertEquals('Variable "$name" of non-null type "String!" must not be null.', $response['error']);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['name' => '']),
            true
        );
        $this->assertArraySubset(
            ['name' => ['The name field must have a value.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_provides_deposit(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['providesDeposit' => 'Invalid']),
            true
        );
        $this->assertEquals('Variable "$providesDeposit" got invalid value "Invalid"; Boolean cannot represent a non boolean value: "Invalid"', $response['error']);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['providesDeposit' => null]),
            true
        );
        $this->assertEquals('Variable "$providesDeposit" of non-null type "Boolean!" must not be null.', $response['error']);
    }

    public function test_it_will_fail_with_invalid_parameter_user_management(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['reservesExistentialDeposit' => 'Invalid']),
            true
        );
        $this->assertEquals('Variable "$reservesExistentialDeposit" got invalid value "Invalid"; Boolean cannot represent a non boolean value: "Invalid"', $response['error']);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['reservesAccountCreationDeposit' => 'Invalid']),
            true
        );
        $this->assertEquals('Variable "$reservesAccountCreationDeposit" got invalid value "Invalid"; Boolean cannot represent a non boolean value: "Invalid"', $response['error']);
    }

    public function test_it_will_fail_with_invalid_parameter_account_rules_whitelisted_callers(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['whitelistedCallers' => ['Invalid']]]),
            true
        );
        $this->assertArraySubset(
            ['accountRules.whitelistedCallers.0' => ['The accountRules.whitelistedCallers.0 is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['whitelistedCallers' => [$data['account'], $data['account']]]]),
            true
        );
        $this->assertArraySubset(
            ['accountRules.whitelistedCallers.0' => ['The accountRules.whitelistedCallers.0 field has a duplicate value.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['whitelistedCallers' => [Str::random(300)]]]),
            true
        );
        $this->assertArraySubset(
            ['accountRules.whitelistedCallers.0' => ['The accountRules.whitelistedCallers.0 field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['whitelistedCallers' => []]]),
            true
        );
        $this->assertArraySubset(
            ['accountRules.whitelistedCallers' => ['The account rules.whitelisted callers field must have at least 1 items.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['whitelistedCallers' => null]]),
            true
        );
        $this->assertNotEmpty($response);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['whitelistedCallers' => '']]),
            true
        );
        $this->assertArraySubset(
            ['accountRules.whitelistedCallers.0' => ['The accountRules.whitelistedCallers.0 field must have a value.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_account_rules_require_token(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['requireToken' => ['collectionId' => 1, 'tokenId' => ['integer' => 1]]]]),
            true
        );
        $this->assertArraySubset(
            [
                'accountRules.requireToken.collectionId' => ['The selected account rules.require token.collection id is invalid.'],
            ],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['requireToken' => ['collectionId' => 1, 'tokenId' => null]]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$accountRules" got invalid value null at "accountRules.requireToken.tokenId"; Expected non-nullable type "EncodableTokenIdInput!" not to be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['requireToken' => ['collectionId' => null, 'tokenId' => ['integer' => 1]]]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$accountRules" got invalid value null at "accountRules.requireToken.collectionId"; Expected non-nullable type',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['accountRules' => ['requireToken' => ['collectionId' => Hex::MAX_UINT256 + 1, 'tokenId' => ['integer' => Hex::MAX_UINT256 + 1]]]]),
            true
        );
        $this->assertArraySubset(
            [
                0 => [
                    'message' => 'Variable "$accountRules" got invalid value 1.1579208923732E+77 at "accountRules.requireToken.collectionId"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
                1 => [
                    'message' => 'Variable "$accountRules" got invalid value 1.1579208923732E+77 at "accountRules.requireToken.tokenId.integer"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
            ],
            $response['errors']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_whitelisted_callers(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCallers' => ['Invalid']]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCallers.0' => ['The dispatchRules.0.whitelistedCallers.0 is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCallers' => [$data['account'], $data['account']]]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCallers.0' => ['The dispatchRules.0.whitelistedCallers.0 field has a duplicate value.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCallers' => [Str::random(300)]]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCallers.0' => ['The dispatchRules.0.whitelistedCallers.0 field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCallers' => []]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCallers' => ['The dispatchRules.0.whitelistedCallers field must have at least 1 items.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCallers' => null]]]),
            true
        );
        $this->assertNotEmpty($response['data']);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCallers' => '']]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCallers.0' => ['The dispatchRules.0.whitelistedCallers.0 field must have a value.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_require_token(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['requireToken' => ['collectionId' => 1, 'tokenId' => ['integer' => 1]]]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.requireToken.collectionId' => ['The selected dispatchRules.0.requireToken.collectionId is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['requireToken' => ['collectionId' => 1, 'tokenId' => null]]]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$dispatchRules" got invalid value null at "dispatchRules[0].requireToken.tokenId"; Expected non-nullable type "EncodableTokenIdInput!" not to be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['requireToken' => ['collectionId' => null, 'tokenId' => ['integer' => 1]]]]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$dispatchRules" got invalid value null at "dispatchRules[0].requireToken.collectionId"; Expected non-nullable type',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['requireToken' => ['collectionId' => Hex::MAX_UINT256 + 1, 'tokenId' => ['integer' => Hex::MAX_UINT256 + 1]]]]]),
            true
        );
        $this->assertArraySubset(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules[0].requireToken.collectionId"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules[0].requireToken.tokenId.integer"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
            ],
            $response['errors']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_whitelisted_collections(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCollections' => ['Invalid']]]]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules[0].whitelistedCollections[0]"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCollections' => [1, 1]]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCollections.0' => ['The dispatchRules.0.whitelistedCollections.0 field has a duplicate value.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCollections' => [5000]]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCollections.0' => ['The selected dispatchRules.0.whitelistedCollections.0 is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCollections' => []]]]),
            true
        );
        $this->assertArraySubset(
            ['dispatchRules.0.whitelistedCollections' => ['The dispatchRules.0.whitelistedCollections field must have at least 1 items.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCollections' => null]]]),
            true
        );
        $this->assertNotEmpty($response);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['whitelistedCollections' => [Hex::MAX_UINT256 + 1]]]]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules[0].whitelistedCollections[0]"; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_max_fuel_burn_per_transaction(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['maxFuelBurnPerTransaction' => 'Invalid']]]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules[0].maxFuelBurnPerTransaction"; Cannot represent following value as uint256: "Invalid"',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['maxFuelBurnPerTransaction' => Hex::MAX_UINT128 + 1]]]),
            true
        );
        $this->assertEquals(
            'Variable "$dispatchRules" got invalid value 3.4028236692094E+38 at "dispatchRules[0].maxFuelBurnPerTransaction"; Cannot represent following value as uint256: 3.4028236692094E+38',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_user_fuel_budget(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['userFuelBudget' => ['amount' => 'Invalid', 'resetPeriod' => 'Invalid']]]]),
            true
        );
        $this->assertArraySubset(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules[0].userFuelBudget.amount"; Cannot represent following value as uint256: "Invalid"',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules[0].userFuelBudget.resetPeriod"; Cannot represent following value as uint256: "Invalid"',
                ],
            ],
            $response['errors']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['userFuelBudget' => ['amount' => Hex::MAX_UINT256 + 1, 'resetPeriod' => Hex::MAX_UINT256 + 1]]]]),
            true
        );
        $this->assertArraySubset(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules[0].userFuelBudget.amount"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules[0].userFuelBudget.resetPeriod"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
            ],
            $response['errors']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_dispatch_rules_tank_fuel_budget(): void
    {
        $data = $this->generateData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['tankFuelBudget' => ['amount' => 'Invalid', 'resetPeriod' => 'Invalid']]]]),
            true
        );
        $this->assertArraySubset(
            [
                ['message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules[0].tankFuelBudget.amount"; Cannot represent following value as uint256: "Invalid"'],
                ['message' => 'Variable "$dispatchRules" got invalid value "Invalid" at "dispatchRules[0].tankFuelBudget.resetPeriod"; Cannot represent following value as uint256: "Invalid"'],
            ],
            $response['errors']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['dispatchRules' => [['tankFuelBudget' => ['amount' => Hex::MAX_UINT256 + 1, 'resetPeriod' => Hex::MAX_UINT256 + 1]]]]),
            true
        );
        $this->assertArraySubset(
            [
                0 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules[0].tankFuelBudget.amount"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
                1 => [
                    'message' => 'Variable "$dispatchRules" got invalid value 1.1579208923732E+77 at "dispatchRules[0].tankFuelBudget.resetPeriod"; Cannot represent following value as uint256: 1.1579208923732E+77',
                ],
            ],
            $response['errors']
        );
    }
}
