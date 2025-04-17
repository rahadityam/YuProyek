@php
    // Ambil ID proyek dari URL jika ada
    $projectId = $projectId ?? request()->segment(2);
    $currentProjectId = request()->segment(2) ?? null;
    $isInProject = request()->is('projects/*/*'); // Cek apakah sedang dalam proyek
    
    // Ambil nama proyek dari database jika ID ada
    $projectName = '';
    if ($currentProjectId) {
        $project = \App\Models\Project::find($currentProjectId);
        $projectName = $project ? $project->name : $currentProjectId;
    }
@endphp

@if ($isInProject && $currentProjectId)
    <div class="h-full flex flex-col overflow-x-hidden" x-data="{
        activeMenu: window.location.pathname,
        openDropdown: localStorage.getItem('openDropdown') === 'true',
        openManageProjectDropdown: localStorage.getItem('openManageProjectDropdown') === 'true',
        setActiveMenu() {
            this.activeMenu = window.location.pathname;
        },
        toggleDropdown() {
            this.openDropdown = !this.openDropdown;
            localStorage.setItem('openDropdown', this.openDropdown);
        },
        toggleManageProjectDropdown() {
            this.openManageProjectDropdown = !this.openManageProjectDropdown;
            localStorage.setItem('openManageProjectDropdown', this.openManageProjectDropdown);
        }
    }" x-init="setActiveMenu">
        <h2 class="text-lg font-bold ml-4 mb-4 text-[#6D6D6D]" x-show="!{{ $isCollapsed }}">{{ $projectName }}</h2>

        <!-- Kelola Proyek Dropdown -->
        <div x-data class="flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
            <div class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full" @click="toggleManageProjectDropdown">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Kelola Proyek</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openManageProjectDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="!{{ $isCollapsed }}">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>

            <div class="overflow-hidden transition-all duration-300 w-full"
        x-bind:class="{ 'ml-6': !{{ $isCollapsed }}, 'ml-0': {{ $isCollapsed }} }"
        x-bind:style="openManageProjectDropdown ? 'max-height: 200px' : 'max-height: 0px'">
        <!-- Dashboard -->
        <a href="/projects/{{ $currentProjectId }}/dashboard" class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
            :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/dashboard') }"
            :style="activeMenu.includes('/projects/{{ $currentProjectId }}/dashboard') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
            <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Dashboard</span>
        </a>

        <!-- Papan Kanban -->
        <a href="/projects/{{ $currentProjectId }}/kanban" class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
            :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/kanban') }"
            :style="activeMenu.includes('/projects/{{ $currentProjectId }}/kanban') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
            <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="5" height="18" rx="1"></rect>
                    <rect x="9" y="7" width="5" height="14" rx="1"></rect>
                    <rect x="15" y="5" width="5" height="16" rx="1"></rect>
                </svg>
            </div>
            <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Papan Kanban</span>
        </a>

        <a href="/projects/{{ $currentProjectId }}/pembayaran" class="flex items-center pr-l pl-2 pb-1 pt-1  rounded-md cursor-pointer group w-full"
                    :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/pembayaran') }"
                    :style="activeMenu.includes('/projects/{{ $currentProjectId }}/pembayaran') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Pembayaran</span>
                </a>
    </div>
</div>

        <!-- Keuangan -->
        <!-- <div x-data class="flex flex-col items-center mt-2" :class="{ 'items-start': !{{ $isCollapsed }} }">
            <div class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full" @click="toggleDropdown">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Keuangan</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="!{{ $isCollapsed }}">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>

            <div class="overflow-hidden transition-all duration-300 w-full"
                x-bind:class="{ 'ml-6': !{{ $isCollapsed }}, 'ml-0': {{ $isCollapsed }} }"
                x-bind:style="openDropdown ? 'max-height: 100px' : 'max-height: 0px'">
                <a href="#" class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
                    :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/penggajian') || activeMenu.includes('/projects/{{ $currentProjectId }}/wage-standards') }"
                    :style="(activeMenu.includes('/projects/{{ $currentProjectId }}/penggajian') || activeMenu.includes('/projects/{{ $currentProjectId }}/wage-standards')) ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Penggajian</span>
                </a>

                <a href="/projects/{{ $currentProjectId }}/pembayaran" class="flex items-center pr-l pl-2 pb-1 pt-1  rounded-md cursor-pointer group w-full"
                    :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/pembayaran') }"
                    :style="activeMenu.includes('/projects/{{ $currentProjectId }}/pembayaran') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Pembayaran</span>
                </a>
            </div>
        </div> -->

        <!-- Team Members -->
        <div class="mt-2 flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
            <a href="{{ route('projects.team', $projectId) }}" class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
                :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/team') }"
                :style="activeMenu.includes('/projects/{{ $currentProjectId }}/team') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Team Members</span>
            </a>
        </div>

        <!-- Aktivitas -->
        <div class="mt-2 flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
    <a href="/projects/{{ $currentProjectId }}/activity" class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
        :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/aktivitas') }"
        :style="activeMenu.includes('/projects/{{ $currentProjectId }}/aktivitas') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
        <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                <path d="M12 8v4l2 2"></path>
            </svg>
        </div>
        <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Aktivitas</span>
    </a>
</div>

<div class="mt-2 flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
    <a href="/projects/{{ $currentProjectId }}/settings" class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
        :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/settings') }"
        :style="activeMenu.includes('/projects/{{ $currentProjectId }}/settings') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
        <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1-2.83 0 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
        </div>
        <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Pengaturan</span>
    </a>
</div>
    </div>
@endif