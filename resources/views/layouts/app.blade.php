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
    <!-- Sidebar hanya muncul jika ada project_id di URL -->
@if(request()->segment(1) === 'projects' && is_numeric(request()->segment(2)))
    <div class="w-64 bg-white text-[#6D6D6D] pl-8 pr-2 pt-4 pb-4 overflow-y-auto" style="height: calc(100vh - 4rem);">
        @include('components.sidebar', ['projectId' => request()->segment(2)])
    </div>
@endif


    <!-- Scrollable Main Content -->
    <div class="flex-1 p-6 overflow-y-auto" style="height: calc(100vh - 4rem);">
        <main>
            {{ $slot }}
        </main>
    </div>
</div>

</div>
<!-- Panggil Alpine.js -->
<script src="{{ asset('js/bundle.js') }}" defer></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons(); // Inisialisasi ikon
</script>
</body>
</html>