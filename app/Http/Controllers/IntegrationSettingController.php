<?php

namespace App\Http\Controllers;

use App\Models\IntegrationSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class IntegrationSettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', IntegrationSetting::class);
        $settings = IntegrationSetting::all();
        return view('integration_settings.index', compact('settings'));
    }

    public function create(): View
    {
        $this->authorize('create', IntegrationSetting::class);
        return view('integration_settings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', IntegrationSetting::class);
        $validated = $request->validate([
            'integration' => 'required|string|in:workable,brighthire|unique:integration_settings,integration',
            'name' => 'required|string|max:255',
            'subdomain' => 'nullable|string',
            'api_token' => 'required|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            IntegrationSetting::where('integration', $validated['integration'])
                ->update(['is_default' => false]);
        }

        IntegrationSetting::create([
            'integration' => $validated['integration'],
            'name' => $validated['name'],
            'subdomain' => $validated['subdomain'],
            'api_token' => $validated['api_token'],
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('integration-settings.index')->with('success', 'Integration setting saved.');
    }

    public function edit(IntegrationSetting $integrationSetting): View
    {
        $this->authorize('update', $integrationSetting);
        return view('integration_settings.edit', compact('integrationSetting'));
    }

    public function update(Request $request, IntegrationSetting $integrationSetting): RedirectResponse
    {
        $this->authorize('update', $integrationSetting);
        $validated = $request->validate([
            'integration' => 'required|string|in:workable,brighthire|unique:integration_settings,integration,' . $integrationSetting->id,
            'name' => 'required|string|max:255',
            'subdomain' => 'nullable|string',
            'api_token' => 'required|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            IntegrationSetting::where('id', '!=', $integrationSetting->id)
                ->where('integration', $validated['integration'])
                ->update(['is_default' => false]);
        }

        $integrationSetting->update([
            'integration' => $validated['integration'],
            'name' => $validated['name'],
            'subdomain' => $validated['subdomain'],
            'api_token' => $validated['api_token'],
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('integration-settings.index')->with('success', 'Integration setting updated.');
    }

    public function destroy(IntegrationSetting $integrationSetting): RedirectResponse
    {
        $this->authorize('delete', $integrationSetting);
        $integrationSetting->delete();
        return redirect()->route('integration-settings.index')->with('success', 'Integration setting deleted.');
    }
}
