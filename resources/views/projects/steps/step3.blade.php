<!-- Step 3: Company Research -->
<div class="step-content hidden" id="step-3">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Company Research</h3>
    
    <div class="mb-4">
        <x-input-label for="company_name" :value="__('Company Name')" />
        <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name', $project->company_name ?? '')" />
        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="mb-4">
            <x-input-label for="founding_date" :value="__('Founding Date')" />
            <x-text-input id="founding_date" class="block mt-1 w-full" type="date" name="founding_date" :value="old('founding_date', $project->founding_date ?? '')" />
            <x-input-error :messages="$errors->get('founding_date')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="company_size" :value="__('Company Size')" />
            <x-text-input id="company_size" class="block mt-1 w-full" type="text" name="company_size" :value="old('company_size', $project->company_size ?? '')" placeholder="e.g. 50-100 employees" />
            <x-input-error :messages="$errors->get('company_size')" class="mt-2" />
        </div>
    </div>

    <div class="mb-4">
        <x-input-label for="turnover" :value="__('Annual Turnover')" />
        <x-text-input id="turnover" class="block mt-1 w-full" type="text" name="turnover" :value="old('turnover', $project->turnover ?? '')" placeholder="e.g. $5-10 million" />
        <x-input-error :messages="$errors->get('turnover')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="mb-4">
            <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" />
            <x-text-input id="linkedin_url" class="block mt-1 w-full" type="url" name="linkedin_url" :value="old('linkedin_url', $project->linkedin_url ?? '')" />
            <x-input-error :messages="$errors->get('linkedin_url')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="website_url" :value="__('Website URL')" />
            <x-text-input id="website_url" class="block mt-1 w-full" type="url" name="website_url" :value="old('website_url', $project->website_url ?? '')" />
            <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
        </div>
    </div>

    <div class="mb-4">
        <x-input-label for="competitors" :value="__('Competitors')" />
        <x-text-input id="competitors" class="block mt-1 w-full" type="text" name="competitors" :value="old('competitors', $project->competitors ?? '')" placeholder="Comma-separated list of competitors" />
        <x-input-error :messages="$errors->get('competitors')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="industry_details" :value="__('Industry Details')" />
        <textarea id="industry_details" name="industry_details" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('industry_details', $project->industry_details ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('industry_details')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="typical_clients" :value="__('Typical Clients')" />
        <textarea id="typical_clients" name="typical_clients" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('typical_clients', $project->typical_clients ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('typical_clients')" class="mt-2" />
    </div>
</div>