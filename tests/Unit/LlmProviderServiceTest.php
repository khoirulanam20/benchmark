<?php

namespace Tests\Unit;

use App\Models\AiModel;
use App\Services\LlmProviderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmProviderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_openai_compatible_does_not_double_v1_in_url(): void
    {
        Http::fake(function (Request $request) {
            $this->assertStringEndsWith('/v1/chat/completions', $request->url());
            $this->assertStringNotContainsString('/v1/v1/', $request->url());

            return Http::response([
                'choices' => [['message' => ['content' => 'OK']]],
                'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
            ]);
        });

        $model = new AiModel([
            'model_name' => 'test-model',
            'provider' => 'openai_compatible',
            'base_url' => 'https://proxy.example.com/v1',
        ]);
        $model->api_key = 'test-key-12345';

        $service = app(LlmProviderService::class);
        $result = $service->sendPrompt($model, 'Hi');

        $this->assertSame('OK', $result['output']);
    }

    public function test_gpt_6_models_use_max_completion_tokens(): void
    {
        Http::fake(function (Request $request) {
            $body = json_decode($request->body(), true);
            $this->assertArrayHasKey('max_completion_tokens', $body);
            $this->assertArrayNotHasKey('max_tokens', $body);
            $this->assertFalse($body['stream']);

            return Http::response([
                'choices' => [['message' => ['content' => 'OK']]],
                'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
            ]);
        });

        $model = new AiModel([
            'model_name' => 'gpt-6-luna',
            'provider' => 'openai_compatible',
            'base_url' => 'https://sumopod.example.com/v1',
            'default_params' => ['max_tokens' => 512, 'temperature' => 0.7],
        ]);
        $model->api_key = 'test-key-12345';

        $service = app(LlmProviderService::class);
        $result = $service->sendPrompt($model, 'Hi');

        $this->assertSame('OK', $result['output']);
    }

    public function test_parses_legacy_choice_text_field(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['text' => 'legacy output']],
                'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
            ]),
        ]);

        $model = new AiModel([
            'model_name' => 'router-model',
            'provider' => 'openai_compatible',
            'base_url' => 'https://9routers.example.com',
        ]);
        $model->api_key = 'test-key-12345';

        $service = app(LlmProviderService::class);
        $result = $service->sendPrompt($model, 'Hi');

        $this->assertSame('legacy output', $result['output']);
    }
}
