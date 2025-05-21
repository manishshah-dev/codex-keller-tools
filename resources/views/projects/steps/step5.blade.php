<!-- Step 5: Additional Info -->
<div class="step-content hidden" id="step-5">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
    
    <div class="mb-6">
        <h4 class="font-medium text-gray-700 mb-2">Salary Comparison</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="mb-4">
                <x-input-label for="average_salary" :value="__('Average Salary')" />
                <x-text-input id="average_salary" class="block mt-1 w-full" type="number" step="0.01" name="average_salary" :value="old('average_salary', $project->average_salary ?? '')" />
                <x-input-error :messages="$errors->get('average_salary')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="min_salary" :value="__('Minimum Salary')" />
                <x-text-input id="min_salary" class="block mt-1 w-full" type="number" step="0.01" name="min_salary" :value="old('min_salary', $project->min_salary ?? '')" />
                <x-input-error :messages="$errors->get('min_salary')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="max_salary" :value="__('Maximum Salary')" />
                <x-text-input id="max_salary" class="block mt-1 w-full" type="number" step="0.01" name="max_salary" :value="old('max_salary', $project->max_salary ?? '')" />
                <x-input-error :messages="$errors->get('max_salary')" class="mt-2" />
            </div>
        </div>

        <div class="mb-4">
            <x-input-label for="similar_job_postings" :value="__('Similar Job Postings')" />
            <textarea id="similar_job_postings" name="similar_job_postings" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('similar_job_postings', $project->similar_job_postings ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('similar_job_postings')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="salary_data_source" :value="__('Salary Data Source')" />
            <x-text-input id="salary_data_source" class="block mt-1 w-full" type="text" name="salary_data_source" :value="old('salary_data_source', $project->salary_data_source ?? '')" placeholder="e.g. Glassdoor, Indeed, LinkedIn" />
            <x-input-error :messages="$errors->get('salary_data_source')" class="mt-2" />
        </div>
    </div>

    <div class="mb-6">
        <h4 class="font-medium text-gray-700 mb-2">Search Strings</h4>
        <div class="mb-4">
            <x-input-label for="linkedin_boolean_string" :value="__('LinkedIn Boolean String')" />
            <textarea id="linkedin_boolean_string" name="linkedin_boolean_string" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('linkedin_boolean_string', $project->linkedin_boolean_string ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('linkedin_boolean_string')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="google_xray_linkedin_string" :value="__('Google X-Ray LinkedIn String')" />
            <textarea id="google_xray_linkedin_string" name="google_xray_linkedin_string" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('google_xray_linkedin_string', $project->google_xray_linkedin_string ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('google_xray_linkedin_string')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="google_xray_cv_string" :value="__('Google X-Ray CV String')" />
            <textarea id="google_xray_cv_string" name="google_xray_cv_string" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('google_xray_cv_string', $project->google_xray_cv_string ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('google_xray_cv_string')" class="mt-2" />
        </div>
    </div>

    <div class="mb-6">
        <h4 class="font-medium text-gray-700 mb-2">Keywords & Questions</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <x-input-label for="keywords" :value="__('Keywords')" />
                <x-text-input id="keywords" class="block mt-1 w-full" type="text" name="keywords" :value="old('keywords', $project->keywords ?? '')" placeholder="Comma-separated list of keywords" />
                <x-input-error :messages="$errors->get('keywords')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="synonyms" :value="__('Synonyms')" />
                <x-text-input id="synonyms" class="block mt-1 w-full" type="text" name="synonyms" :value="old('synonyms', $project->synonyms ?? '')" placeholder="Comma-separated list of synonyms" />
                <x-input-error :messages="$errors->get('synonyms')" class="mt-2" />
            </div>
        </div>

        <div class="mb-4">
            <x-input-label for="translation_language" :value="__('Translation Language')" />
            <select id="translation_language" name="translation_language" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="" {{ old('translation_language', $project->translation_language ?? '') == '' ? 'selected' : '' }}>Select a language</option>
                <option value="fr" {{ old('translation_language', $project->translation_language ?? '') == 'fr' ? 'selected' : '' }}>French</option>
                <option value="es" {{ old('translation_language', $project->translation_language ?? '') == 'es' ? 'selected' : '' }}>Spanish</option>
                <option value="de" {{ old('translation_language', $project->translation_language ?? '') == 'de' ? 'selected' : '' }}>German</option>
                <option value="zh" {{ old('translation_language', $project->translation_language ?? '') == 'zh' ? 'selected' : '' }}>Chinese</option>
                <option value="ja" {{ old('translation_language', $project->translation_language ?? '') == 'ja' ? 'selected' : '' }}>Japanese</option>
                <option value="ru" {{ old('translation_language', $project->translation_language ?? '') == 'ru' ? 'selected' : '' }}>Russian</option>
                <option value="pt" {{ old('translation_language', $project->translation_language ?? '') == 'pt' ? 'selected' : '' }}>Portuguese</option>
                <option value="it" {{ old('translation_language', $project->translation_language ?? '') == 'it' ? 'selected' : '' }}>Italian</option>
                <option value="nl" {{ old('translation_language', $project->translation_language ?? '') == 'nl' ? 'selected' : '' }}>Dutch</option>
                <option value="ar" {{ old('translation_language', $project->translation_language ?? '') == 'ar' ? 'selected' : '' }}>Arabic</option>
                <option value="hi" {{ old('translation_language', $project->translation_language ?? '') == 'hi' ? 'selected' : '' }}>Hindi</option>
                <option value="ko" {{ old('translation_language', $project->translation_language ?? '') == 'ko' ? 'selected' : '' }}>Korean</option>
                <option value="multi" {{ old('translation_language', $project->translation_language ?? '') == 'multi' ? 'selected' : '' }}>Multiple Languages</option>
            </select>
            <x-input-error :messages="$errors->get('translation_language')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="translations" :value="__('Translations')" />
            <x-text-input id="translations" class="block mt-1 w-full" type="text" name="translations" :value="old('translations', $project->translations ?? '')" placeholder="Translations of keywords in other languages" />
            <x-input-error :messages="$errors->get('translations')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="candidate_questions" :value="__('Candidate Questions')" />
            <textarea id="candidate_questions" name="candidate_questions" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Questions to ask candidates">{{ old('candidate_questions', $project->candidate_questions ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('candidate_questions')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="recruiter_questions" :value="__('Recruiter Questions')" />
            <textarea id="recruiter_questions" name="recruiter_questions" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Questions for recruiters to ask">{{ old('recruiter_questions', $project->recruiter_questions ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('recruiter_questions')" class="mt-2" />
        </div>
    </div>
</div>