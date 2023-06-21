<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec;

use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\FuelTankRules;
use Enjin\Platform\FuelTanks\Models\Substrate\UserAccountManagementParams;
use Enjin\Platform\Services\Processor\Substrate\Codec\Encoder as BaseEncoder;

class Encoder extends BaseEncoder
{
    public function addAccount(string $tankId, string $userId): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('AddAccount')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.add_account'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'userId' => [
                'Id' => HexConverter::unPrefix($userId),
            ],
        ]);

        return HexConverter::prefix($encoded);
    }

    public function removeAccount(string $tankId, string $userId): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('RemoveAccount')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.remove_account'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'userId' => [
                'Id' => HexConverter::unPrefix($userId),
            ],
        ]);

        return HexConverter::prefix($encoded);
    }

    public function batchAddAccount(string $tankId, array $userIds): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('BatchAddAccount')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.batch_add_account'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'userIds' => array_map(function ($userId) {
                return [
                    'Id' => HexConverter::unPrefix($userId),
                ];
            }, $userIds),
        ]);

        return HexConverter::prefix($encoded);
    }

    public function batchRemoveAccount(string $tankId, array $userIds): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('BatchRemoveAccount')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.batch_remove_account'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'userIds' => array_map(function ($userId) {
                return [
                    'Id' => HexConverter::unPrefix($userId),
                ];
            }, $userIds),
        ]);

        return HexConverter::prefix($encoded);
    }

    public function createFuelTank(string $name, bool $providesDeposit, ?AccountRulesParams $accountRules = null, ?array $dispatchRules = [], ?UserAccountManagementParams $userAccount = null): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('CreateFuelTank')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.create_fuel_tank'),
            'descriptor' => [
                'name' => HexConverter::stringToHexPrefixed($name),
                'userAccountManagement' => $userAccount?->toEncodable(),
                'ruleSets' => array_map(fn ($rules) => $rules->toEncodable(), $dispatchRules),
                'providesDeposit' => $providesDeposit,
                'accountRules' => $accountRules?->toEncodable() ?? [],
            ],
        ]);

        return HexConverter::prefix($encoded);
    }

    public function destroyFuelTank(string $tankId): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('DestroyFuelTank')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.destroy_fuel_tank'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
        ]);

        return HexConverter::prefix($encoded);
    }

    public function setConsumption(string $tankId, int $ruleSetId, string $totalConsumed, ?string $userId = null, ?int $lastResetBlock = null): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('ForceSetConsumption')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.force_set_consumption'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'userId' => $userId ? [
                'Id' => HexConverter::unPrefix($userId),
            ] : null,
            'ruleSetId' => $ruleSetId,
            'consumption' => [
                'totalConsumed' => $totalConsumed,
                'lastResetBlock' => $lastResetBlock,
            ],
        ]);

        return HexConverter::prefix($encoded);
    }

    public function insertRuleSet(string $tankId, int $ruleSetId, DispatchRulesParams $dispatchRules): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('InsertRuleSet')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.insert_rule_set'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'ruleSetId' => $ruleSetId,
            'rules' => $dispatchRules->toEncodable(),
        ]);

        return HexConverter::prefix($encoded);
    }

    public function removeRuleSet(string $tankId, int $ruleSetId): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('RemoveRuleSet')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.remove_rule_set'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'ruleSetId' => $ruleSetId,
        ]);

        return HexConverter::prefix($encoded);
    }

    public function removeAccountRuleData(string $tankId, string $userId, int $ruleSetId, FuelTankRules $dispatchRule): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('RemoveAccountRuleData')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.remove_account_rule_data'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'userId' => [
                'Id' => HexConverter::unPrefix($userId),
            ],
            'ruleSetId' => $ruleSetId,
            'ruleKind' => str_replace('Params', '', class_basename($dispatchRule)),
        ]);

        return HexConverter::prefix($encoded);
    }

    public function mutateFuelTank(string $tankId, null|array|UserAccountManagementParams $userAccount = null, ?bool $providesDeposit = null, ?AccountRulesParams $accountRules = null): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('MutateFuelTank')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.mutate_fuel_tank'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'mutation' => [
                'userAccountManagement' => is_array($userAccount) ? ['NoMutation' => null] : ['SomeMutation' => $userAccount?->toEncodable()],
                'providesDeposit' => $providesDeposit,
                'accountRules' => $accountRules?->toEncodable(),
            ],
        ]);

        return HexConverter::prefix($encoded);
    }

    public function scheduleMutateFreezeState(string $tankId, bool $isFrozen, ?int $ruleSetId = null): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('ScheduleMutateFreezeState')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.schedule_mutate_freeze_state'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'ruleSetId' => $ruleSetId,
            'isFrozen' => $isFrozen,
        ]);

        return HexConverter::prefix($encoded);
    }

    public function dispatch(string $tankId, int $ruleSetId, string $encodedCall, bool $paysRemainingFee): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('Dispatch')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.dispatch'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'ruleSetId' => $ruleSetId,
        ]);

        return HexConverter::prefix($encoded) . HexConverter::unPrefix($encodedCall) . ($paysRemainingFee ? '01' : '00');
    }

    public function dispatchAndTouch(string $tankId, int $ruleSetId, string $encodedCall, bool $paysRemainingFee): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('DispatchAndTouch')->encode([
            'callIndex' => $this->getCallIndex('FuelTanks.dispatch_and_touch'),
            'tankId' => [
                'Id' => HexConverter::unPrefix($tankId),
            ],
            'ruleSetId' => $ruleSetId,
        ]);

        return HexConverter::prefix($encoded) . HexConverter::unPrefix($encodedCall) . ($paysRemainingFee ? '01' : '00');
    }
}
