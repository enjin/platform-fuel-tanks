<?php

namespace Enjin\Platform\FuelTanks\Tests\Unit;

use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\MaxFuelBurnPerTransactionParams;
use Enjin\Platform\FuelTanks\Models\Substrate\PermittedCallsParams;
use Enjin\Platform\FuelTanks\Models\Substrate\TankFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\UserFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCallersParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCollectionsParams;
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
                'reservesExistentialDeposit' => null,
                'reservesAccountCreationDeposit' => null,
                'isFrozen' => false,
                'providesDeposit' => false,
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
                'reservesExistentialDeposit' => null,
                'reservesAccountCreationDeposit' => null,
                'isFrozen' => false,
                'providesDeposit' => false,
                'accountRules' => null,
            ],
            $content
        );
    }

    public function test_it_can_decode_fuel_tanks_storage_data_with_user_account_management()
    {
        $content = $this->codec->decoder()->tankStorageData('0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74206c666d786a696f69000000010100000000');

        $this->assertEquals(
            [
                'owner' => '0x3274a0b6662b3cab47da58afd6549b17f0cbf5b7a977bb7fed481ce76ea8af74',
                'name' => 'lfmxjioi',
                'ruleSets' => [],
                'totalReserved' => '0',
                'accountCount' => '0',
                'reservesExistentialDeposit' => null,  // TODO: This should be removed when transition is over
                'reservesAccountCreationDeposit' => false,
                'isFrozen' => false,
                'providesDeposit' => false,
                'accountRules' => null,
            ],
            $content
        );
    }

    public function test_it_can_decode_fuel_tanks_storage_data_with_user_account_management_and_rule_set_with_no_rules()
    {
        $content = $this->codec->decoder()->tankStorageData('0x56fba7af9da63a74853ced5555fec97ce993bd02060ed5954938f72636bb0800206c65336335766d7904000000000000130000c84e676dc11b04010101000000');

        $this->assertEquals(
            [
                'owner' => '0x56fba7af9da63a74853ced5555fec97ce993bd02060ed5954938f72636bb0800',
                'name' => 'le3c5vmy',
                'ruleSets' => [
                    new DispatchRulesParams(
                        ruleSetId: 0,
                        isFrozen: false,
                    ),
                ],
                'totalReserved' => '2000000000000000000',
                'accountCount' => '1',
                'reservesExistentialDeposit' => null, // TODO: This should be removed when transition is over
                'reservesAccountCreationDeposit' => true,
                'isFrozen' => false,
                'providesDeposit' => false,
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
                'reservesExistentialDeposit' => null,
                'reservesAccountCreationDeposit' => null,
                'isFrozen' => false,
                'providesDeposit' => false,
                'accountRules' => null,
            ],
            $content
        );
    }

    public function test_it_can_decode_fuel_tanks_storage_data_with_multiple_rules()
    {
        $content = $this->codec->decoder()->tankStorageData('0x32249aa5459605d8d940b8535dfbcb0b45016f560f784b9420ed346557b85242206c656664686c6d62144811000004030313000064a7b3b6e00d7b00000000004b2d000004040413000064a7b3b6e00d7b0000000000001ab400000401010400000000000000000000000000000000002cfb0000040202000064a7b3b6e00d000000000000000000d345010004000004d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00000000000000');

        $this->assertEquals(
            [
                'owner' => '0x32249aa5459605d8d940b8535dfbcb0b45016f560f784b9420ed346557b85242',
                'name' => 'lefdhlmb',
                'ruleSets' => [
                    new DispatchRulesParams(
                        ruleSetId: 4424,
                        userFuelBudget: new UserFuelBudgetParams(
                            amount: '1000000000000000000',
                            resetPeriod: 123,
                            userCount: '0',
                        ),
                        isFrozen: false,
                    ),
                    new DispatchRulesParams(
                        ruleSetId: 11595,
                        tankFuelBudget: new TankFuelBudgetParams(
                            amount: '1000000000000000000',
                            resetPeriod: 123,
                            totalConsumed: '0',
                            lastResetBlock: null,
                        ),
                        isFrozen: false,
                    ),
                    new DispatchRulesParams(
                        ruleSetId: 46106,
                        whitelistedCollections: new WhitelistedCollectionsParams(
                            collections: [0],
                        ),
                        isFrozen: false,
                    ),
                    new DispatchRulesParams(
                        ruleSetId: 64300,
                        maxFuelBurnPerTransaction: new MaxFuelBurnPerTransactionParams(
                            max: '1000000000000000000',
                        ),
                        isFrozen: false,
                    ),
                    new DispatchRulesParams(
                        ruleSetId: 83411,
                        whitelistedCallers: new WhitelistedCallersParams(
                            callers: ['d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d'],
                        ),
                        isFrozen: false,
                    ),
                ],
                'totalReserved' => '0',
                'accountCount' => '0',
                'reservesExistentialDeposit' => null,
                'reservesAccountCreationDeposit' => null,
                'isFrozen' => false,
                'providesDeposit' => false,
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
                'reservesExistentialDeposit' => null,
                'reservesAccountCreationDeposit' => null,
                'isFrozen' => false,
                'providesDeposit' => false,
                'accountRules' => new AccountRulesParams(
                    whitelistedCallers: new WhitelistedCallersParams(callers: ['d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d']),
                ),
            ],
            $content
        );
    }
}
