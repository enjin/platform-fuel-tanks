<?php

namespace Enjin\Platform\FuelTanks\Services\Processor\Substrate\Codec;

use Enjin\Platform\Services\Processor\Substrate\Codec\Encoder as BaseEncoder;

class Encoder extends BaseEncoder
{
    protected static array $callIndexKeys = [
        'AddAccount' => 'FuelTanks.add_account',
        'RemoveAccount' => 'FuelTanks.remove_account',
        'BatchAddAccount' => 'FuelTanks.batch_add_account',
        'BatchRemoveAccount' => 'FuelTanks.batch_remove_account',
        'CreateFuelTank' => 'FuelTanks.create_fuel_tank',
        'CreateFuelTankV1010' => 'FuelTanks.create_fuel_tank',
        'DestroyFuelTank' => 'FuelTanks.destroy_fuel_tank',
        'ForceSetConsumption' => 'FuelTanks.force_set_consumption',
        'InsertRuleSet' => 'FuelTanks.insert_rule_set',
        'InsertRuleSetV1010' => 'FuelTanks.insert_rule_set',
        'RemoveRuleSet' => 'FuelTanks.remove_rule_set',
        'RemoveAccountRuleData' => 'FuelTanks.remove_account_rule_data',
        'RemoveAccountRuleDataV1010' => 'FuelTanks.remove_account_rule_data',
        'MutateFuelTank' => 'FuelTanks.mutate_fuel_tank',
        'MutateFuelTankV1010' => 'FuelTanks.mutate_fuel_tank',
        'MutateFreezeState' => 'FuelTanks.mutate_freeze_state',
        'Dispatch' => 'FuelTanks.dispatch',
        'DispatchV1010' => 'FuelTanks.dispatch',
        'DispatchAndTouch' => 'FuelTanks.dispatch_and_touch',
        'DispatchAndTouchV1010' => 'FuelTanks.dispatch_and_touch',
    ];
}
