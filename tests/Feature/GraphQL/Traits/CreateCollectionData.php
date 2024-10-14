<?php

namespace Enjin\Platform\FuelTanks\Tests\Feature\GraphQL\Traits;

use Enjin\Platform\Models\Collection;
use Enjin\Platform\Models\Token;
use Enjin\Platform\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

trait CreateCollectionData
{
    /**
     * The wallet account.
     */
    protected Model $wallet;

    /**
     * The collection.
     */
    protected Model $collection;

    /**
     * The token.
     */
    protected Model $token;

    /**
     * Create collection data.
     */
    public function createCollectionData(?string $publicKey = null): void
    {
        $this->wallet = Wallet::firstOrCreate(
            [
                'public_key' => $publicKey ?: config('enjin-platform.chains.daemon-account'),
            ],
            Wallet::factory()->raw([
                'public_key' => $publicKey ?: config('enjin-platform.chains.daemon-account'),
            ]),
        );

        $this->collection = Collection::factory()->create([
            'collection_chain_id' => (string) fake()->unique()->numberBetween(2000),
            'owner_wallet_id' => $this->wallet->id,
            'max_token_count' => fake()->numberBetween(1),
            'max_token_supply' => (string) fake()->numberBetween(1),
            'force_collapsing_supply' => fake()->boolean(),
            'is_frozen' => false,
        ]);

        $this->token = Token::factory()->create([
            'collection_id' => $this->collection->id,
            'token_chain_id' => (string) fake()->unique()->numberBetween(2000),
            'supply' => (string) fake()->numberBetween(1),
            'cap' => null,
            'cap_supply' => null,
            'is_frozen' => false,
        ]);
    }
}
