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
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'api_endpoint' => 'required|string',
            'api_key' => 'required|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            IntegrationSetting::where('is_default', true)
                ->where('type', $validated['type'])
                ->update(['is_default' => false]);
        }

        IntegrationSetting::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'api_endpoint' => $validated['api_endpoint'],
            'api_key' => $validated['api_key'],
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
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'api_endpoint' => 'required|string',
            'api_key' => 'required|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            IntegrationSetting::where('id', '!=', $integrationSetting->id)
                ->where('type', $validated['type'])
                ->update(['is_default' => false]);
        }

        $integrationSetting->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'api_endpoint' => $validated['api_endpoint'],
            'api_key' => $validated['api_key'],
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
