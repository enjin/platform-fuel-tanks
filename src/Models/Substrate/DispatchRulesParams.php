<?php

namespace Enjin\Platform\FuelTanks\Models\Substrate;

use Illuminate\Support\Str;

class DispatchRulesParams
{
    /**
     * Create a new dispatch rule params instance.
     */
    public function __construct(
        public ?int $ruleSetId = 0,
        public ?WhitelistedCallersParams $whitelistedCallers = null,
        public ?RequireTokenParams $requireToken = null,
        public ?WhitelistedCollectionsParams $whitelistedCollections = null,
        public ?MaxFuelBurnPerTransactionParams $maxFuelBurnPerTransaction = null,
        public ?UserFuelBudgetParams $userFuelBudget = null,
        public ?TankFuelBudgetParams $tankFuelBudget = null,
        public ?PermittedCallsParams $permittedCalls = null,
        public ?PermittedExtrinsicsParams $permittedExtrinsics = null,
        public ?bool $isFrozen = false,
    ) {
    }

    /**
     * Create a new instance from the given parameters.
     */
    public function fromEncodable(int $setId, mixed $params): self
    {
        $this->ruleSetId = $setId;
        $this->isFrozen = $params['isFrozen'];

        foreach ($params['rules'] as $rule) {
            $ruleParam = '\Enjin\Platform\FuelTanks\Models\Substrate\\' . ($ruleName = array_key_first($rule)) . 'Params';
            $ruleParams = $ruleParam::fromEncodable($rule);
            $this->{Str::camel($ruleName)} = $ruleParams;
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

        if ($this->whitelistedCollections) {
            $params[] = $this->whitelistedCollections->toEncodable();
        }

        if ($this->maxFuelBurnPerTransaction) {
            $params[] = $this->maxFuelBurnPerTransaction->toEncodable();
        }

        if ($this->userFuelBudget) {
            $params[] = $this->userFuelBudget->toEncodable();
        }

        if ($this->tankFuelBudget) {
            $params[] = $this->tankFuelBudget->toEncodable();
        }

        // We have to set an empty array for the permitted extrinsics here and encode manually later
        // due to what appears to be a bug in the Scale Codec library where it cannot encode a Call
        // type due to missing metadata when creating the Call ScaleInstance class.
        if ($this->permittedExtrinsics) {
            $params[] = ['PermittedExtrinsics' => ['extrinsics' => []]];
        }

        return $params;
    }
}
