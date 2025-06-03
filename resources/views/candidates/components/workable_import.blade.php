<div class="border border-gray-200 rounded-lg p-4">
    <h4 class="font-medium mb-2">Import from Workable</h4>
    <p class="text-sm text-gray-600 mb-4">Import candidates directly from your Workable account.</p>
    
    <form action="{{ route('projects.candidates.index', $project) }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="department" :value="__('Department')" />
                <select id="department" name="department" class="select2 block mt-1 w-full">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="country" :value="__('Country')" />
                <select id="country" name="country" class="select2 block mt-1 w-full">
                    <option value="">All Countries</option>
                    @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <x-input-label for="workable_job" :value="__('Job')" />
            <select id="workable_job" name="workable_job" class="select2 block mt-1 w-full">
                <option value="">Select Workable Job</option>
                @foreach($jobs as $job)
                    @php
                        $text = $job->title;
                        if($job->city || $job->country) {
                            $text .= ' (' . trim($job->city . ', ' . $job->country, ', ') . ')';
                        }
                    @endphp
                    <option value="{{ $job->id }}" data-department="{{ $job->department }}" data-country="{{ $job->country }}" {{ request('job') == $job->id ? 'selected' : '' }}>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="filter_email" value="1" {{ request('filter_email') ? 'checked' : '' }} class="rounded" />
                    <span class="text-sm">{{ __('Filter by Email') }}</span>
                </label>
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{ request('email') }}" />
            </div>
            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="filter_created_after" value="1" {{ request('filter_created_after') ? 'checked' : '' }} class="rounded" />
                    <span class="text-sm">{{ __('Created After') }}</span>
                </label>
                <input id="created_after" type="datetime-local" name="created_after" value="{{ request('created_after') }}" class="block mt-1 w-full" />
            </div>
        </div>
        <div class="flex justify-end">
            <x-primary-button>{{ __('Load Candidates') }}</x-primary-button>
        </div>
    </form>

    <form action="{{ route('projects.candidates.import-workable', $project) }}" method="POST" class="space-y-4 mt-6">
        @csrf
        <input type="hidden" name="job" value="{{ request('job') }}" />
        <input type="hidden" name="filter_email" value="{{ request('filter_email') }}" />
        <input type="hidden" name="email" value="{{ request('email') }}" />
        <input type="hidden" name="filter_created_after" value="{{ request('filter_created_after') }}" />
        <input type="hidden" name="created_after" value="{{ request('created_after') }}" />
        <div>
            <x-input-label for="workable_candidates" :value="__('Select Candidates')" />
            <select id="workable_candidates" name="workable_candidates[]" multiple class="select2 block mt-1 w-full">
                @foreach($workableCandidates as $candidate)
                    <option value="{{ $candidate['id'] }}">{{ $candidate['name'] }} - {{ $candidate['job']['title'] ?? '' }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('workable_candidates')" class="mt-2" />
        </div>
        <div class="flex justify-end">
            <x-primary-button>{{ __('Import Candidates') }}</x-primary-button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {        
        function filterJobs() {
            const dept = $('#department').val();
            const country = $('#country').val();
            $('#workable_job option').each(function() {
                const optDept = $(this).data('department') || '';
                const optCountry = $(this).data('country') || '';
                const show = (!dept || optDept === dept) && (!country || optCountry === country) || $(this).val() === '';
                $(this).prop('hidden', !show);
                // $(this).remove();
            });
            $('#workable_job').trigger('change.select2');
        }

        $('#department').on('change', filterJobs);
        $('#country').on('change', filterJobs);
        filterJobs();
    });
</script>