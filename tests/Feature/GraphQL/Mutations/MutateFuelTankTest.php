<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MutateFuelTankTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'MutateFuelTank';

    public function test_it_can_mutate_fuel_tank(): void
    {
        $data = $this->generateFuelTankData();
        $response = $this->graphql($this->method, $data);
        $this->assertEquals(
            $response['encodedData'],
            $this->service->updateFuelTank($data)->encoded_data
        );
    }

    public function test_it_will_fail_with_invalid_parameter_mutation(): void
    {
        $data = $this->generateFuelTankData();
        $response = $this->graphql($this->method, array_merge($data, ['mutation'=>[]]), true);
        $this->assertArraySubset(
            ['mutation' => ['The mutation field is required.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $data = $this->generateFuelTankData();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId' => 'Invalid wallet address']),
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
            array_merge($data, ['tankId'=> null]),
            true
        );
        $this->assertEquals('Variable "$tankId" of non-null type "String!" must not be null.', $response['error']);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['tankId'=> '']),
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id field must have a value.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_provides_deposit(): void
    {
        $data = $this->generateFuelTankData();

        Arr::set($data, 'mutation.providesDeposit', 'Invalid');
        $response = $this->graphql($this->method, $data, true);
        $this->assertStringContainsString('got invalid value "Invalid" at "mutation.providesDeposit"; Boolean cannot represent a non boolean value', $response['error']);

        Arr::set($data, 'mutation.providesDeposit', null);
        $response = $this->graphql($this->method, $data, true);
        $this->assertNotEmpty($response['data']);
    }

    public function test_it_will_fail_with_invalid_parameter_user_management(): void
    {
        $data = $this->generateFuelTankData();

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.reservesExistentialDeposit', 'Invalid');
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString('invalid value "Invalid" at "mutation.reservesExistentialDeposit"; Boolean cannot represent a non boolean value', $response['error']);

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.reservesAccountCreationDeposit', 'Invalid');
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString('invalid value "Invalid" at "mutation.reservesAccountCreationDeposit"; Boolean cannot represent a non boolean value', $response['error']);

        Arr::set($data, 'mutation.reservesExistentialDeposit', null);
        Arr::set($data, 'mutation.reservesAccountCreationDeposit', null);
        $response = $this->graphql($this->method, $data, true);
        $this->assertNotEmpty($response['data']);
    }

    public function test_it_will_fail_with_invalid_parameter_account_rules_whitelisted_callers(): void
    {
        $data = $this->generateFuelTankData();

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.whitelistedCallers', 'Invalid');
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            ['mutation.accountRules.whitelistedCallers.0' => ['The mutation.accountRules.whitelistedCallers.0 is not a valid substrate address.']],
            $response['error']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.whitelistedCallers', [$data['tankId'], $data['tankId']]);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            [
                'mutation.accountRules.whitelistedCallers.0' => ['The mutation.accountRules.whitelistedCallers.0 field has a duplicate value.'],
                'mutation.accountRules.whitelistedCallers.1' => ['The mutation.accountRules.whitelistedCallers.1 field has a duplicate value.'],
            ],
            $response['error']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.whitelistedCallers', [Str::random(300)]);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            ['mutation.accountRules.whitelistedCallers.0' => ['The mutation.accountRules.whitelistedCallers.0 field must not be greater than 255 characters.']],
            $response['error']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.whitelistedCallers', []);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            ['mutation.accountRules.whitelistedCallers' => ['The mutation.account rules.whitelisted callers field must have at least 1 items.']],
            $response['error']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.whitelistedCallers', null);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertNotEmpty($response['data']);

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.whitelistedCallers', '');
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            ['mutation.accountRules.whitelistedCallers.0' => ['The mutation.accountRules.whitelistedCallers.0 field must have a value.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_account_rules_require_token(): void
    {
        $data = $this->generateFuelTankData();

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.requireToken', ['collectionId' => 1, 'tokenId'=> ['integer' => 1]]);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertArraySubset(
            ['mutation.accountRules.requireToken.collectionId' => ['The selected mutation.account rules.require token.collection id is invalid.']],
            $response['error']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.requireToken', ['collectionId' => null, 'tokenId'=> null]);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString(
            'value null at "mutation.accountRules.requireToken.collectionId"; Expected non-nullable type "BigInt!" not to be null',
            $response['errors'][0]['message']
        );
        $this->assertStringContainsString(
            'Variable "$mutation" got invalid value null at "mutation.accountRules.requireToken.tokenId"; Expected non-nullable type "EncodableTokenIdInput!" not to be null.',
            $response['errors'][1]['message']
        );

        $invalidData = $data;
        Arr::set($invalidData, 'mutation.accountRules.requireToken', ['collectionId' => Hex::MAX_UINT256 + 1, 'tokenId'=> ['integer' => Hex::MAX_UINT256 + 1]]);
        $response = $this->graphql($this->method, $invalidData, true);
        $this->assertStringContainsString(
            'value 1.1579208923732E+77 at "mutation.accountRules.requireToken.collectionId"; Cannot represent following value as uint256',
            $response['errors'][0]['message']
        );
        $this->assertStringContainsString(
            'Variable "$mutation" got invalid value 1.1579208923732E+77 at "mutation.accountRules.requireToken.tokenId.integer"; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['errors'][1]['message']
        );
    }

    /**
     * Creates fuel tank data.
     */
    protected function generateFuelTankData(): array
    {
        return [
            'tankId' => $this->createFuelTank()->public_key,
            'mutation' => Arr::except($this->generateData(), ['name', 'account', 'dispatchRules']),
        ];
    }
}
