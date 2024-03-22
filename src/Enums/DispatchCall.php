<?php

namespace Enjin\Platform\FuelTanks\Enums;

use Enjin\Platform\Traits\EnumExtensions;

enum DispatchCall: string
{
    use EnumExtensions;

    case MULTI_TOKENS = 'primary';
    case FUEL_TANKS = 'fuel-tanks';
    case MARKETPLACE = 'marketplace';
}
