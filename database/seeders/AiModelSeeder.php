<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

class AiModelSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            [
                'model_name' => 'gpt-4o',
                'provider' => 'openai',
                'display_name' => 'GPT-4o',
                'input_price_per_1k_tokens' => 0.002500,
                'output_price_per_1k_tokens' => 0.010000,
                'default_params' => ['temperature' => 0.7, 'max_tokens' => 4096, 'top_p' => 1.0],
                'is_active' => true,
            ],
            [
                'model_name' => 'gpt-4o-mini',
                'provider' => 'openai',
                'display_name' => 'GPT-4o Mini',
                'input_price_per_1k_tokens' => 0.000150,
                'output_price_per_1k_tokens' => 0.000600,
                'default_params' => ['temperature' => 0.7, 'max_tokens' => 4096, 'top_p' => 1.0],
                'is_active' => true,
            ],
            [
                'model_name' => 'claude-sonnet-4-20250514',
                'provider' => 'anthropic',
                'display_name' => 'Claude Sonnet 4',
                'input_price_per_1k_tokens' => 0.003000,
                'output_price_per_1k_tokens' => 0.015000,
                'default_params' => ['temperature' => 0.7, 'max_tokens' => 4096, 'top_p' => 1.0],
                'is_active' => true,
            ],
            [
                'model_name' => 'claude-haiku-35-20241022',
                'provider' => 'anthropic',
                'display_name' => 'Claude 3.5 Haiku',
                'input_price_per_1k_tokens' => 0.000800,
                'output_price_per_1k_tokens' => 0.004000,
                'default_params' => ['temperature' => 0.7, 'max_tokens' => 4096, 'top_p' => 1.0],
                'is_active' => true,
            ],
            [
                'model_name' => 'gemini-2.0-flash',
                'provider' => 'google',
                'display_name' => 'Gemini 2.0 Flash',
                'input_price_per_1k_tokens' => 0.000100,
                'output_price_per_1k_tokens' => 0.000400,
                'default_params' => ['temperature' => 0.7, 'max_tokens' => 8192, 'top_p' => 1.0],
                'is_active' => true,
            ],
            [
                'model_name' => 'gemini-2.5-pro-preview-05-06',
                'provider' => 'google',
                'display_name' => 'Gemini 2.5 Pro',
                'input_price_per_1k_tokens' => 0.001250,
                'output_price_per_1k_tokens' => 0.010000,
                'default_params' => ['temperature' => 0.7, 'max_tokens' => 8192, 'top_p' => 1.0],
                'is_active' => true,
            ],
        ];

        foreach ($models as $model) {
            AiModel::updateOrCreate(
                ['model_name' => $model['model_name']],
                $model,
            );
        }
    }
}
