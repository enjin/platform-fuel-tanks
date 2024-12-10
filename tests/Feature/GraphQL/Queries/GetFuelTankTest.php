<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Queries;

use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetFuelTankTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'GetFuelTank';

    /**
     * The fuel tanks.
     */
    protected Collection $tanks;

    public function test_it_can_get_fuel_tank(): void
    {
        $tank = $this->createFuelTank();
        $response = $this->graphql(
            $this->method,
            ['name' => $tank->name]
        );
        $this->assertNotEmpty($response);

        $response = $this->graphql(
            $this->method,
            ['tankId' => $tank->public_key]
        );
        $this->assertNotEmpty($response);
    }

    public function test_it_will_fail_with_invalid_name(): void
    {
        $response = $this->graphql($this->method, ['name' => null], true);
        $this->assertArrayContainsArray(
            [
                'name' => ['The name field is required when tank id is not present.'],
                'tankId' => ['The tank id field is required when name is not present.'],
            ],
            $response['error']
        );

        $response = $this->graphql($this->method, ['name' => ''], true);
        $this->assertArrayContainsArray(
            ['name' => ['The name field is required when tank id is not present.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['name' => Str::random(50)], true);
        $this->assertArrayContainsArray(
            ['name' => ['The name field must not be greater than 32 characters.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['name' => Str::random(6)], true);
        $this->assertArrayContainsArray(
            ['name' => ['The selected name is invalid.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_tank_id(): void
    {
        $response = $this->graphql($this->method, ['tankId' => null], true);
        $this->assertArrayContainsArray(
            [
                'name' => ['The name field is required when tank id is not present.'],
                'tankId' => ['The tank id field is required when name is not present.'],
            ],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankId' => ''], true);
        $this->assertArrayContainsArray(
            ['tankId' => ['The tank id field is required when name is not present.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankId' => Str::random(50)], true);
        $this->assertArrayContainsArray(
            ['tankId' => ['The tank id is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => resolve(SubstrateProvider::class)->public_key()],
            true
        );
        $this->assertArrayContainsArray(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );
    }
}
