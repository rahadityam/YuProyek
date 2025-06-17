<!-- Tambahkan ini di bagian <head> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs" defer></script>

<!-- Sidebar -->
<aside class="fixed top-16 left-0 w-64 h-[calc(100vh-4rem)] bg-white border-r shadow-sm z-50">
    <div class="p-4 space-y-5 overflow-y-auto h-full">
     <!-- Dashboard -->
        <div class="relative">
            @if (request()->routeIs('ceo.dashboard'))
                <div class="absolute left-0 top-0 h-full w-1 bg-blue-600 rounded-r"></div>
            @endif
            <a href="{{ route('ceo.dashboard') }}"
                class="flex items-center gap-3 p-2 pl-3 rounded-md transition
                {{ request()->routeIs('ceo.dashboard') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <i class="bi bi-speedometer2 text-lg"></i>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
        </div>

        <!-- List Projects -->
        <div class="relative">
            @if (request()->routeIs('ceo.project_list'))
                <div class="absolute left-0 top-0 h-full w-1 bg-blue-600 rounded-r"></div>
            @endif
            <a href="{{ route('ceo.project_list') }}"
                class="flex items-center gap-3 p-2 pl-3 rounded-md transition
                {{ request()->routeIs('ceo.project_list') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <i class="bi bi-folder text-lg"></i>
                <span class="text-sm font-medium">List Projects</span>
            </a>
        </div>

        <!-- List Users -->
        <div class="relative">
            @if (request()->routeIs('ceo.user_list'))
                <div class="absolute left-0 top-0 h-full w-1 bg-blue-600 rounded-r"></div>
            @endif
            <a href="{{ route('ceo.user_list') }}"
                class="flex items-center gap-3 p-2 pl-3 rounded-md transition
                {{ request()->routeIs('ceo.user_list') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <i class="bi bi-people text-lg"></i>
                <span class="text-sm font-medium">List Users</span>
            </a>
        </div>
    </div>
    </div>
</aside>