<!-- resources/views/job_description_templates/_form.blade.php -->
<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Template Name')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $template->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description (Optional)')" />
        <textarea id="description" name="description" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $template->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="industry" :value="__('Industry')" />
        <x-text-input id="industry" class="block mt-1 w-full" type="text" name="industry" :value="old('industry', $template->industry ?? '')" />
        <x-input-error :messages="$errors->get('industry')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="job_level" :value="__('Job Level')" />
        <select id="job_level" name="job_level" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Select Job Level</option>
            <option value="entry" {{ old('job_level', $template->job_level ?? '') === 'entry' ? 'selected' : '' }}>Entry Level</option>
            <option value="mid" {{ old('job_level', $template->job_level ?? '') === 'mid' ? 'selected' : '' }}>Mid Level</option>
            <option value="senior" {{ old('job_level', $template->job_level ?? '') === 'senior' ? 'selected' : '' }}>Senior Level</option>
            <option value="executive" {{ old('job_level', $template->job_level ?? '') === 'executive' ? 'selected' : '' }}>Executive</option>
        </select>
        <x-input-error :messages="$errors->get('job_level')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="overview_template" :value="__('Overview Template')" />
        <textarea id="overview_template" name="overview_template" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('overview_template', $template->overview_template ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('overview_template')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="responsibilities_template" :value="__('Responsibilities Template')" />
        <textarea id="responsibilities_template" name="responsibilities_template" rows="6" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('responsibilities_template', $template->responsibilities_template ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('responsibilities_template')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="requirements_template" :value="__('Requirements Template')" />
        <textarea id="requirements_template" name="requirements_template" rows="6" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('requirements_template', $template->requirements_template ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('requirements_template')" class="mt-2" />
        <p class="text-sm text-gray-500 mt-1">Include both non-negotiable and preferred requirements here.</p>
    </div>

    <div>
        <x-input-label for="benefits_template" :value="__('Benefits Template')" />
        <textarea id="benefits_template" name="benefits_template" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('benefits_template', $template->benefits_template ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('benefits_template')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="disclaimer_template" :value="__('Disclaimer Template')" />
        <textarea id="disclaimer_template" name="disclaimer_template" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('disclaimer_template', $template->disclaimer_template ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('disclaimer_template')" class="mt-2" />
    </div>

    <div class="flex items-center">
        <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <label for="is_active" class="ml-2 text-sm text-gray-600">Active Template</label>
    </div>
</div>