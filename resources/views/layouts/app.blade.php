<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 overflow-hidden">
        <!-- Top Header - Full Width -->
        <x-header />

        <div class="min-h-screen flex pt-16">
            <!-- Sidebar -->
            <x-sidebar />

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    <!-- Page Heading -->
                    @isset($header)
                        <div class="bg-white border-b border-gray-200">
                            <div class="px-6 py-4">
                                {{ $header }}
                            </div>
                        </div>
                    @endisset

                    <!-- Main Content Area -->
                    <div class="p-6 pl-10 overflow-y-auto h-[80vh]">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <!-- Sidebar overlay for mobile -->
        <div id="sidebar-overlay" class="fixed top-16 bottom-0 left-0 right-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

        <!-- JavaScript for sidebar toggle -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.getElementById('sidebar-toggle');
                const sidebarClose = document.getElementById('sidebar-close');
                const sidebarOverlay = document.getElementById('sidebar-overlay');

                function toggleSidebar() {
                    const collapsed = sidebar.getAttribute('data-collapsed') === 'true';
                    sidebar.setAttribute('data-collapsed', collapsed ? 'false' : 'true');
                    if (window.innerWidth < 1024) {
                        sidebar.classList.toggle('-translate-x-full');
                        sidebarOverlay.classList.toggle('hidden');
                    }
                }

                function closeSidebarMobile() {
                    if (window.innerWidth < 1024) {
                        sidebar.classList.add('-translate-x-full');
                        sidebarOverlay.classList.add('hidden');
                    }
                }

                // Start collapsed on both desktop and mobile
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.setAttribute('data-collapsed', 'true');
                    sidebarOverlay.classList.add('hidden');
                } else {
                    // sidebar.setAttribute('data-collapsed', 'true');
                }

                sidebarToggle?.addEventListener('click', toggleSidebar);
                sidebarClose?.addEventListener('click', closeSidebarMobile);
                sidebarOverlay?.addEventListener('click', closeSidebarMobile);
            });
        </script>

        <!-- jQuery (Select2 dependency) -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        
        {{-- Stack for page-specific scripts --}}
        @stack('scripts')
    </body>
</html>
