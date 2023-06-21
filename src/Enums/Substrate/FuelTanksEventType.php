<?php

namespace Enjin\Platform\FuelTanks\Enums\Substrate;

use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\AccountAdded;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\AccountRemoved;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\AccountRuleDataRemoved;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\CallDispatched;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\FreezeStateMutated;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\FuelTankCreated;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\FuelTankDestroyed;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\FuelTankMutated;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\MutateFreezeStateScheduled;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\RuleSetInserted;
use Enjin\Platform\FuelTanks\Services\Processor\Substrate\Events\Implementations\FuelTanks\RuleSetRemoved;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Traits\EnumExtensions;

enum FuelTanksEventType: string
{
    use EnumExtensions;

    case FUEL_TANK_CREATED = 'FuelTankCreated';
    case FUEL_TANK_DESTROYED = 'FuelTankDestroyed';
    case FUEL_TANK_MUTATED = 'FuelTankMutated';
    case ACCOUNT_ADDED = 'AccountAdded';
    case ACCOUNT_REMOVED = 'AccountRemoved';
    case MUTATE_FREEZE_STATE_SCHEDULED = 'MutateFreezeStateScheduled';
    case FREEZE_STATE_MUTATED = 'FreezeStateMutated';
    case ACCOUNT_RULE_DATA_REMOVED = 'AccountRuleDataRemoved';
    case RULE_SET_INSERTED = 'RuleSetInserted';
    case RULE_SET_REMOVED = 'RuleSetRemoved';
    case CALL_DISPATCHED = 'CallDispatched';

    /**
     * Get the processor for the event.
     */
    public function getProcessor(): SubstrateEvent
    {
        return match ($this) {
            self::FUEL_TANK_CREATED => new FuelTankCreated(),
            self::FUEL_TANK_DESTROYED => new FuelTankDestroyed(),
            self::FUEL_TANK_MUTATED => new FuelTankMutated(),
            self::ACCOUNT_ADDED => new AccountAdded(),
            self::ACCOUNT_REMOVED => new AccountRemoved(),
            self::MUTATE_FREEZE_STATE_SCHEDULED => new MutateFreezeStateScheduled(),
            self::FREEZE_STATE_MUTATED => new FreezeStateMutated(),
            self::ACCOUNT_RULE_DATA_REMOVED => new AccountRuleDataRemoved(),
            self::RULE_SET_INSERTED => new RuleSetInserted(),
            self::RULE_SET_REMOVED => new RuleSetRemoved(),
            self::CALL_DISPATCHED => new CallDispatched(),
        };
    }
}
