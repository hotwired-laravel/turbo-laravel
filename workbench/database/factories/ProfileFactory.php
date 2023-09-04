<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\User\Profile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Workbench\App\Models\User\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
