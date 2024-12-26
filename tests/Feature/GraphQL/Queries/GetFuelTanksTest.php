<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Queries;

use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetFuelTanksTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'GetFuelTanks';

    /**
     * The fuel tanks.
     */
    protected Collection $tanks;

    /**
     * Setup test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tanks = $this->createFuelTank(10);
    }

    public function test_it_can_get_fuel_tanks(): void
    {
        $response = $this->graphql(
            $this->method,
            ['names' => $this->tanks->pluck('name')->toArray()]
        );
        $this->assertEquals($response['totalCount'], 10);

        $response = $this->graphql(
            $this->method,
            ['tankIds' => $this->tanks->pluck('public_key')->toArray()]
        );
        $this->assertEquals($response['totalCount'], 10);

        $response = $this->graphql($this->method);
        $this->assertNotEmpty($response['totalCount']);
    }

    public function test_it_will_fail_with_invalid_names(): void
    {
        $response = $this->graphql($this->method, ['names' => ['']], true);
        $this->assertArrayContainsArray(
            ['names.0' => ['The names.0 field must have a value.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['names' => [Str::random(50)]], true);
        $this->assertArrayContainsArray(
            ['names.0' => ['The names.0 field must not be greater than 32 characters.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['names' => ['duplicate', 'duplicate']], true);
        $this->assertArrayContainsArray(
            ['names.0' => ['The names.0 field has a duplicate value.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_tank_ids(): void
    {
        $response = $this->graphql($this->method, ['tankIds' => ['']], true);
        $this->assertArrayContainsArray(
            ['tankIds.0' => ['The tankIds.0 field must have a value.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankIds' => [Str::random(50)]], true);
        $this->assertArrayContainsArray(
            ['tankIds.0' => ['The tankIds.0 is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankIds' => ['duplicate', 'duplicate']], true);
        $this->assertArrayContainsArray(
            ['tankIds.0' => ['The tankIds.0 field has a duplicate value.']],
            $response['error']
        );
    }
}
