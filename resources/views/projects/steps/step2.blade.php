<!-- Step 2: Intake Form -->
<div class="step-content hidden" id="step-2">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Intake Form</h3>
    
    <div class="mb-4">
        <x-input-label for="job_title" :value="__('Job Title')" />
        <x-text-input id="job_title" class="block mt-1 w-full" type="text" name="job_title" :value="old('job_title', $project->job_title ?? '')" />
        <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="mb-4">
            <x-input-label for="experience_level" :value="__('Experience Level')" />
            <select id="experience_level" name="experience_level" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Select Experience Level</option>
                <option value="Entry Level" {{ old('experience_level', $project->experience_level ?? '') === 'Entry Level' ? 'selected' : '' }}>Entry Level</option>
                <option value="Mid Level" {{ old('experience_level', $project->experience_level ?? '') === 'Mid Level' ? 'selected' : '' }}>Mid Level</option>
                <option value="Senior Level" {{ old('experience_level', $project->experience_level ?? '') === 'Senior Level' ? 'selected' : '' }}>Senior Level</option>
                <option value="Executive" {{ old('experience_level', $project->experience_level ?? '') === 'Executive' ? 'selected' : '' }}>Executive</option>
            </select>
            <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="employment_type" :value="__('Employment Type')" />
            <select id="employment_type" name="employment_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Select Employment Type</option>
                <option value="Full-time" {{ old('employment_type', $project->employment_type ?? '') === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                <option value="Part-time" {{ old('employment_type', $project->employment_type ?? '') === 'Part-time' ? 'selected' : '' }}>Part-time</option>
                <option value="Contract" {{ old('employment_type', $project->employment_type ?? '') === 'Contract' ? 'selected' : '' }}>Contract</option>
                <option value="Temporary" {{ old('employment_type', $project->employment_type ?? '') === 'Temporary' ? 'selected' : '' }}>Temporary</option>
                <option value="Internship" {{ old('employment_type', $project->employment_type ?? '') === 'Internship' ? 'selected' : '' }}>Internship</option>
            </select>
            <x-input-error :messages="$errors->get('employment_type')" class="mt-2" />
        </div>
    </div>

    <div class="mb-4">
        <x-input-label for="education_requirements" :value="__('Education Requirements')" />
        <x-text-input id="education_requirements" class="block mt-1 w-full" type="text" name="education_requirements" :value="old('education_requirements', $project->education_requirements ?? '')" />
        <x-input-error :messages="$errors->get('education_requirements')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="salary_range" :value="__('Salary Range')" />
        <x-text-input id="salary_range" class="block mt-1 w-full" type="text" name="salary_range" :value="old('salary_range', $project->salary_range ?? '')" placeholder="e.g. $50,000 - $70,000" />
        <x-input-error :messages="$errors->get('salary_range')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="required_skills" :value="__('Required Skills')" />
        <textarea id="required_skills" name="required_skills" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="List the required skills for this position">{{ old('required_skills', $project->required_skills ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('required_skills')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="preferred_skills" :value="__('Preferred Skills')" />
        <textarea id="preferred_skills" name="preferred_skills" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="List any preferred skills for this position">{{ old('preferred_skills', $project->preferred_skills ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('preferred_skills')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="additional_notes" :value="__('Additional Notes')" />
        <textarea id="additional_notes" name="additional_notes" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Any additional information about the position">{{ old('additional_notes', $project->additional_notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('additional_notes')" class="mt-2" />
    </div>
</div>