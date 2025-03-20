<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Panggil Tailwind CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
</head>
<body class="font-sans antialiased h-full">
<div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Fixed Navbar -->
    <div class="sticky top-0 z-50">
        @include('layouts.navigation')
    </div>
    <div class="flex flex-1">
        <!-- Sidebar hanya muncul jika ada project_id di URL dan bukan halaman detail project atau apply -->
        @if(request()->segment(1) === 'projects' && is_numeric(request()->segment(2)) && request()->segment(3) && request()->segment(3) !== 'apply')
            <div x-data="{ isCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
                 x-init="$watch('isCollapsed', value => localStorage.setItem('sidebarCollapsed', value))"
                 class="flex relative">
                <!-- Sidebar -->
                <div class="bg-white text-[#6D6D6D] overflow-y-auto overflow-x-hidden transition-all duration-300"
                     :class="{ 'w-64': !isCollapsed, 'w-16': isCollapsed }"
                     style="height: calc(100vh - 4rem);">
                    <div class="pt-4 pb-4" 
                         :class="{ 'px-4': !isCollapsed, 'px-2': isCollapsed }">
                        @include('components.sidebar', ['projectId' => request()->segment(2), 'isCollapsed' => 'isCollapsed'])
                    </div>
                </div>
                <!-- Tombol Toggle -->
                <div class="absolute flex items-center justify-center w-6 h-6 bg-white rounded-full cursor-pointer shadow-lg -right-3 mt-4 z-10"
                     @click="isCollapsed = !isCollapsed">
                    <svg x-show="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    <svg x-show="isCollapsed" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </div>
            </div>
        @endif
        <!-- Scrollable Main Content -->
        <div class="flex-1 overflow-y-auto" style="height: calc(100vh - 4rem);">
            <main>
                {{ $slot }}
            </main>
        </div>
    </div>
</div>
<!-- Panggil Alpine.js -->
<script src="{{ asset('js/bundle.js') }}" defer></script>
@stack('scripts')
</body>
</html>