<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    private const PROVIDERS = ['openai', 'anthropic', 'google'];

    public function index(Request $request): View
    {
        $apiKeys = ApiKey::where('user_id', $request->user()->id)
            ->orderBy('provider_name')
            ->get();

        return view('api-keys.index', [
            'apiKeys' => $apiKeys,
            'providers' => self::PROVIDERS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'provider_name' => ['required', Rule::in(self::PROVIDERS)],
            'api_key' => ['required', 'string', 'min:10'],
        ]);

        ApiKey::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'provider_name' => $request->provider_name,
            ],
            [
                'encrypted_key' => Crypt::encryptString($request->api_key),
            ],
        );

        return back()->with('status', "API key for {$request->provider_name} saved securely.");
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_unless($apiKey->user_id === $request->user()->id, 403);

        $apiKey->delete();

        return back()->with('status', 'API key deleted.');
    }
}
