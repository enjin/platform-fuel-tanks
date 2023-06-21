<?php

namespace Enjin\Platform\FuelTanks\Services;

use Enjin\Platform\FuelTanks\Enums\DispatchCall;
use Enjin\Platform\FuelTanks\Enums\DispatchRule;
use Enjin\Platform\FuelTanks\Exceptions\FuelTanksException;
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
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\Models\Laravel\Transaction;
use Enjin\Platform\Services\Database\TransactionService as DatabaseTransactionService;
use Enjin\Platform\Services\Database\WalletService;
use Enjin\Platform\Support\SS58Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Facades\GraphQL;

class TransactionService extends DatabaseTransactionService
{
    use HasEncodableTokenId;

    /**
     * Create a new service instance.
     */
    public function __construct(
        public readonly Codec $codec,
        public readonly WalletService $wallet
    ) {
    }

    /**
     * Removes an account rule data from a fuel tank.
     */
    public function removeAccountRuleData(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->removeAccountRuleData(
                $account,
                SS58Address::getPublicKey(Arr::get($args, 'userId')),
                Arr::get($args, 'ruleSetId'),
                DispatchRule::getEnumCase(Arr::get($args, 'rule'))->toKind()
            ),
            'method' => 'RemoveAccountRuleData',
        ]);
    }

    /**
     * Removes a rule set from a fuel tank.
     */
    public function removeRuleSet(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->removeRuleSet(
                $account,
                Arr::get($args, 'ruleSetId')
            ),
            'method' => 'RemoveRuleSet',
        ]);
    }

    /**
     * Inserts a new rule set for a fuel tank.
     */
    public function insertRuleSet(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->insertRuleSet(
                $account,
                Arr::get($args, 'ruleSetId'),
                $this->setDispatchRulesParams($args)
            ),
            'method' => 'InsertRuleSet',
        ]);
    }

    /**
     * Schedule a freeze state mutation for a fuel tank.
     */
    public function scheduleMutateFreezeState(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->scheduleMutateFreezeState(
                $account,
                Arr::get($args, 'isFrozen', false),
                Arr::get($args, 'ruleSetId'),
            ),
            'method' => 'ScheduleMutateFreezeState',
        ]);
    }

    /**
     * Delete a fuel tank.
     */
    public function deleteFuelTank(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->destroyFuelTank($account),
            'method' => 'DestroyFuelTank',
        ]);
    }

    /**
     * Adds a fuel tank in batch.
     */
    public function batchAddAccount(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->batchAddAccount(
                $account,
                collect($args['userIds'])->map(fn ($account) => SS58Address::getPublicKey($account))->toArray()
            ),
            'method' => 'BatchAddAccount',
        ]);
    }

    /**
     * Removes a fuel tank account in batch.
     */
    public function batchRemoveAccount(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->batchRemoveAccount(
                $account,
                collect($args['userIds'])->map(fn ($account) => SS58Address::getPublicKey($account))->toArray()
            ),
            'method' => 'BatchRemoveAccount',
        ]);
    }

    /**
     * Removes a fuel tank account.
     */
    public function removeAccount(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->removeAccount($account, SS58Address::getPublicKey($args['userId'])),
            'method' => 'RemoveAccount',
        ]);
    }

    /**
     * Set consumption.
     */
    public function setConsumption(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->setConsumption(
                $account,
                Arr::get($args, 'ruleSetId'),
                Arr::get($args, 'totalConsumed'),
                Arr::get($args, 'userId') ? SS58Address::getPublicKey($args['userId']) : null,
                Arr::get($args, 'lastResetBlock')
            ),
            'method' => 'SetConsumption',
        ]);
    }

    /**
     * Dispatch a call and touch.
     */
    public function dispatchAndTouch(array $args): Model
    {
        return $this->dispatch($args, true);
    }

    /**
     * Dispatch a call.
     */
    public function dispatch(array $args, bool $touch = false): Model
    {
        $result = GraphQL::queryAndReturnResult(
            Arr::get($args, 'dispatch.query'),
            (array) Arr::get($args, 'dispatch.variables'),
            ['schema' => DispatchCall::getEnumCase(Arr::get($args, 'dispatch.call'))?->value]
        )->toArray();

        if (Arr::get($result, 'errors.0.message')) {
            throw new FuelTanksException(__('enjin-platform-fuel-tanks::exception.dispatch_query_error'));
        }

        $encodedData = null;
        if ($data = Arr::get($result, 'data')) {
            $data = array_shift($data);
            $encodedData = Arr::get($data, 'encodedData');
            Transaction::destroy(Arr::get($data, 'id'));
        }

        $account = SS58Address::getPublicKey($args['tankId']);
        $method = $touch ? 'DispatchAndTouch' : 'Dispatch';

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->{$method}(
                $account,
                Arr::get($args, 'ruleSetId'),
                $encodedData,
                Arr::get($args, 'paysRemainingFee', false)
            ),
            'method' => $method,
        ]);
    }

    /**
     * Adds a fuel tank account.
     */
    public function addAccount(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->addAccount($account, SS58Address::getPublicKey($args['userId'])),
            'method' => 'AddAccount',
        ]);
    }

    /**
     * Updates a fuel tank.
     */
    public function updateFuelTank(array $args): Model
    {
        $account = SS58Address::getPublicKey($args['tankId']);

        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->mutateFuelTank(
                $account,
                $this->setUserAccountManagementParams(Arr::get($args, 'mutation')),
                Arr::get($args, 'mutation.providesDeposit'),
                $this->setAccountRulesParams(Arr::get($args, 'mutation.accountRules'))
            ),
            'method' => 'MutateFuelTank',
        ]);
    }

    /**
     * Creates a fuel tank.
     */
    public function createFuelTank(array $args): Model
    {
        return $this->store([
            'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
            'encoded_data' => $this->codec->encode()->createFuelTank(
                $args['name'],
                $args['providesDeposit'],
                $this->setAccountRulesParams($args),
                $this->setDispatchRulesParamsArray($args),
                $this->setUserAccountManagementParams($args),
            ),
            'method' => 'CreateFuelTank',
        ]);
    }

    /**
     * Set user account management param object.
     */
    protected function setUserAccountManagementParams(array $args): ?UserAccountManagementParams
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
    protected function setDispatchRulesParams(array $args): DispatchRulesParams
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
    protected function setDispatchRulesParamsArray(array $args): array
    {
        $dispatchRulesParams = [];
        if ($rules = Arr::get($args, 'dispatchRules')) {
            foreach ($rules as $rule) {
                $dispatchRulesParams[] = $this->setDispatchRulesParams($rule);
            }
        }

        return $dispatchRulesParams;
    }

    /**
     * Set account rule params object.
     */
    protected function setAccountRulesParams(array $args): ?AccountRulesParams
    {
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
