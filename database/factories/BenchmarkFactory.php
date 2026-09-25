<?php

namespace Database\Factories;

use App\Enums\BenchmarkStatus;
use App\Models\Benchmark;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Benchmark>
 */
class BenchmarkFactory extends Factory
{
    protected $model = Benchmark::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'prompt_text' => fake()->paragraph(),
            'status' => BenchmarkStatus::Pending,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BenchmarkStatus::Completed,
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BenchmarkStatus::Processing,
            'started_at' => now(),
        ]);
    }
}
