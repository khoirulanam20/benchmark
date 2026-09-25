<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'typesafe_model' => AppSetting::get('typesafe_model', 'jev-latest'),
            'typesafe_base_url' => AppSetting::get('typesafe_base_url', 'https://api.typesafe.ai'),
            'has_typesafe_key' => AppSetting::hasSecret('typesafe_api_key'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'typesafe_api_key' => ['nullable', 'string', 'min:10'],
            'typesafe_model' => ['required', 'string', 'max:100'],
            'typesafe_base_url' => ['required', 'url', 'max:500'],
        ]);

        if (! empty($validated['typesafe_api_key'])) {
            AppSetting::set('typesafe_api_key', $validated['typesafe_api_key']);
        }

        AppSetting::set('typesafe_model', $validated['typesafe_model']);
        AppSetting::set('typesafe_base_url', rtrim($validated['typesafe_base_url'], '/'));

        return redirect()->route('admin.settings.edit')
            ->with('status', 'Pengaturan scoring (TypeSafe Jev) disimpan.');
    }
}
