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
        public ?WhitelistedPalletsParams $whitelistedPallets = null,
        public ?bool $isFrozen = false,
    ) {
    }

    /**
     * Create a new instance from the given parameters.
     */
    public function fromEncodable(int $setId, mixed $params): self
    {
        $this->ruleSetId = $setId;
        $this->isFrozen = $params['isFrozen'] ?? false;
//        ray($params);

        foreach ($params['rules'] as $rule) {
//            ray($rule);

            ray($rule);
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

        if ($this->whitelistedPallets) {
            $params[] = $this->whitelistedPallets->toEncodable();
        }

        if ($this->permittedCalls) {
            $params[] = $this->permittedCalls->toEncodable();
        }

        if ($this->permittedExtrinsics) {
            $params[] = $this->permittedExtrinsics->toEncodable();
        }

        return $params;
    }
}
