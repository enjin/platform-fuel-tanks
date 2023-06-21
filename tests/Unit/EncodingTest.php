<?php

namespace Enjin\Platform\FuelTanks\Tests\Unit;

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
use Enjin\Platform\FuelTanks\Tests\TestCase;

class EncodingTest extends TestCase
{
    protected Codec $codec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codec = new Codec();
    }

    public function test_it_can_encode_add_account()
    {
        $data = $this->codec->encode()->addAccount(
            '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d'
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.add_account'];
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d",
            $data
        );
    }

    public function test_it_can_encode_remove_account()
    {
        $data = $this->codec->encode()->removeAccount(
            '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d'
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.remove_account'];
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d",
            $data
        );
    }

    public function test_it_can_encode_destroy_fuel_tank()
    {
        $data = $this->codec->encode()->destroyFuelTank(
            '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f'
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.destroy_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f",
            $data
        );
    }

    public function test_it_can_encode_batch_add_account()
    {
        $data = $this->codec->encode()->batchAddAccount(
            '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            [
                '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
            ],
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.batch_add_account'];
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f0800d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48",
            $data
        );
    }

    public function test_it_can_encode_batch_remove_account()
    {
        $data = $this->codec->encode()->batchRemoveAccount(
            '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            [
                '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
            ],
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.batch_remove_account'];
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f0800d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_min_params()
    {
        $accountRules = new AccountRulesParams();

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Fuel Tank',
            false,
            $accountRules,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}3c456e6a696e204675656c2054616e6b00000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_provide_deposit_true()
    {
        $accountRules = new AccountRulesParams();

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Fuel Tank',
            true,
            $accountRules,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}3c456e6a696e204675656c2054616e6b00000100",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_user_account_management()
    {
        $accountRules = new AccountRulesParams();
        $userAccount = new UserAccountManagementParams(
            tankReservesExistentialDeposit: true,
            tankReservesAccountCreationDeposit: true,
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Fuel Tank',
            false,
            $accountRules,
            [],
            $userAccount,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}3c456e6a696e204675656c2054616e6b010101000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_require_token()
    {
        $accountRules = new AccountRulesParams(
            requireToken: new RequireTokenParams(
                collectionId: 2000,
                tokenId: 255,
            )
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Fuel Tank',
            false,
            $accountRules,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}3c456e6a696e204675656c2054616e6b0000000401d0070000000000000000000000000000ff000000000000000000000000000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_whitelisted_callers()
    {
        $accountRules = new AccountRulesParams(
            whitelistedCallers: new WhitelistedCallersParams(
                callers: [
                    '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
                    '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                ]
            )
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Fuel Tank',
            false,
            $accountRules,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}3c456e6a696e204675656c2054616e6b0000000400088eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_both_account_rule_set()
    {
        $accountRules = new AccountRulesParams(
            whitelistedCallers: new WhitelistedCallersParams(
                callers: [
                    '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
                    '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                ]
            ),
            requireToken: new RequireTokenParams(
                collectionId: 2000,
                tokenId: 255,
            ),
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Fuel Tank',
            false,
            $accountRules,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}3c456e6a696e204675656c2054616e6b0000000800088eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d01d0070000000000000000000000000000ff000000000000000000000000000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_whitelisted_callers_dispatch_rule()
    {
        $dispatchRules = new DispatchRulesParams(
            whitelistedCallers: new WhitelistedCallersParams(
                callers: [
                    '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                ]
            )
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Tank',
            false,
            null,
            [$dispatchRules],
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}28456e6a696e2054616e6b000400000000040004d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d0000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_whitelisted_collections_dispatch_rule()
    {
        $dispatchRules = new DispatchRulesParams(
            whitelistedCollections: new WhitelistedCollectionsParams(
                collections: ['2000'],
            ),
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Tank',
            false,
            null,
            [$dispatchRules],
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}28456e6a696e2054616e6b000400000000040104d00700000000000000000000000000000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_max_fuel_burn_per_transaction()
    {
        $dispatchRules = new DispatchRulesParams(
            maxFuelBurnPerTransaction: new MaxFuelBurnPerTransactionParams(
                max: 25775
            ),
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Tank',
            false,
            null,
            [$dispatchRules],
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}28456e6a696e2054616e6b0004000000000402af6400000000000000000000000000000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_user_fuel_budget()
    {
        $dispatchRules = new DispatchRulesParams(
            userFuelBudget: new UserFuelBudgetParams(
                amount: '250000000',
                resetPeriod: '300000'
            ),
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Tank',
            false,
            null,
            [$dispatchRules],
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}28456e6a696e2054616e6b000400000000040302ca9a3be09304000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_tank_fuel_budget()
    {
        $dispatchRules = new DispatchRulesParams(
            tankFuelBudget: new TankFuelBudgetParams(
                amount: '350000000',
                resetPeriod: '3775'
            ),
        );

        $data = $this->codec->encode()->createFuelTank(
            'Enjin Tank',
            false,
            null,
            [$dispatchRules],
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.create_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}28456e6a696e2054616e6b0004000000000404024e7253bf0e00000000",
            $data
        );
    }

    public function test_it_can_encode_schedule_mutate_freeze_state_without_rule_set_id()
    {
        $data = $this->codec->encode()->scheduleMutateFreezeState(
            '0x18353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c',
            true,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.schedule_mutate_freeze_state'];
        $this->assertEquals(
            "0x{$callIndex}0018353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c0001",
            $data
        );
    }

    public function test_it_can_encode_schedule_mutate_freeze_state_with_rule_set_id()
    {
        $data = $this->codec->encode()->scheduleMutateFreezeState(
            '0x18353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c',
            true,
            '255',
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.schedule_mutate_freeze_state'];
        $this->assertEquals(
            "0x{$callIndex}0018353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c01ff00000001",
            $data
        );
    }

    public function test_it_can_encode_insert_or_update_rule_set()
    {
        $dispatchRules = new DispatchRulesParams(
            whitelistedCallers: new WhitelistedCallersParams(
                callers: ['0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d'],
            ),
        );

        $data = $this->codec->encode()->insertRuleSet(
            '0x18353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c',
            '10',
            $dispatchRules,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.insert_rule_set'];
        $this->assertEquals(
            "0x{$callIndex}0018353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c0a000000040004d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d",
            $data
        );
    }

    public function test_it_can_encode_remove_rule_set()
    {
        $data = $this->codec->encode()->removeRuleSet(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            '10'
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.remove_rule_set'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d0a000000",
            $data
        );
    }

    public function test_it_can_encode_remove_account_rule_data()
    {
        $data = $this->codec->encode()->removeAccountRuleData(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
            '20',
            new WhitelistedCallersParams(),
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.remove_account_rule_data'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a481400000000",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_some_mutation_null()
    {
        $data = $this->codec->encode()->mutateFuelTank(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.mutate_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d01000000",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_some_mutation_filled()
    {
        $data = $this->codec->encode()->mutateFuelTank(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            new UserAccountManagementParams(
                tankReservesExistentialDeposit: true,
                tankReservesAccountCreationDeposit: true,
            )
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.mutate_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d010101010000",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_provides_deposit()
    {
        $data = $this->codec->encode()->mutateFuelTank(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            [],
            true,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.mutate_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00010100",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_account_rules()
    {
        $data = $this->codec->encode()->mutateFuelTank(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            [],
            null,
            new AccountRulesParams(
                requireToken: new RequireTokenParams(
                    collectionId: '2000',
                    tokenId: '255',
                )
            )
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.mutate_fuel_tank'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d0000010401d0070000000000000000000000000000ff000000000000000000000000000000",
            $data
        );
    }

    public function test_it_can_encode_dispatch()
    {
        $createCollection = '0x2800000000000000';
        $data = $this->codec->encode()->dispatch(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            255,
            $createCollection,
            false,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.dispatch'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27dff000000280000000000000000",
            $data
        );
    }

    public function test_it_can_encode_dispatch_and_touch()
    {
        $createCollection = '0x2800000000000000';
        $data = $this->codec->encode()->dispatchAndTouch(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            255,
            $createCollection,
            false,
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.dispatch_and_touch'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27dff000000280000000000000000",
            $data
        );
    }

    public function test_it_can_encode_set_consumption_with_no_options()
    {
        $data = $this->codec->encode()->setConsumption(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            255,
            '100000000'
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.force_set_consumption'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00ff0000000284d71700",
            $data
        );
    }

    public function test_it_can_encode_set_consumption_with_user_id()
    {
        $data = $this->codec->encode()->setConsumption(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            255,
            '100000000',
            '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48'
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.force_set_consumption'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d01008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48ff0000000284d71700",
            $data
        );
    }

    public function test_it_can_encode_set_consumption_with_last_reset_block()
    {
        $data = $this->codec->encode()->setConsumption(
            '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            255,
            '100000000',
            null,
            100
        );

        $callIndex = $this->codec->encode()->callIndexes['FuelTanks.force_set_consumption'];
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00ff0000000284d7170164000000",
            $data
        );
    }
}
