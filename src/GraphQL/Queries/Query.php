<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Queries;

use Enjin\Platform\FuelTanks\GraphQL\Traits\InFuelTanksSchema;
use Enjin\Platform\Interfaces\PlatformGraphQlQuery;
use Rebing\GraphQL\Support\Query as GraphQlQuery;

abstract class Query extends GraphQlQuery implements PlatformGraphQlQuery
{
    use InFuelTanksSchema;

    /**
     * Adhoc rules.
     *
     * @var array
     */
    public static $adhocRules = [];

    /**
     * Get validation rules.
     */
    public function getRules(array $arguments = []): array
    {
        return collect(parent::getRules($arguments))->mergeRecursive(static::$adhocRules)->all();
    }
}
