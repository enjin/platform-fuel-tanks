<?php

namespace Enjin\Platform\FuelTanks\GraphQL\Mutations;

use Enjin\Platform\FuelTanks\GraphQL\Traits\InFuelTanksSchema;
use Enjin\Platform\Interfaces\PlatformGraphQlMutation;
use Rebing\GraphQL\Support\Mutation as GraphQlMutation;

abstract class Mutation extends GraphQlMutation implements PlatformGraphQlMutation
{
    use InFuelTanksSchema;

    /**
     * Adhoc rules.
     *
     * @var array
     */
    public static $adhocRules = [];

    /**
     * Get the blockchain method name from the graphql mutation name.
     */
    public function getMethodName(): string
    {
        return $this->attributes()['name'];
    }

    /**
     * Get the graphql mutation name.
     */
    public function getMutationName(): string
    {
        return $this->attributes()['name'];
    }

    /**
     * Get validation rules.
     */
    public function getRules(array $arguments = []): array
    {
        return collect(parent::getRules($arguments))->mergeRecursive(static::$adhocRules)->all();
    }

    public static function getEncodableParams(...$params): array
    {
        return [];
    }
}
