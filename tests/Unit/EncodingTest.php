<?php

namespace Enjin\Platform\FuelTanks\Tests\Unit;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\AddAccountMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\BatchAddAccountMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\BatchRemoveAccountMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\CreateFuelTankMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\DestroyFuelTankMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\DispatchAndTouchMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\DispatchMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\ForceSetConsumptionMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\InsertRuleSetMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\MutateFuelTankMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\RemoveAccountMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\RemoveAccountRuleDataMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\RemoveRuleSetMutation;
use Enjin\Platform\FuelTanks\GraphQL\Mutations\ScheduleMutateFreezeStateMutation;
use Enjin\Platform\FuelTanks\Models\Substrate\AccountRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\DispatchRulesParams;
use Enjin\Platform\FuelTanks\Models\Substrate\MaxFuelBurnPerTransactionParams;
use Enjin\Platform\FuelTanks\Models\Substrate\PermittedExtrinsicsParams;
use Enjin\Platform\FuelTanks\Models\Substrate\RequireTokenParams;
use Enjin\Platform\FuelTanks\Models\Substrate\TankFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\UserAccountManagementParams;
use Enjin\Platform\FuelTanks\Models\Substrate\UserFuelBudgetParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCallersParams;
use Enjin\Platform\FuelTanks\Models\Substrate\WhitelistedCollectionsParams;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\FuelTanks\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
        $data = TransactionSerializer::encode('AddAccount', AddAccountMutation::getEncodableParams(
            tankId: '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            userId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d'
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.add_account', true, true);
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d",
            $data
        );
    }

    public function test_it_can_encode_remove_account()
    {
        $data = TransactionSerializer::encode('RemoveAccount', RemoveAccountMutation::getEncodableParams(
            tankId: '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            userId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d'
        ));

        $callIndex = $this->codec->encoder()->getCallindex('FuelTanks.remove_account', true);
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d",
            $data
        );
    }

    public function test_it_can_encode_destroy_fuel_tank()
    {
        $data = TransactionSerializer::encode('DestroyFuelTank', DestroyFuelTankMutation::getEncodableParams(
            tankId: '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f'
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.destroy_fuel_tank', true);
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f",
            $data
        );
    }

    public function test_it_can_encode_batch_add_account()
    {
        $data = TransactionSerializer::encode('BatchAddAccount', BatchAddAccountMutation::getEncodableParams(
            tankId: '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            userIds: [
                '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
            ],
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.batch_add_account', true);
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f0800d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48",
            $data
        );
    }

    public function test_it_can_encode_batch_remove_account()
    {
        $data = TransactionSerializer::encode('BatchRemoveAccount', BatchRemoveAccountMutation::getEncodableParams(
            tankId: '0xbe5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f',
            userIds: [
                '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
            ],
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.batch_remove_account', true);
        $this->assertEquals(
            "0x{$callIndex}00be5ddb1579b72e84524fc29e78609e3caf42e85aa118ebfe0b0ad404b5bdd25f0800d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_min_params()
    {
        $accountRules = new AccountRulesParams();

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Fuel Tank',
            providesDeposit: false,
            accountRules: $accountRules,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
        $this->assertEquals(
            "0x{$callIndex}3c456e6a696e204675656c2054616e6b00000000",
            $data
        );
    }

    public function test_it_can_encode_create_fuel_tank_with_provide_deposit_true()
    {
        $accountRules = new AccountRulesParams();

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Fuel Tank',
            providesDeposit: true,
            accountRules: $accountRules,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Fuel Tank',
            providesDeposit: false,
            accountRules: $accountRules,
            dispatchRules: [],
            userAccountManagement: $userAccount,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Fuel Tank',
            providesDeposit: false,
            accountRules: $accountRules,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Fuel Tank',
            providesDeposit: false,
            accountRules: $accountRules,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Fuel Tank',
            providesDeposit: false,
            accountRules: $accountRules,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Tank',
            providesDeposit: false,
            userAccountManagement: null,
            dispatchRules: [$dispatchRules],
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Tank',
            providesDeposit: false,
            userAccountManagement:  null,
            dispatchRules: [$dispatchRules],
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Tank',
            providesDeposit: false,
            userAccountManagement: null,
            dispatchRules: [$dispatchRules],
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Tank',
            providesDeposit: false,
            userAccountManagement: null,
            dispatchRules: [$dispatchRules],
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
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

        $data = TransactionSerializer::encode('CreateFuelTank', CreateFuelTankMutation::getEncodableParams(
            name: 'Enjin Tank',
            providesDeposit: false,
            userAccountManagement: null,
            dispatchRules: [$dispatchRules],
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.create_fuel_tank', true);
        $this->assertEquals(
            "0x{$callIndex}28456e6a696e2054616e6b0004000000000404024e7253bf0e00000000",
            $data
        );
    }

    public function test_it_can_encode_schedule_mutate_freeze_state_without_rule_set_id()
    {
        $data = TransactionSerializer::encode('MutateFreezeState', ScheduleMutateFreezeStateMutation::getEncodableParams(
            tankId: '0x18353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c',
            isFrozen: true,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.mutate_freeze_state', true);
        $this->assertEquals(
            "0x{$callIndex}0018353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c0001",
            $data
        );
    }

    public function test_it_can_encode_schedule_mutate_freeze_state_with_rule_set_id()
    {
        $data = TransactionSerializer::encode('MutateFreezeState', ScheduleMutateFreezeStateMutation::getEncodableParams(
            tankId: '0x18353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c',
            isFrozen: true,
            ruleSetId: '255',
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.mutate_freeze_state', true);
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

        $data = TransactionSerializer::encode('InsertRuleSet', InsertRulesetMutation::getEncodableParams(
            tankId: '0x18353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c',
            ruleSetId: '10',
            dispatchRules: $dispatchRules,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.insert_rule_set', true);
        $this->assertEquals(
            "0x{$callIndex}0018353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c0a000000040004d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d",
            $data
        );
    }

    public function test_it_can_encode_insert_or_update_rule_with_permitted_extrinsics()
    {
        $dispatchRules = new DispatchRulesParams(
            permittedExtrinsics: new PermittedExtrinsicsParams(
                extrinsics: ['CreateCollection', 'ApproveCollection', 'SimpleTransferToken', 'OperatorTransferToken'],
            ),
        );

        $data = TransactionSerializer::encode('InsertRuleSet', InsertRulesetMutation::getEncodableParams(
            tankId: '0x18353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c',
            ruleSetId: '10',
            dispatchRules: $dispatchRules,
        ));

        $data = Str::take($data, Str::length($data) - 4);
        $data .= Arr::get($dispatchRules->permittedExtrinsics->toEncodable(), 'PermittedExtrinsics.extrinsics');

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.insert_rule_set', true);
        $this->assertEquals(
            "0x{$callIndex}0018353dcf7a6eb053b6f0c01774d1f8cfe0c15963780f6935c49a9fd4f50b893c0a0000000407102800000000000000280f006a03b1a3d40d7e344dfb27157931b14b59fe2ff11d7352353321fe400e956802002806006a03b1a3d40d7e344dfb27157931b14b59fe2ff11d7352353321fe400e95680200000000002806006a03b1a3d40d7e344dfb27157931b14b59fe2ff11d7352353321fe400e9568020001006a03b1a3d40d7e344dfb27157931b14b59fe2ff11d7352353321fe400e9568020000",
            $data
        );
    }

    public function test_it_can_encode_remove_rule_set()
    {
        $data = TransactionSerializer::encode('RemoveRuleSet', RemoveRulesetmutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            ruleSetId: '10'
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.remove_rule_set', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d0a000000",
            $data
        );
    }

    public function test_it_can_encode_remove_account_rule_data()
    {
        $data = TransactionSerializer::encode('RemoveAccountRuleData', RemoveAccountRuleDataMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            userId: '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
            ruleSetId: '20',
            rule: 'WHITELISTED_CALLERS',
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.remove_account_rule_data', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a481400000000",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_some_mutation_null()
    {
        $data = TransactionSerializer::encode('MutateFuelTank', MutateFuelTankMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.mutate_fuel_tank', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d01000000",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_some_mutation_filled()
    {
        $data = TransactionSerializer::encode('MutateFuelTank', MutateFuelTankMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            userAccount: new UserAccountManagementParams(
                tankReservesExistentialDeposit: true,
                tankReservesAccountCreationDeposit: true,
            )
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.mutate_fuel_tank', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d010101010000",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_provides_deposit()
    {
        $data = TransactionSerializer::encode('MutateFuelTank', MutateFuelTankMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            userAccount: [],
            providesDeposit: true,
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.mutate_fuel_tank', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00010100",
            $data
        );
    }

    public function test_it_can_encode_mutate_fuel_tank_with_account_rules()
    {
        $data = TransactionSerializer::encode('MutateFuelTank', MutateFuelTankMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            userAccount: [],
            providesDeposit: null,
            accountRules: new AccountRulesParams(
                requireToken: new RequireTokenParams(
                    collectionId: '2000',
                    tokenId: '255',
                )
            )
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.mutate_fuel_tank', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d0000010401d0070000000000000000000000000000ff000000000000000000000000000000",
            $data
        );
    }

    public function test_it_can_encode_dispatch()
    {
        $createCollection = '2800000000000000';
        $data = TransactionSerializer::encode('Dispatch', DispatchMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            ruleSetId: 255,
        )) . $createCollection . '00';

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.dispatch', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27dff000000280000000000000000",
            $data
        );
    }

    public function test_it_can_encode_dispatch_and_touch()
    {
        $createCollection = '2800000000000000';
        $data = TransactionSerializer::encode('DispatchAndTouch', DispatchAndTouchMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            ruleSetId: 255,
        )) . $createCollection . '00';

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.dispatch_and_touch', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27dff000000280000000000000000",
            $data
        );
    }

    public function test_it_can_encode_set_consumption_with_no_options()
    {
        $data = TransactionSerializer::encode('ForceSetConsumption', ForceSetConsumptionMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            ruleSetId: 255,
            totalConsumed: '100000000'
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.force_set_consumption', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00ff0000000284d71700",
            $data
        );
    }

    public function test_it_can_encode_set_consumption_with_user_id()
    {
        $data = TransactionSerializer::encode('ForceSetConsumption', ForceSetConsumptionMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            ruleSetId: 255,
            totalConsumed: '100000000',
            userId: '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48'
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.force_set_consumption', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d01008eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48ff0000000284d71700",
            $data
        );
    }

    public function test_it_can_encode_set_consumption_with_last_reset_block()
    {
        $data = TransactionSerializer::encode('ForceSetConsumption', ForceSetConsumptionMutation::getEncodableParams(
            tankId: '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            ruleSetId: 255,
            totalConsumed: '100000000',
            userId: null,
            lastResetBlock: 100
        ));

        $callIndex = $this->codec->encoder()->getCallIndex('FuelTanks.force_set_consumption', true);
        $this->assertEquals(
            "0x{$callIndex}00d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00ff0000000284d7170164000000",
            $data
        );
    }
}
