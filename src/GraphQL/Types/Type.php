<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Types;

use Enjin\Platform\FuelTanks\GraphQL\Traits\InFuelTanksSchema;
use Enjin\Platform\Interfaces\PlatformGraphQlType;
use Rebing\GraphQL\Support\Type as GraphQlType;

abstract class Type extends GraphQlType implements PlatformGraphQlType
{
    use InFuelTanksSchema;
}
