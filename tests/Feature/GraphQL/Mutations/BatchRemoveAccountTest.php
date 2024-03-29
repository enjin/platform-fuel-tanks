<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\BatchRemoveAccountMutation;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BatchRemoveAccountTest extends TestCaseGraphQL
{
    /**
     * The graphql method.
     */
    protected string $method = 'BatchRemoveAccount';

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

    public function test_it_can_batch_remove_account(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = ['tankId' => $this->tank->public_key, 'userIds' => [$this->account]]
        );
        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, BatchRemoveAccountMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_will_fail_with_invalid_parameter_tank_id(): void
    {
        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $response = $this->graphql(
            $this->method,
            ['tankId' => $pubicKey, 'userIds' => [$this->account]],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The selected tankId is invalid.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => Str::random(300), 'userIds' => [$this->account]],
            true
        );
        $this->assertArraySubset(
            ['tankId' => ['The tank id field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => 'Invalid', 'userIds' => [$this->account]],
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
            ['tankId' => $tank->public_key, 'userIds' => [$this->account]],
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
            ['tankId' => $this->tank->public_key, 'userIds' => 'Invalid'],
            true
        );
        $this->assertArraySubset(
            ['userIds.0' => ['The userIds.0 is not a valid substrate address.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userIds' => null],
            true
        );
        $this->assertEquals(
            'Variable "$userIds" of non-null type "[String!]!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userIds' => [Str::random(300)]],
            true
        );
        $this->assertArraySubset(
            ['userIds.0' => ['The userIds.0 field must not be greater than 255 characters.']],
            $response['error']
        );

        $pubicKey = resolve(SubstrateProvider::class)->public_key();
        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userIds' => [$pubicKey, $pubicKey]],
            true
        );
        $this->assertArraySubset(
            [
                'userIds.0' => ['The userIds.0 field has a duplicate value.'],
                'userIds.1' => ['The userIds.1 field has a duplicate value.'],
            ],
            $response['error']
        );


        FuelTankAccount::where('fuel_tank_id', $this->tank->id)->delete();
        $response = $this->graphql(
            $this->method,
            ['tankId' => $this->tank->public_key, 'userIds' => [$this->account]],
            true
        );
        $this->assertArraySubset(
            ['userIds' => ["The user ids contains an account that doesn't exist in the fuel tank."]],
            $response['error']
        );

        $provider = resolve(SubstrateProvider::class);
        $response = $this->graphql(
            $this->method,
            [
                'tankId' => $this->tank->public_key,
                'userIds' => Collection::range(1, 101)->map(fn ($row) => $provider->public_key())->toArray()],
            true
        );
        $this->assertArraySubset(
            ['userIds' => ['The user ids field must not have more than 100 items.']],
            $response['error']
        );
    }
}
