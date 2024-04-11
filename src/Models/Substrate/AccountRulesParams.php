<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Illuminate\Support\Arr;

class AccountRulesParams
{
    /**
     * Create a new account rule params instance.
     */
    public function __construct(
        public ?WhitelistedCallersParams $whitelistedCallers = null,
        public ?RequireTokenParams $requireToken = null,
    ) {
    }

    /**
     * Create a new instance from the given parameters.
     */
    public function fromEncodable(mixed $params): self
    {
        ray($params);

        if (!empty($whitelist = Arr::get($params, 'WhitelistedCallers'))) {
            ray($whitelist);
            $this->whitelistedCallers = WhitelistedCallersParams::fromEncodable($whitelist);
        }

        if (!empty($required = Arr::get($params, 'RequireToken'))) {
            //            ray($required);
            $this->requireToken = RequireTokenParams::fromEncodable($required);
        }

        return $this;
    }

    /**
     * Returns the encodable representation of this instance.
     */
    public function toEncodable(): array
    {
        $params = [];

        if ($this->whitelistedCallers) {
            $params[] = $this->whitelistedCallers->toEncodable();
        }

        if ($this->requireToken) {
            $params[] = $this->requireToken->toEncodable();
        }

        return $params;
    }

    public function toArray(): array
    {
        return [
            'WhitelistedCallers' => $this->whitelistedCallers?->toArray(),
            'RequireToken' => $this->requireToken?->toArray(),
        ];
    }
}
