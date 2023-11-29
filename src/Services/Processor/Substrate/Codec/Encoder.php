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
        'DestroyFuelTank' => 'FuelTanks.destroy_fuel_tank',
        'ForceSetConsumption' => 'FuelTanks.force_set_consumption',
        'InsertRuleSet' => 'FuelTanks.insert_rule_set',
        'RemoveRuleSet' => 'FuelTanks.remove_rule_set',
        'RemoveAccountRuleData' => 'FuelTanks.remove_account_rule_data',
        'MutateFuelTank' => 'FuelTanks.mutate_fuel_tank',
        'ScheduleMutateFreezeState' => 'FuelTanks.mutate_freeze_state',
        'Dispatch' => 'FuelTanks.dispatch',
        'DispatchAndTouch' => 'FuelTanks.dispatch_and_touch',
    ];
}
