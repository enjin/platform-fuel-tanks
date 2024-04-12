<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate;

use Carbon\Carbon;
use Closure;
use Enjin\Platform\FuelTanks\Enums\AccountRule as AccountRuleEnum;
use Enjin\Platform\FuelTanks\Enums\DispatchRule as DispatchRuleEnum;
use Enjin\Platform\FuelTanks\Models\AccountRule;
use Enjin\Platform\FuelTanks\Models\DispatchRule;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\FuelTanks\Models\FuelTankAccount;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\FuelTanks\Services\TankService;
use Enjin\Platform\Services\Processor\Substrate\Parser as BaseParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Parser extends BaseParser
{
    protected static $tankCache = [];
    protected TankService $tankService;
    protected Codec $codec;

    /**
     * Creates a new instance.
     */
    public function __construct(TankService $tankService)
    {
        parent::__construct();

        $this->tankService = $tankService;
        $this->codec = new Codec();
    }

    /**
     * Parses and store data to tanks.
     */
    public function tanksStorages(array $data): void
    {
        $insertData = [];
        $insertRules = [];
        foreach ($data as [$key, $tank]) {
            $tankKey = $this->codec->decoder()->tankStorageKey($key);
            $tankData = $this->codec->decoder()->tankStorageData($tank);

            $ownerWallet = $this->getCachedWallet(
                $owner = $tankData['owner'],
                fn () => $this->walletService->firstOrStore(['account' => $owner])
            );

            $ruleSets = Arr::get($tankData, 'ruleSets');
            $accountRules = Arr::get($tankData, 'accountRules');

            if (!empty($ruleSets) || !empty($accountRules)) {
                $insertRules[] = [
                    'tank' => $tankKey['tankAccount'],
                    'ruleSets' => $ruleSets,
                    'accountRules' => $accountRules,
                ];
            }

            $insertData[] = [
                'public_key' => $tankKey['tankAccount'],
                'owner_wallet_id' => $ownerWallet->id,
                'name' => $tankData['name'],
                'reserves_existential_deposit' => $tankData['reservesExistentialDeposit'],
                'reserves_account_creation_deposit' => $tankData['reservesAccountCreationDeposit'],
                'provides_deposit' => $tankData['providesDeposit'],
                'is_frozen' => $tankData['isFrozen'],
                'account_count' => 0,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ];
        }

        $this->tankService->insert($insertData);
        $this->fuelTankRules($insertRules);
    }

    /**
     * Parses and store data to tank accounts.
     */
    public function accountsStorages(array $data): void
    {
        $insertData = [];
        foreach ($data as [$key, $fuelTankAccount]) {
            $accountKey = $this->codec->decoder()->fuelTankAccountStorageKey($key);
            $accountData = $this->codec->decoder()->fuelTankAccountStorageData($fuelTankAccount);

            $userWallet = $this->getCachedWallet(
                $user = $accountKey['userAccount'],
                fn () => $this->walletService->firstOrStore(['account' => $user])
            );
            $tankAccount = $this->getCachedTank(
                $tank = $accountKey['tankAccount'],
                fn () => $this->tankService->get($tank),
            );

            $insertData[] = [
                'fuel_tank_id' => $tankAccount->id,
                'wallet_id' => $userWallet->id,
                'tank_deposit' => $accountData['tankDeposit'],
                'user_deposit' => $accountData['userDeposit'],
                'total_received' => $accountData['totalReceived'],
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ];
        }

        FuelTankAccount::insert($insertData);
    }

    /**
     * Parses and store data to tank rules.
     */
    protected function fuelTankRules(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $insertDispatchRules = [];
        $insertAccountRules = [];
        foreach ($data as $tankRules) {
            $tank = FuelTank::firstWhere('public_key', $tankRules['tank']);

            foreach ($tankRules['ruleSets'] as $ruleSet) {
                foreach (DispatchRuleEnum::caseValuesAsArray() as $rule) {
                    $ruleParam = Str::camel($rule);

                    if (empty($ruleParam = $ruleSet->{$ruleParam})) {
                        continue;
                    }

                    $insertDispatchRules[] = [
                        'fuel_tank_id' => $tank->id,
                        'rule_set_id' => $ruleSet->ruleSetId,
                        'rule' => $rule,
                        'value' => json_encode(Arr::get($ruleParam->toArray(), $rule)),
                        'is_frozen' => $ruleSet->isFrozen,
                        'created_at' => $now = Carbon::now(),
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($accountRules = $tankRules['accountRules'])) {
                foreach (AccountRuleEnum::caseValuesAsArray() as $rule) {
                    $ruleParam = Str::camel($rule);

                    if (empty($ruleParam = $accountRules->{$ruleParam})) {
                        continue;
                    }

                    $insertAccountRules[] = [
                        'fuel_tank_id' => $tank->id,
                        'rule' => $rule,
                        'value' => json_encode(Arr::get($ruleParam->toArray(), $rule)),
                        'created_at' => $now = Carbon::now(),
                        'updated_at' => $now,
                    ];
                }
            }
        }

        DispatchRule::insert($insertDispatchRules);
        AccountRule::insert($insertAccountRules);
    }

    /**
     * Returns a cached wallet or the default value.
     */
    protected function getCachedTank(string $key, ?Closure $default = null): mixed
    {
        if (!isset(static::$tankCache[$key])) {
            static::$tankCache[$key] = $default();
        }

        return static::$tankCache[$key];
    }
}
