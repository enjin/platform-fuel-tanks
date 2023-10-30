<?php

namespace Enjin\Platform\FuelTanks\Services\Blockchain\Implemetations;

use Enjin\Platform\Clients\Abstracts\WebsocketAbstract;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\MaxFuelBurnPerTransactionParams;
use Enjin\Platform\FuelTanks\Models\Substrate\RequireTokenParams;
use Enjin\Platform\FuelTanks\Models\Substrate\TankFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\UserAccountManagementParams;
use Enjin\Platform\FuelTanks\Models\Substrate\UserFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCallersParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCollectionsParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Blockchain\Implementations\Substrate as PlatformSubstrate;
use Illuminate\Support\Arr;

class Substrate extends PlatformSubstrate
{
    public function __construct(WebsocketAbstract $client)
    {
        $this->codec = new Codec();

        parent::__construct($client);
    }

    /**
     * Set user account management param object.
     */
    public function getUserAccountManagementParams(?array $args = null): ?UserAccountManagementParams
    {
        if (!is_null($existentialDeposit = Arr::get($args, 'reservesExistentialDeposit'))
            || !is_null($creationDeposit = Arr::get($args, 'reservesAccountCreationDeposit'))) {
            return new UserAccountManagementParams(
                $existentialDeposit ?? false,
                $creationDeposit ?? false
            );
        }

        return null;
    }

    /**
     * Set the dispatch rule params object.
     */
    public function getDispatchRulesParams(array $args): DispatchRulesParams
    {
        return new DispatchRulesParams(
            0, // TODO: Add ruleSet
            ($callers = Arr::get($args, 'whitelistedCallers'))
                ? new WhitelistedCallersParams($callers)
                : null,
            ($requireToken = Arr::get($args, 'requireToken'))
                ? new RequireTokenParams(Arr::get($requireToken, 'collectionId'), $this->encodeTokenId($requireToken))
                : null,
            ($collections = Arr::get($args, 'whitelistedCollections'))
                ? new WhitelistedCollectionsParams($collections)
                : null,
            ($maxFuelBurnPerTransaction = Arr::get($args, 'maxFuelBurnPerTransaction'))
                ? new MaxFuelBurnPerTransactionParams($maxFuelBurnPerTransaction)
                : null,
            ($userFuelBudget = Arr::get($args, 'userFuelBudget'))
                ? new UserFuelBudgetParams(Arr::get($userFuelBudget, 'amount'), Arr::get($userFuelBudget, 'resetPeriod'))
                : null,
            ($tankFuelBudget = Arr::get($args, 'tankFuelBudget'))
                ? new TankFuelBudgetParams(Arr::get($tankFuelBudget, 'amount'), Arr::get($tankFuelBudget, 'resetPeriod'))
                : null,
        );
    }

    /**
     * Set dispatch rules.
     */
    public function getDispatchRulesParamsArray(array $args): array
    {
        $dispatchRulesParams = [];
        if ($rules = Arr::get($args, 'dispatchRules')) {
            foreach ($rules as $rule) {
                $dispatchRulesParams[] = $this->getDispatchRulesParams($rule);
            }
        }

        return $dispatchRulesParams;
    }

    /**
     * Set account rule params object.
     */
    public function getAccountRulesParams(?array $args = null): ?AccountRulesParams
    {
        if (is_null($args)) {
            return null;
        }

        $accountRulesParams = null;
        $callers = Arr::get($args, 'accountRules.whitelistedCallers');
        $requireToken = Arr::get($args, 'accountRules.requireToken');
        if ($callers || $requireToken) {
            $accountRulesParams = new AccountRulesParams(
                $callers
                    ? new WhitelistedCallersParams($callers)
                    : null,
                $requireToken
                    ? new RequireTokenParams(Arr::get($requireToken, 'collectionId'), $this->encodeTokenId($requireToken))
                    : null
            );
        }

        return $accountRulesParams;
    }
}
