<?php

namespace Database\Factories;

use App\Models\AiModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiModel>
 */
class AiModelFactory extends Factory
{
    protected $model = AiModel::class;

    public function definition(): array
    {
        return [
            'model_name' => fake()->unique()->slug(),
            'provider' => fake()->randomElement(['openai', 'anthropic', 'google']),
            'display_name' => fake()->words(2, true),
            'input_price_per_1k_tokens' => fake()->randomFloat(6, 0.0001, 0.01),
            'output_price_per_1k_tokens' => fake()->randomFloat(6, 0.001, 0.05),
            'default_params' => ['temperature' => 0.7, 'max_tokens' => 4096, 'top_p' => 1.0],
            'is_active' => true,
        ];
    }
}
