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
        return new self(
            collectionId: gmp_strval(Arr::get($params, 'RequireToken.collectionId')),
            tokenId: gmp_strval(Arr::get($params, 'RequireToken.tokenId')),
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
}
