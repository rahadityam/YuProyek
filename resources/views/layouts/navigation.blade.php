<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="w-full px-4 sm:px-6 lg:px-8"> <!-- Hapus max-w-7xl dan mx-auto -->
        <div class="flex justify-between h-16 relative">
            <!-- Item di Kiri -->
            <div class="flex items-center"> <!-- Pastikan item di kiri berada di ujung kiri -->
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

               @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'ceo')
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-end h-16">
                    <x-nav-link :href="route('projects.my-projects')" :active="request()->routeIs(['projects.my-projects', 'dashboard'])">
                        {{ __('Home') }}
                    </x-nav-link>
                </div>
            @endif

                <!-- Navigation Links - Positioned to align with bottom border -->
                <!-- <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-end h-16">
                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.index')">
                        {{ __('Eksplor Proyek') }}
                    </x-nav-link>
                </div> -->

                <!-- Navigation Links -->
                <!-- <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-end h-16">
                    <x-nav-link :href="route('projects.my-projects')" :active="request()->routeIs('projects.my-projects')">
                        {{ __('Proyek Saya') }}
                    </x-nav-link>
                </div> -->
            </div>

            <!-- Item di Kanan -->
            <div class="flex items-center"> <!-- Pastikan item di kanan berada di ujung kanan -->
                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <div x-data="notificationBell()" class="relative mr-3">
        <!-- Notification Bell Icon -->
        <button @click="toggle" class="relative p-2 text-gray-500 rounded-full hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Unread Count Badge -->
            <span x-show="unreadCount > 0" x-text="unreadCount"
                  class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
            </span>
        </button>

        <!-- Dropdown Panel -->
        <div x-show="isOpen" @click.away="isOpen = false" x-transition
             class="absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 origin-top-right z-50"
             style="display:none;">
            <div class="flex justify-between items-center px-4 py-2 border-b">
                <h3 class="font-semibold text-gray-800">Notifications</h3>
                <button @click="markAllRead" x-show="unreadCount > 0" class="text-xs text-indigo-600 hover:underline focus:outline-none">Mark all as read</button>
            </div>

            <div x-show="isLoading" class="p-4 text-center text-sm text-gray-500">Loading...</div>

            <div x-show="!isLoading && notifications.length === 0" class="p-4 text-center text-sm text-gray-500">
                You have no notifications.
            </div>

            {{-- resources/views/layouts/navigation.blade.php --}}

<ul x-show="!isLoading && notifications.length > 0" class="max-h-96 overflow-y-auto divide-y divide-gray-100">
    <template x-for="notification in notifications" :key="notification.id">
        <li :class="{ 'bg-indigo-50': !notification.read_at }" class="hover:bg-gray-50">
            {{-- Kita buat link membungkus semuanya --}}
            <a :href="notification.data.action_url || '#'" 
               @click="markAsRead(notification.id)"
               class="block px-4 py-3">
                <p class="text-sm text-gray-700" x-text="notification.data.message"></p>

                {{-- Aksi khusus untuk Undangan Proyek --}}
                <template x-if="notification.type.includes('UserInvitedToProjectNotification') && !notification.read_at">
                    <div class="mt-2 flex space-x-2">
                        <form :action="notification.data.action_accept_url" method="POST" @click.stop class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                               class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700">
                                Accept
                            </button>
                        </form>
                        <form :action="notification.data.action_decline_url" method="POST" @click.stop class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                               class="inline-flex items-center px-2.5 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                Decline
                            </button>
                        </form>
                    </div>
                </template>
                <p class="text-xs text-gray-400 mt-1" x-text="timeAgo(notification.created_at)"></p>
            </a>
        </li>
    </template>
   </ul>
</div>
</div>
                <x-dropdown align="right" width="48">
    <x-slot name="trigger">
        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
            <div class="flex flex-col items-start">
                <div class="font-medium">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500">
                    @if(Auth::user()->role === 'worker')
                    Pekerja Proyek
                    @elseif(Auth::user()->role === 'project_owner')
                    Project Manager
                    @elseif(Auth::user()->role === 'admin')
                    Admin
                    @endif
                </div>
            </div>
            <div class="ms-1">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
        </button>
    </x-slot>

                        <x-slot name="content">
                            <!-- @if(Auth::user()->role === 'worker')
                                <x-dropdown-link :href="route('user.switch-role', ['role' => 'project_owner'])">
                                    {{ __('Beralih ke Project Manager') }}
                                </x-dropdown-link>
                            @elseif(Auth::user()->role === 'project_owner')
                                <x-dropdown-link :href="route('user.switch-role', ['role' => 'worker'])">
                                    {{ __('Beralih ke Pekerja Proyek') }}
                                </x-dropdown-link>
                            @endif -->

                            @if(Auth::user()->role !== 'admin')
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
         <!-- Responsive Settings -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

    
    </div>
    
</nav>