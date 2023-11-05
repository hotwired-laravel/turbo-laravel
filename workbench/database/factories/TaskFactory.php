<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Workbench\App\Models\Task;

/**
 * @template TModel of \Workbench\App\Models\Task
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => BoardFactory::new(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];
    }
}
