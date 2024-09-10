<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec;

use Codec\ScaleBytes;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\Services\Processor\Substrate\Codec\Decoder as BaseDecoder;
use Illuminate\Support\Arr;

class Decoder extends BaseDecoder
{
    /**
     * Decodes the given data by key.
     */
    public function tankStorageKey(string $data): array
    {
        $decoded = $this->codec->process('TankStorageKey', new ScaleBytes($data));

        return [
            'tankAccount' => ($tankAccount = Arr::get($decoded, 'tankAccount')) !== null ? HexConverter::prefix($tankAccount) : null,
        ];
    }

    /**
     * Decodes the given data.
     */
    public function tankStorageData(string $data): array
    {
        try {
            $decoded = $this->codec->process('TankStorageDataV1010', new ScaleBytes($data));
        } catch (\Exception) {
            $decoded = $this->codec->process('TankStorageData', new ScaleBytes($data));
        }

        return [
            'owner' => ($owner = Arr::get($decoded, 'owner')) !== null ? HexConverter::prefix($owner) : null,
            'name' => ($name = Arr::get($decoded, 'name')) !== null ? HexConverter::hexToString($name) : null,
            'ruleSets' => $this->parseRuleSets(Arr::get($decoded, 'ruleSets', [])),
            'totalReserved' => gmp_strval(Arr::get($decoded, 'totalReserved')),
            'accountCount' => gmp_strval(Arr::get($decoded, 'accountCount')),
            'reservesExistentialDeposit' => null, // TODO: Remove this field
            'reservesAccountCreationDeposit' => Arr::get($decoded, 'userAccountManagement.tankReservesAccountCreationDeposit'),
            'isFrozen' => Arr::get($decoded, 'isFrozen'),
            'providesDeposit' => is_bool($r = $this->getValue($decoded, ['providesDeposit', 'coveragePolicy'])) ? $r : $r === 'FeesAndDeposit',
            'accountRules' => $this->parseAccountRules(Arr::get($decoded, 'accountRules')),
            // TODO: New fields in v1010
            //      coveragePolicy => "Fees", "FeesAndDeposit"
        ];
    }

    /**
     * Decodes fuel tank account by key.
     */
    public function fuelTankAccountStorageKey(string $data): array
    {
        $decoded = $this->codec->process('FuelTankAccountStorageKey', new ScaleBytes($data));

        return [
            'tankAccount' => ($tank = Arr::get($decoded, 'tankAccount')) !== null ? HexConverter::prefix($tank) : null,
            'userAccount' => ($user = Arr::get($decoded, 'account')) !== null ? HexConverter::prefix($user) : null,
        ];
    }

    /**
     * Decodes fuel tank account by data.
     */
    public function fuelTankAccountStorageData(string $data): array
    {
        try {
            $decoded = $this->codec->process('FuelTankAccountStorageDataV1010', new ScaleBytes($data));
        } catch (\Exception) {
            $decoded = $this->codec->process('FuelTankAccountStorageData', new ScaleBytes($data));
        }

        return [
            'tankDeposit' => gmp_strval(Arr::get($decoded, 'tankDeposit')),
            'userDeposit' => gmp_strval(Arr::get($decoded, 'userDeposit')),
            'totalReceived' => gmp_strval(Arr::get($decoded, 'totalReceived')),
            'ruleDataSets' => '', //TODO: Implement
        ];
    }

    /**
     * Parses the rule sets.
     */
    protected function parseRuleSets(array $ruleSets): array
    {
        if (empty($ruleSets)) {
            return [];
        }

        return array_map(
            fn ($setId, $ruleSet) => (new DispatchRulesParams())->fromEncodable($setId, $ruleSet),
            array_keys($ruleSets),
            $ruleSets,
        );
    }

    /**
     * Parses the account rules.
     */
    protected function parseAccountRules(array $accountRules): ?AccountRulesParams
    {
        if (empty($accountRules)) {
            return null;
        }

        return (new AccountRulesParams())->fromEncodable($accountRules);
    }
}
