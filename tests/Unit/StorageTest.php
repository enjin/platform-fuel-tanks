<?php

namespace Enjin\Platform\FuelTanks\Tests\Unit;

use Enjin\Platform\FuelTanks\Enums\CoveragePolicy;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\MaxFuelBurnPerTransactionParams;
use Enjin\Platform\FuelTanks\Models\Substrate\PermittedCallsParams;
use Enjin\Platform\FuelTanks\Models\Substrate\PermittedExtrinsicsParams;
use Enjin\Platform\FuelTanks\Models\Substrate\UserFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCallersParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\FuelTanks\Tests\TestCase;

final class StorageTest extends TestCase
{
    protected Codec $codec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codec = new Codec();
    }

    public function test_it_can_decode_fuel_tanks_storage_key()
    {
        $content = $this->codec->decoder()->tankStorageKey('0xb8ed204d2f9b209f43a0487b80cceca11dff2785cc2c6efead5657dc32a2065e018cfc79ba6e75a34e112c9d8c1d60d358e96e983a15f5a458c719ef98f9c40f7bab18e8ef1fd7a467291df02ee8d915');
        $this->assertEquals([
            'tankAccount' => '0x58e96e983a15f5a458c719ef98f9c40f7bab18e8ef1fd7a467291df02ee8d915',
        ], $content);
    }

    public function test_it_can_decode_fuel_tanks_storage_data_without_rule_sets()
    {
        $content = $this->codec->decoder()->tankStorageData('0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74206c64746b327268730013000058ec354844530c00000000');

        $this->assertEquals(
            [
                'owner' => '0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74',
                'name' => 'ldtk2rhs',
                'ruleSets' => [],
                'totalReserved' => '6000000000000000000',
                'accountCount' => '3',
                'reservesAccountCreationDeposit' => null,
                'coveragePolicy' => CoveragePolicy::FEES,
                'isFrozen' => false,
                'accountRules' => null,
            ],
            $content
        );
    }

    public function test_it_can_decode_fuel_tanks_storage_data_with_rule_sets()
    {
        $content = $this->codec->decoder()->tankStorageData('0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74206c647a61346f647904000000000402020080c6a47e8d0300000000000000000000000000000000');

        $this->assertEquals(
            [
                'owner' => '0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74',
                'name' => 'ldza4ody',
                'ruleSets' => [
                    new DispatchRulesParams(
                        ruleSetId: 0,
                        maxFuelBurnPerTransaction: new MaxFuelBurnPerTransactionParams(max: '1000000000000000'),
                        isFrozen: false,
                    ),
                ],
                'totalReserved' => '0',
                'accountCount' => '0',
                'reservesAccountCreationDeposit' => null,
                'isFrozen' => false,
                'coveragePolicy' => CoveragePolicy::FEES,
                'accountRules' => null,
            ],
            $content
        );
    }

    public function test_it_can_decode_fuel_tanks_storage_data_with_permitted_calls()
    {
        $content = $this->codec->decoder()->tankStorageData('0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74206c666276346f3330040000000004060604082d0000000000000000');

        $this->assertEquals(
            [
                'owner' => '0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74',
                'name' => 'lfbv4o30',
                'ruleSets' => [
                    new DispatchRulesParams(
                        ruleSetId: 0,
                        permittedCalls: new PermittedCallsParams(calls: ['2d00']),
                        isFrozen: false,
                    ),
                ],
                'totalReserved' => '0',
                'accountCount' => '0',
                'reservesAccountCreationDeposit' => null,
                'isFrozen' => false,
                'coveragePolicy' => CoveragePolicy::FEES,
                'accountRules' => null,
            ],
            $content
        );
    }

    public function test_it_can_decode_fuel_tanks_storage_data_with_multiple_rules()
    {
        $content = $this->codec->decoder()->tankStorageData('0x583a5efb357bf4a63bd04e1da3915250fcd3c2415f03b1de9917292f77927f4e344d616769634675656c54616e6b080000000004030317000010632d5ec76b056400000014000101000000040707042042616c616e636573207472616e73666572000100000101010100');

        $this->assertEquals(
            [
                'owner' => '0x583a5efb357bf4a63bd04e1da3915250fcd3c2415f03b1de9917292f77927f4e',
                'name' => 'MagicFuelTank',
                'ruleSets' =>  [
                    new DispatchRulesParams(
                        ruleSetId: 0,
                        userFuelBudget: new UserFuelBudgetParams(
                            amount: '7766279631452241920',
                            resetPeriod: '100',
                            userCount:'5',
                        ),
                        isFrozen: false,
                    ),
                    new DispatchRulesParams(
                        ruleSetId:  1,
                        permittedExtrinsics: new PermittedExtrinsicsParams(
                            extrinsics:  ['Balances.transfer'],
                        ),
                        isFrozen: false,
                    ),
                ],
                'totalReserved' => '0',
                'accountCount' => '0',
                'reservesAccountCreationDeposit' => true,
                'coveragePolicy' => CoveragePolicy::FEES_AND_DEPOSIT,
                'isFrozen' => true,
                'accountRules' => null,
            ],
            $content
        );
    }

    public function test_it_can_decode_fuel_tanks_storage_data_with_account_rules()
    {
        $content = $this->codec->decoder()->tankStorageData('0x92f864d58d02a51762b8a87e364b2c30cac8a0addfaae4031fece2e08676f052206c656933676f716b00000000000004000004d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d');

        $this->assertEquals(
            [
                'owner' => '0x92f864d58d02a51762b8a87e364b2c30cac8a0addfaae4031fece2e08676f052',
                'name' => 'lei3goqk',
                'ruleSets' => [],
                'totalReserved' => '0',
                'accountCount' => '0',
                'reservesAccountCreationDeposit' => null,
                'coveragePolicy' => CoveragePolicy::FEES,
                'isFrozen' => false,
                'accountRules' => new AccountRulesParams(
                    whitelistedCallers: new WhitelistedCallersParams(callers: ['d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d']),
                ),
            ],
            $content
        );
    }
}
