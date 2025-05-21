<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobDescriptionTemplate; // Import the model
use Illuminate\View\View; // Import View
use Illuminate\Http\RedirectResponse; // Import RedirectResponse
use Illuminate\Validation\Rule; // Import Rule facade

class JobDescriptionTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $templates = JobDescriptionTemplate::orderBy('name')->get();
        return view('job_description_templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('job_description_templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_description_templates,name',
            'description' => 'nullable|string',
            'industry' => 'required|string|max:255', // Made required based on original migration
            'job_level' => 'required|string|max:255', // Made required based on original migration
            'overview_template' => 'nullable|string',
            'responsibilities_template' => 'nullable|string',
            'requirements_template' => 'nullable|string',
            'benefits_template' => 'nullable|string',
            'disclaimer_template' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active'); // Handle checkbox

        JobDescriptionTemplate::create($validated);

        return redirect()->route('job-description-templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobDescriptionTemplate $jobDescriptionTemplate): View
    {
        // Rename variable for clarity in view context
        $template = $jobDescriptionTemplate;
        return view('job_description_templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobDescriptionTemplate $jobDescriptionTemplate): View // Use route model binding
    {
        // Rename variable for clarity in view context
        $template = $jobDescriptionTemplate;
        return view('job_description_templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobDescriptionTemplate $jobDescriptionTemplate): RedirectResponse // Use route model binding
    {
        $validated = $request->validate([
            // Ensure name is unique, ignoring the current template's name
            'name' => ['required', 'string', 'max:255', Rule::unique('job_description_templates', 'name')->ignore($jobDescriptionTemplate->id)],
            'description' => 'nullable|string',
            'industry' => 'required|string|max:255', // Made required based on original migration
            'job_level' => 'required|string|max:255', // Made required based on original migration
            'overview_template' => 'nullable|string',
            'responsibilities_template' => 'nullable|string',
            'requirements_template' => 'nullable|string',
            'benefits_template' => 'nullable|string',
            'disclaimer_template' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active'); // Handle checkbox

        $jobDescriptionTemplate->update($validated);

        return redirect()->route('job-description-templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobDescriptionTemplate $jobDescriptionTemplate): RedirectResponse // Use route model binding
    {
        $jobDescriptionTemplate->delete(); // Soft delete if SoftDeletes trait is used

        return redirect()->route('job-description-templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  \App\Models\JobDescriptionTemplate  $jobDescriptionTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate(JobDescriptionTemplate $jobDescriptionTemplate): RedirectResponse
    {
        $duplicate = $jobDescriptionTemplate->duplicate();

        return redirect()->route('job-description-templates.edit', $duplicate)
            ->with('success', 'Template duplicated successfully. You are now editing the new copy.');
    }
}
