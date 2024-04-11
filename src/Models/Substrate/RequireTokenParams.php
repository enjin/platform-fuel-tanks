<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Illuminate\Support\Arr;

class RequireTokenParams extends FuelTankRules
{
    /**
     * Creates a new instance.
     */
    public function __construct(
        public string $collectionId,
        public string $tokenId,
    ) {
    }

    /**
     * Creates a new instance from the given array.
     */
    public static function fromEncodable(array $params): self
    {
        $collectionId = Arr::get($params, 'RequireToken.collectionId') ?? Arr::get($params, 'RequireToken.collection_id');
        $tokenId = Arr::get($params, 'RequireToken.tokenId') ?? Arr::get($params, 'RequireToken.token_id');

        return new self(
            collectionId: gmp_strval($collectionId),
            tokenId: gmp_strval($tokenId),
        );
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        return ['RequireToken' => [
            'collectionId' => $this->collectionId,
            'tokenId' => $this->tokenId,
        ]];
    }

    public function toArray(): array
    {
        return [
            'collectionId' => $this->collectionId,
            'tokenId' => $this->tokenId,
        ];
    }
}
