<!-- Tambahkan ini di bagian <head> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs" defer></script>

<!-- Sidebar -->
<aside class="fixed top-16 left-0 w-64 h-[calc(100vh-4rem)] bg-white border-r shadow-sm z-50">
    <div class="p-4 space-y-5 overflow-y-auto h-full">
        <!-- Dashboard -->
        <div class="relative">
            @if (request()->routeIs('admin.dashboard'))
                <div class="absolute left-0 top-0 h-full w-1 bg-blue-600 rounded-r"></div>
            @endif
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 p-2 pl-3 rounded-md transition
                      {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <i class="bi bi-speedometer2 text-lg"></i>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
        </div>

        <!-- Manage Projects -->
        <div x-data="{ open: {{ request()->routeIs('admin.projects.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full p-2 rounded-md transition
                           text-gray-600 hover:bg-gray-100">
                <div class="flex items-center gap-3">
                    <i class="bi bi-folder text-lg"></i>
                    <span class="text-sm font-medium">Manage Projects</span>
                </div>
                <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>
            <div x-show="open" x-cloak class="ml-7 mt-2 space-y-1 relative">
                @if (request()->routeIs('admin.projects.index'))
                    <div class="absolute left-0 top-0 h-full w-1 bg-blue-600 rounded-r"></div>
                @endif
                <a href="{{ route('admin.projects.index') }}"
                   class="flex items-center gap-2 p-2 rounded-md transition
                          {{ request()->routeIs('admin.projects.index') ? 'bg-blue-50 text-blue-600 font-medium pl-3' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="bi bi-list-task text-sm"></i>
                    <span class="text-sm">Project</span>
                </a>
            </div>
        </div>

        <!-- Manage Users -->
        <div x-data="{ open: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full p-2 rounded-md transition
                           text-gray-600 hover:bg-gray-100">
                <div class="flex items-center gap-3">
                    <i class="bi bi-person text-lg"></i>
                    <span class="text-sm font-medium">Manage Users</span>
                </div>
                <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>
            <div x-show="open" x-cloak class="ml-7 mt-2 space-y-1 relative">
                @if (request()->routeIs('admin.users.index'))
                    <div class="absolute left-0 top-0 h-full w-1 bg-blue-600 rounded-r"></div>
                @endif
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2 p-2 rounded-md transition
                          {{ request()->routeIs('admin.users.index') ? 'bg-blue-50 text-blue-600 font-medium pl-3' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="bi bi-people text-sm"></i>
                    <span class="text-sm">Users</span>
                </a>
            </div>
        </div>

    </div>
</aside>
