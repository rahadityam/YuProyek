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
    <div class="h-full flex flex-col" x-data="{
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
        <h2 class="text-lg font-bold mb-4 text-[#6D6D6D]">{{ $projectName }}</h2>

        <!-- Kelola Proyek Dropdown -->
        <div x-data>
            <div class="flex items-center p-2 rounded-md cursor-pointer group" @click="toggleManageProjectDropdown">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="ml-3 group-hover:text-[#5F65DB]">Kelola Proyek</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openManageProjectDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>

            <div class="ml-6 overflow-hidden transition-all duration-300"
                x-bind:style="openManageProjectDropdown ? 'max-height: 200px' : 'max-height: 0px'">
                <!-- Dashboard -->
                <a href="/projects/{{ $currentProjectId }}/dashboard" class="flex items-center p-2 rounded-md cursor-pointer group"
                    :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/dashboard') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Dashboard</span>
                </a>

                <!-- Papan Kanban -->
                <a href="/projects/{{ $currentProjectId }}/kanban" class="flex items-center p-2 rounded-md cursor-pointer group"
                    :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/kanban') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="5" height="18" rx="1"></rect>
                        <rect x="9" y="7" width="5" height="14" rx="1"></rect>
                        <rect x="15" y="5" width="5" height="16" rx="1"></rect>
                    </svg>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Papan Kanban</span>
                </a>
            </div>
        </div>

        <!-- Keuangan -->
        <div x-data>
            <div class="flex items-center p-2 rounded-md cursor-pointer group" @click="toggleDropdown">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="ml-3 group-hover:text-[#5F65DB]">Keuangan</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>

            <div class="ml-6 overflow-hidden transition-all duration-300"
                x-bind:style="openDropdown ? 'max-height: 100px' : 'max-height: 0px'">
                <a href="/projects/{{ $currentProjectId }}/penggajian" class="flex items-center p-2 rounded-md cursor-pointer group"
                :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/penggajian') || activeMenu.includes('/projects/{{ $currentProjectId }}/wage-standards') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Penggajian</span>
                </a>

                <a href="/projects/{{ $currentProjectId }}/pembayaran" class="flex items-center p-2 rounded-md cursor-pointer group"
                    :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/pembayaran') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Pembayaran</span>
                </a>
            </div>
        </div>

        <!-- Aktivitas -->
        <a href="/projects/{{ $currentProjectId }}/aktivitas" class="flex items-center p-2 rounded-md cursor-pointer group"
            :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/aktivitas') }">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span class="ml-3 group-hover:text-[#5F65DB]">Aktivitas</span>
        </a>

        <!-- Pengaturan -->
        <a href="/projects/{{ $currentProjectId }}/pengaturan" class="flex items-center p-2 rounded-md cursor-pointer group"
            :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/pengaturan') }">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            <span class="ml-3 group-hover:text-[#5F65DB]">Pengaturan</span>
        </a>
    </div>
@endif