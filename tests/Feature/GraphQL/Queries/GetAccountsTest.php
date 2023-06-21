<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Queries;

use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Illuminate\Support\Str;

class GetAccountsTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'GetAccounts';

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

    public function test_it_can_get_fuel_tanks(): void
    {
        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key]
        );
        $this->assertEquals($response['totalCount'], 5);
    }

    public function test_it_will_fail_with_invalid_tank_id(): void
    {
        $response = $this->graphql($this->method, ['tankId' => null], true);
        $this->assertEquals(
            'Variable "$tankId" of non-null type "String!" must not be null.',
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankId' => ''], true);
        $this->assertArraySubset(
            ['tankId' => ['The tank id field must have a value.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankId' => Str::random(10)], true);
        $this->assertArraySubset(
            ['tankId' => ['The tank id is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankId' => Str::random(300)], true);
        $this->assertArraySubset(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => resolve(SubstrateProvider::class)->public_key()],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );
    }
}
