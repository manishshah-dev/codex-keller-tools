<?php

namespace App\Http\Controllers;

use App\Models\WorkableSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WorkableSettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', WorkableSetting::class);
        $settings = WorkableSetting::all();
        return view('workable_settings.index', compact('settings'));
    }

    public function create(): View
    {
        $this->authorize('create', WorkableSetting::class);
        return view('workable_settings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WorkableSetting::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string',
            'api_token' => 'required|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            WorkableSetting::where('is_default', true)->update(['is_default' => false]);
        }

        WorkableSetting::create([
            'name' => $validated['name'],
            'subdomain' => $validated['subdomain'],
            'api_token' => $validated['api_token'],
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('workable-settings.index')->with('success', 'Workable setting saved.');
    }

    public function edit(WorkableSetting $workableSetting): View
    {
        $this->authorize('update', $workableSetting);
        return view('workable_settings.edit', compact('workableSetting'));
    }

    public function update(Request $request, WorkableSetting $workableSetting): RedirectResponse
    {
        $this->authorize('update', $workableSetting);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string',
            'api_token' => 'required|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            WorkableSetting::where('id', '!=', $workableSetting->id)->update(['is_default' => false]);
        }

        $workableSetting->update([
            'name' => $validated['name'],
            'subdomain' => $validated['subdomain'],
            'api_token' => $validated['api_token'],
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return redirect()->route('workable-settings.index')->with('success', 'Workable setting updated.');
    }

    public function destroy(WorkableSetting $workableSetting): RedirectResponse
    {
        $this->authorize('delete', $workableSetting);
        $workableSetting->delete();
        return redirect()->route('workable-settings.index')->with('success', 'Workable setting deleted.');
    }
}
