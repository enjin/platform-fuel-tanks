<?php

namespace Enjin\Platform\FuelTanks\Database\Factories;

use Enjin\Platform\FuelTanks\Models\AccountRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = AccountRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'rule' => '', // AccountRule,
            'value' => [],
        ];
    }
}
