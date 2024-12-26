<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\DestroyFuelTankMutation;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Illuminate\Support\Str;

class DestroyFuelTankTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'DestroyFuelTank';

    public function test_it_can_destroy_fuel_tank(): void
    {
        $tank = $this->createFuelTank();
        $response = $this->graphql($this->method, $params = ['tankId' => $tank->public_key]);
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DestroyFuelTankMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_can_skip_validation(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = ['tankId' => resolve(SubstrateProvider::class)->public_key(), 'skipValidation' => true]
        );

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, DestroyFuelTankMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_will_fail_with_invalid_parameter(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $response = $this->graphql($this->method, ['tankId' => $pubicKey], true);
        $this->assertArrayContainsArray(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankId' => Str::random(300)], true);
        $this->assertArrayContainsArray(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql($this->method, ['tankId' => 'Invalid'], true);
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
        $response = $this->graphql($this->method, ['tankId' => $tank->public_key], true);
        $this->assertArrayContainsArray(
            ['tankId' => ['The tank id provided is not owned by you.']],
            $response['error']
        );
    }
}
