<div id="sidebar" class="fixed top-16 bottom-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-all duration-300 ease-in-out lg:static lg:inset-0 lg:top-0 -translate-x-full lg:translate-x-0" data-collapsed="true">
    <div class="flex flex-col h-full">
        
        <!-- Sidebar Navigation -->
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Dashboard') }}">
                <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0" />
                </svg>
                <span class="nav-text whitespace-nowrap ml-3">{{ __('Dashboard') }}</span>
            </a>

            <a href="{{ route('projects.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('projects.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Projects') }}">
                <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="nav-text whitespace-nowrap ml-3">{{ __('Projects') }}</span>
            </a>

            <a href="{{ route('job-descriptions.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('job-descriptions.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Job Description') }}">
                <svg class="nav-icon h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                <span class="nav-text whitespace-nowrap ml-3">{{ __('Job Description') }}</span>
            </a>

            <a href="{{ route('candidates.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('candidates.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Candidates') }}">
                <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="nav-text whitespace-nowrap ml-3">{{ __('Candidates') }}</span>
            </a>

            <a href="{{ route('profiles.project-selection') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('profiles.project-selection') || request()->routeIs('projects.profiles.*') || request()->routeIs('projects.candidates.profiles.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Profiles') }}">
                <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="nav-text whitespace-nowrap ml-3">{{ __('Candidate Profiles') }}</span>
            </a>
            
            @if(Auth::user()->isAdmin())

                <a href="{{ route('ai-settings.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('ai-settings.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('AI Settings') }}">
                    <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="nav-text whitespace-nowrap ml-3">{{ __('AI Settings') }}</span>
                </a>

                <a href="{{ route('workable-settings.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('workable-settings.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Workable Settings') }}">
                    <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7M3 7l8 5 8-5" />
                    </svg>
                    <span class="nav-text whitespace-nowrap ml-3">{{ __('Workable Settings') }}</span>
                </a>

                <a href="{{ route('users.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('users.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Users') }}">
                    <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="nav-text whitespace-nowrap ml-3">{{ __('Users') }}</span>
                </a>

                <a href="{{ route('trash.index') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('trash.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Trash') }}">
                    <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1v3M4 7h16" />
                    </svg>
                    <span class="nav-text whitespace-nowrap ml-3">{{ __('Trash') }}</span>
                </a>
            @endif

            <a href="{{ route('submissions.show') }}" class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('submissions.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}" title="{{ __('Mail Logs') }}">
                <svg class="nav-icon h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.586 5.586a2 2 0 002.828 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="nav-text whitespace-nowrap ml-3">{{ __('Mail Logs') }}</span>
            </a>
        </nav>
    </div>
</div>