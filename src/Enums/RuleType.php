<?php

namespace Enjin\Platform\FuelTanks\Enums;

use Enjin\Platform\Traits\EnumExtensions;

enum RuleType: string
{
    use EnumExtensions;

    case ACCOUNT = 'Account';
    case DISPATCH = 'Dispatch';
}
