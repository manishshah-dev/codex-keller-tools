<!-- Step 4: Job Description -->
<div class="step-content hidden" id="step-4">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Job Description</h3>
    
    <div class="mb-4">
        <x-input-label for="overview" :value="__('Overview')" />
        <textarea id="overview" name="overview" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('overview', $project->overview ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('overview')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="responsibilities" :value="__('Responsibilities')" />
        <textarea id="responsibilities" name="responsibilities" rows="6" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('responsibilities', $project->responsibilities ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('responsibilities')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="requirements_non_negotiable" :value="__('Non-Negotiable Requirements')" />
        <textarea id="requirements_non_negotiable" name="requirements_non_negotiable" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('requirements_non_negotiable', $project->requirements_non_negotiable ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('requirements_non_negotiable')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="requirements_preferred" :value="__('Preferred Requirements')" />
        <textarea id="requirements_preferred" name="requirements_preferred" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('requirements_preferred', $project->requirements_preferred ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('requirements_preferred')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="compensation_range" :value="__('Compensation Range')" />
        <x-text-input id="compensation_range" class="block mt-1 w-full" type="text" name="compensation_range" :value="old('compensation_range', $project->compensation_range ?? '')" />
        <x-input-error :messages="$errors->get('compensation_range')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="benefits" :value="__('Benefits')" />
        <textarea id="benefits" name="benefits" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('benefits', $project->benefits ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('benefits')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="jd_status" :value="__('Job Description Status')" />
        <select id="jd_status" name="jd_status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="draft" {{ old('jd_status', $project->jd_status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="review" {{ old('jd_status', $project->jd_status ?? '') === 'review' ? 'selected' : '' }}>In Review</option>
            <option value="approved" {{ old('jd_status', $project->jd_status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="published" {{ old('jd_status', $project->jd_status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        <x-input-error :messages="$errors->get('jd_status')" class="mt-2" />
    </div>
</div>