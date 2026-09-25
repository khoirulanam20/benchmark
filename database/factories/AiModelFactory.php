<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<AiModel>
 */
class AiModelFactory extends Factory
{
    protected $model = AiModel::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'model_name' => fake()->unique()->slug(),
            'provider' => fake()->randomElement(['openai', 'anthropic', 'google']),
            'display_name' => fake()->words(2, true),
            'input_price_per_1k_tokens' => fake()->randomFloat(6, 0.0001, 0.01),
            'output_price_per_1k_tokens' => fake()->randomFloat(6, 0.001, 0.05),
            'default_params' => ['temperature' => 0.7, 'max_tokens' => 4096, 'top_p' => 1.0],
            'is_active' => true,
            'encrypted_api_key' => null,
            'base_url' => null,
        ];
    }

    public function forUser($user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user instanceof User ? $user->id : $user,
        ]);
    }

    public function withApiKey(string $key = 'sk-test-1234567890'): static
    {
        return $this->state(fn (array $attributes) => [
            'encrypted_api_key' => Crypt::encryptString($key),
        ]);
    }
}
