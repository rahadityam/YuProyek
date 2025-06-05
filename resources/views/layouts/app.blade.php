<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Add x-cloak style before loading other assets -->
    <style>
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex flex-col h-screen">
        {{-- Navbar --}}
        <div class="sticky top-0 z-50"> {{-- Navbar memiliki z-index tertinggi --}}
            @include('layouts.navigation')
        </div>

        {{-- Konten Utama (Sidebar + Main Area) --}}
        <div class="flex flex-1 overflow-hidden">
            @if(request()->segment(1) === 'projects' && is_numeric(request()->segment(2)) && request()->segment(3) && request()->segment(3) !== 'apply')
                @php
                    $currentProjectIdForLayout = request()->segment(2);
                    $layoutProject = \App\Models\Project::find($currentProjectIdForLayout);
                @endphp

                @if($layoutProject)
                {{-- Kontainer untuk Sidebar dan Tombol Toggle --}}
                {{-- 'relative' agar tombol absolute bisa diposisikan relatif terhadap kontainer ini --}}
                {{-- 'flex-shrink-0' agar kontainer ini tidak menyusut jika konten utama butuh ruang --}}
                <div id="project-sidebar-container-{{ $currentProjectIdForLayout }}"
                     class="relative flex-shrink-0"
                     x-data="{}" {{-- x-data diperlukan untuk x-init dan Alpine reactivity di child --}}
                     x-init="
                        initializeAlpineSidebarStoreForProject('{{ $currentProjectIdForLayout }}');
                        console.log('Layout App: Sidebar container for project {{ $currentProjectIdForLayout }} initialized. Store isCollapsed:', $store.sidebar.isCollapsed);
                     ">

                    {{-- Sidebar Aktual --}}
                    <div id="project-sidebar-{{ $currentProjectIdForLayout }}"
                        data-turbo-permanent
                        class="bg-white text-[#6D6D6D] overflow-y-auto overflow-x-hidden transition-all duration-300 z-30" {{-- z-index lebih rendah dari tombol --}}
                        :class="$store.sidebar.isCollapsed ? 'w-16' : 'w-64'"
                        style="height: calc(100vh - 4rem);"> {{-- Tinggi sidebar dikurangi tinggi navbar --}}
                        <div class="pt-4 pb-4" :class="{ 'px-4': !$store.sidebar.isCollapsed, 'px-2': $store.sidebar.isCollapsed }">
                            @include('components.sidebar', [
                                'projectId' => $currentProjectIdForLayout
                            ])
                        </div>
                    </div>

                    {{-- Tombol Toggle Sidebar --}}
                    <div
                        {{-- x-data dan x-init di sini untuk debugging spesifik tombol jika diperlukan --}}
                        x-data="{}"
                        x-init="console.log('Layout App: Toggle button initialized. Store isCollapsed:', $store.sidebar.isCollapsed)"
                        class="absolute flex items-center justify-center w-6 h-6 bg-white rounded-full cursor-pointer shadow-lg top-4 border border-gray-200 z-40" {{-- z-index di atas sidebar, di bawah navbar --}}
                        {{-- Kalkulasi posisi 'left' berdasarkan state isCollapsed --}}
                        {{-- w-16 -> 4rem; w-64 -> 16rem. Tombol w-6 -> 1.5rem. Setengah lebar tombol = 0.75rem --}}
                        :class="{
                            'left-[calc(4rem-0.75rem)]': $store.sidebar.isCollapsed,
                            'left-[calc(16rem-0.75rem)]': !$store.sidebar.isCollapsed
                        }"
                        style="transition: left 0.3s ease-in-out;" {{-- Transisi untuk pergerakan tombol --}}
                        @click="$store.sidebar.toggle()">
                        <svg x-cloak x-show="!$store.sidebar.isCollapsed" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        <svg x-cloak x-show="$store.sidebar.isCollapsed" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </div>
                </div>
                @endif
            @endif

            {{-- Area Konten Utama yang Bisa di-scroll --}}
            <div class="flex-1 overflow-y-auto" style="height: calc(100vh - 4rem);">
                @if(request()->segment(1) === 'projects' && is_numeric(request()->segment(2)) && request()->segment(3) && request()->segment(3) !== 'apply' && isset($layoutProject) && $layoutProject)
                    <div class="px-4 pt-2">
                        @php
                            $projectName = $layoutProject->name;
                            $breadcrumbs = [
                                ['name' => 'My Projects', 'url' => route('projects.my-projects')],
                                ['name' => $projectName, 'url' => route('projects.dashboard', $layoutProject->id)],
                            ];

                            $pageName = '';
                            $currentSegment3 = request()->segment(3);
                            $currentSegment4 = request()->segment(4);

                            switch($currentSegment3) {
                                case 'dashboard': $pageName = 'Dashboard'; break;
                                case 'kanban': $pageName = 'Kanban Board'; break;
                                case 'payroll':
                                    if ($currentSegment4 === 'calculate') $pageName = 'Payroll Calculation';
                                    break;
                                case 'payslips':
                                    if ($currentSegment4 === 'create') $pageName = 'Create Payslip';
                                    elseif ($currentSegment4 === 'history') $pageName = 'Payslip History';
                                    elseif (is_numeric($currentSegment4)) $pageName = 'Payslip Detail';
                                    break;
                                case 'settings': $pageName = 'Project Settings'; break;
                                case 'team': $pageName = 'Team Members'; break;
                                case 'activity': $pageName = 'Activity Log'; break;
                                case 'wage-standards':
                                    if ($currentSegment4 === 'create') $pageName = 'Create Wage Standard';
                                    elseif ($currentSegment4 === 'edit' && request()->segment(5)) $pageName = 'Edit Wage Standard';
                                    else $pageName = 'Wage Standards';
                                    break;
                            }
                            if($pageName) $breadcrumbs[] = ['name' => $pageName];
                        @endphp
                        <x-breadcrumb :breadcrumbs="$breadcrumbs" />
                    </div>
                @endif

                @if (isset($header))
                    <header class="bg-white shadow-sm">
                        <div class="max-w-full mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="py-2">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>