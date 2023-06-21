<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Enjin\Platform\Support\SS58Address;
use Illuminate\Support\Arr;

class WhitelistedCallersParams extends FuelTankRules
{
    protected ?array $callers;

    /**
     * Creates a new instance.
     */
    public function __construct(?array $callers = [])
    {
        $this->callers = array_map(
            fn ($caller) => SS58Address::getPublicKey($caller),
            $callers
        );
    }

    /**
     * Creates a new instance from the given array.
     */
    public static function fromEncodable(array $params): self
    {
        return new self(
            callers: Arr::get($params, 'WhitelistedCallers'),
        );
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        return [
            'WhitelistedCallers' => $this->callers,
        ];
    }
}
