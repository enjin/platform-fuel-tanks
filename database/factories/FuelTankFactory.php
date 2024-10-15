<?php

namespace Enjin\Platform\FuelTanks\Database\Factories;

use Enjin\Platform\FuelTanks\Enums\CoveragePolicy;
use Enjin\Platform\FuelTanks\Models\FuelTank;
use Enjin\Platform\Providers\Faker\SubstrateProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class FuelTankFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = FuelTank::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->text(32),
            'public_key' => resolve(SubstrateProvider::class)->public_key(),
            'reserves_account_creation_deposit' => fake()->boolean(),
            'coverage_policy' => fake()->randomElement(CoveragePolicy::caseNamesAsArray()),
        ];
    }
}
