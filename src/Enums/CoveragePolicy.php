<?php

namespace Enjin\Platform\FuelTanks\Enums;

use Enjin\Platform\Traits\EnumExtensions;

enum CoveragePolicy: string
{
    use EnumExtensions;
    case FEES = 'Fees';
    case FEES_AND_DEPOSIT = 'FeesAndDeposit';
}
