<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Illuminate\Support\Arr;

class PermittedCallsParams extends FuelTankRules
{
    /**
     * Creates a new instance.
     */
    public function __construct(
        public ?array $calls = [],
    ) {
    }

    /**
     * Creates a new instance from the given array.
     */
    public static function fromEncodable(array $params): self
    {
        return new self(
            calls: Arr::get($params, 'PermittedCalls.calls')
        );
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        return [
            'PermittedCalls' => $this->calls,
        ];
    }
}
