<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Illuminate\Support\Arr;

class PermittedExtrinsicsParams extends FuelTankRules
{
    /**
     * Creates a new instance.
     */
    public function __construct(
        public ?array $extrinsics = [],
    ) {
    }

    /**
     * Creates a new instance from the given array.
     */
    public static function fromEncodable(array $params): self
    {
        return new self(
            extrinsics: Arr::get($params, 'PermittedExtrinsics')
        );
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        return [
            'PermittedExtrinsics' => $this->extrinsics,
        ];
    }
}
