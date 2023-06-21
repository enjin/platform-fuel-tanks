<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Queries;

use Enjin\Platform\FuelTanks\GraphQL\Traits\InFuelTanksSchema;
use Enjin\Platform\Interfaces\PlatformGraphQlQuery;
use Rebing\GraphQL\Support\Query as GraphQlQuery;

abstract class Query extends GraphQlQuery implements PlatformGraphQlQuery
{
    use InFuelTanksSchema;
}
