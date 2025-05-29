<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
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
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const sidebarToggle = document.getElementById('sidebar-toggle');
                const sidebarClose = document.getElementById('sidebar-close');
                const sidebarOverlay = document.getElementById('sidebar-overlay');
                const mainContent = document.querySelector('.flex-1.flex.flex-col');

                let isCollapsed = true; // Start collapsed by default

                function toggleSidebar() {
                    isCollapsed = !isCollapsed;
                    
                    if (window.innerWidth >= 1024) { // Desktop
                        if (isCollapsed) {
                            // Collapse sidebar
                            sidebar.style.width = '80px';
                            sidebar.setAttribute('data-collapsed', 'true');
                            
                            // Hide text elements
                            const navTexts = sidebar.querySelectorAll('.nav-text');
                            navTexts.forEach(text => {
                                text.style.display = 'none';
                            });
                            
                            // Center icons and adjust padding
                            const navItems = sidebar.querySelectorAll('.nav-item');
                            navItems.forEach(item => {
                                item.style.justifyContent = 'center';
                                item.style.paddingLeft = '1rem';
                                item.style.paddingRight = '1rem';
                            });
                            
                            // Hide logo text, show only icon
                            const logoContainer = sidebar.querySelector('.flex.items-center');
                            if (logoContainer) {
                                logoContainer.style.justifyContent = 'center';
                                const logo = logoContainer.querySelector('svg');
                                if (logo) {
                                    logo.style.width = '24px';
                                    logo.style.height = '24px';
                                }
                            }
                            
                        } else {
                            // Expand sidebar
                            sidebar.style.width = '256px';
                            sidebar.setAttribute('data-collapsed', 'false');
                            
                            // Show text elements
                            const navTexts = sidebar.querySelectorAll('.nav-text');
                            navTexts.forEach(text => {
                                text.style.display = 'block';
                            });
                            
                            // Reset nav items
                            const navItems = sidebar.querySelectorAll('.nav-item');
                            navItems.forEach(item => {
                                item.style.justifyContent = '';
                                item.style.paddingLeft = '';
                                item.style.paddingRight = '';
                            });
                            
                            // Reset logo
                            const logoContainer = sidebar.querySelector('.flex.items-center');
                            if (logoContainer) {
                                logoContainer.style.justifyContent = '';
                                const logo = logoContainer.querySelector('svg');
                                if (logo) {
                                    logo.style.width = '';
                                    logo.style.height = '';
                                }
                            }
                        }
                    } else { // Mobile
                        // Ensure text is always visible on mobile
                        const navTexts = sidebar.querySelectorAll('.nav-text');
                        navTexts.forEach(text => {
                            text.style.display = 'block';
                        });
                        
                        // Reset any desktop collapse styles
                        const navItems = sidebar.querySelectorAll('.nav-item');
                        navItems.forEach(item => {
                            item.style.justifyContent = '';
                            item.style.paddingLeft = '';
                            item.style.paddingRight = '';
                        });
                        
                        sidebar.style.width = '';
                        sidebar.setAttribute('data-collapsed', 'false');
                        
                        if (isCollapsed) {
                            sidebar.classList.add('-translate-x-full');
                            sidebarOverlay.classList.add('hidden');
                        } else {
                            sidebar.classList.remove('-translate-x-full');
                            sidebarOverlay.classList.remove('hidden');
                        }
                    }
                }

                function closeSidebarMobile() {
                    if (window.innerWidth < 1024) {
                        isCollapsed = true;
                        sidebar.classList.add('-translate-x-full');
                        sidebarOverlay.classList.add('hidden');
                    }
                }

                // Initialize state based on screen size
                if (window.innerWidth < 1024) {
                    // Mobile: start hidden
                    isCollapsed = true;
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                } else {
                    // Desktop: start collapsed
                    isCollapsed = true;
                    sidebar.style.width = '80px';
                    sidebar.setAttribute('data-collapsed', 'true');
                    
                    // Hide text elements
                    const navTexts = sidebar.querySelectorAll('.nav-text');
                    navTexts.forEach(text => {
                        text.style.display = 'none';
                    });
                    
                    // Center icons and adjust padding
                    const navItems = sidebar.querySelectorAll('.nav-item');
                    navItems.forEach(item => {
                        item.style.justifyContent = 'center';
                        item.style.paddingLeft = '1rem';
                        item.style.paddingRight = '1rem';
                    });
                }

                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', toggleSidebar);
                }
                if (sidebarClose) sidebarClose.addEventListener('click', closeSidebarMobile);
                if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebarMobile);

                // Handle window resize
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 1024) {
                        // Desktop mode
                        sidebar.classList.remove('-translate-x-full');
                        if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
                        
                        // Apply desktop collapsed state if collapsed
                        if (isCollapsed) {
                            sidebar.style.width = '80px';
                            sidebar.setAttribute('data-collapsed', 'true');
                            
                            const navTexts = sidebar.querySelectorAll('.nav-text');
                            navTexts.forEach(text => {
                                text.style.display = 'none';
                            });
                            
                            const navItems = sidebar.querySelectorAll('.nav-item');
                            navItems.forEach(item => {
                                item.style.justifyContent = 'center';
                                item.style.paddingLeft = '1rem';
                                item.style.paddingRight = '1rem';
                            });
                        }
                    } else {
                        // Mobile mode
                        // Reset desktop styles
                        sidebar.style.width = '';
                        sidebar.setAttribute('data-collapsed', 'false');
                        
                        const navTexts = sidebar.querySelectorAll('.nav-text');
                        navTexts.forEach(text => {
                            text.style.display = 'block';
                        });
                        
                        const navItems = sidebar.querySelectorAll('.nav-item');
                        navItems.forEach(item => {
                            item.style.justifyContent = '';
                            item.style.paddingLeft = '';
                            item.style.paddingRight = '';
                        });
                        
                        if (isCollapsed) {
                            sidebar.classList.add('-translate-x-full');
                        }
                    }
                });
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
