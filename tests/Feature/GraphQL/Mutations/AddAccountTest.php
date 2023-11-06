<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\AddAccountMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Illuminate\Support\Str;

class AddAccountTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'AddAccount';

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

    public function test_it_can_add_account(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = ['tankId' => $this->tank->public_key, 'userId' => resolve(SubstrateProvider::class)->public_key()]
        );
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, AddAccountMutation::getEncodableParams(...$params)),
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $publicKey = resolve(SubstrateProvider::class)->public_key();
        $response = $this->graphql(
            $this->method,
            ['tankId' => $publicKey, 'userId' => $publicKey],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => Str::random(300), 'userId' => $publicKey],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => 'Invalid', 'userId' => $publicKey],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id is not a valid substrate address.']],
            $response['error']
        );

        $tank = $this->createFuelTank();
        $wallet = Wallet::create(
            [
                'public_key' => $publicKey,
                'external_id' => fake()->unique()->uuid(),
                'managed' => fake()->boolean(),
                'verification_id' => fake()->unique()->uuid(),
                'network' => 'developer',
            ]
        );
        $tank->forceFill(['owner_wallet_id' => $wallet->id])->save();
        $response = $this->graphql(
            $this->method,
            ['tankId' => $tank->public_key, 'userId' => $publicKey],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id provided is not owned by you.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_user_id(): void
    {
        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userId' => 'Invalid'],
            true
        );
        $this->assertArraySubset(
            ['userId' => ['The user id is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userId' => null],
            true
        );
        $this->assertEquals(
            'Variable "$userId" of non-null type "String!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userId' => Str::random(300)],
            true
        );
        $this->assertArraySubset(
            ['userId' => ['The user id field must not be greater than 255 characters.']],
            $response['error']
        );

        $publicKey = resolve(SubstrateProvider::class)->public_key();
        FuelTankAccount::create([
            'wallet_id' => Wallet::create(['public_key' => $publicKey, 'network' => 'developer'])->id,
            'fuel_tank_id' => $this->tank->id,
        ]);
        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userId' => $publicKey],
            true
        );
        $this->assertArraySubset(
            ['userId' => ['The user id contains an account that already exists in the fuel tank.']],
            $response['error']
        );
    }
}
