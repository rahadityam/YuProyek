@php
    // Ambil ID proyek dari URL jika ada
    $projectId = $projectId ?? request()->segment(2);
    $currentProjectId = request()->segment(2) ?? null;
    $isInProject = request()->is('projects/*/*'); // Cek apakah sedang dalam proyek

    // Ambil nama proyek dari database jika ID ada
    $projectName = '';
    if ($currentProjectId) {
        $project = \App\Models\Project::find($currentProjectId);
        $projectName = $project ? $project->name : 'Project ' . $currentProjectId;
    }
@endphp

@if ($isInProject && $currentProjectId)
    <div class="h-full flex flex-col overflow-x-hidden" x-data="{
        activeMenu: window.location.pathname,
        openManageProjectDropdown: localStorage.getItem('openManageProjectDropdown_{{ $currentProjectId }}') === 'true', // Unique key per project
        openFinanceDropdown: localStorage.getItem('openFinanceDropdown_{{ $currentProjectId }}') === 'true', // Unique key per project
        setActiveMenu() {
            this.activeMenu = window.location.pathname;
        },
        toggleManageProjectDropdown() {
            this.openManageProjectDropdown = !this.openManageProjectDropdown;
            localStorage.setItem('openManageProjectDropdown_{{ $currentProjectId }}', this.openManageProjectDropdown);
        },
        toggleFinanceDropdown() { // New function for finance dropdown
            this.openFinanceDropdown = !this.openFinanceDropdown;
            localStorage.setItem('openFinanceDropdown_{{ $currentProjectId }}', this.openFinanceDropdown);
        }
    }" x-init="setActiveMenu">
        <h2 class="text-lg font-bold ml-4 mb-4 text-[#6D6D6D]" x-show="!{{ $isCollapsed }}">{{ $projectName }}</h2>

        <!-- Kelola Proyek Dropdown -->
        <div x-data class="flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
            <div class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full" @click="toggleManageProjectDropdown">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Kelola Proyek</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openManageProjectDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="!{{ $isCollapsed }}"> <polyline points="9 18 15 12 9 6"></polyline> </svg>
            </div>

            <div class="overflow-hidden transition-all duration-300 w-full"
                x-bind:class="{ 'ml-6': !{{ $isCollapsed }}, 'ml-0': {{ $isCollapsed }} }"
                x-bind:style="openManageProjectDropdown ? 'max-height: 200px' : 'max-height: 0px'">
                {{-- Dashboard --}}
                <a href="{{ route('projects.dashboard', $currentProjectId) }}" class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
                    :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/dashboard') }"
                    :style="activeMenu.includes('/projects/{{ $currentProjectId }}/dashboard') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect> </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Dashboard</span>
                </a>
                 {{-- Papan Kanban --}}
                <a href="{{ route('projects.kanban', $currentProjectId) }}" class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
                    :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/kanban') }"
                    :style="activeMenu.includes('/projects/{{ $currentProjectId }}/kanban') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="3" y="3" width="5" height="18" rx="1"></rect> <rect x="9" y="7" width="5" height="14" rx="1"></rect> <rect x="15" y="5" width="5" height="16" rx="1"></rect> </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Papan Kanban</span>
                </a>
                
                <a href="{{ route('projects.payroll.calculate', $currentProjectId) }}" class="flex items-center pr-l pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
                     :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/payroll/calculate') }"
                     :style="activeMenu.includes('/projects/{{ $currentProjectId }}/payroll/calculate') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8" :class="{ 'ml-0': {{ $isCollapsed }}, 'ml-0': !{{ $isCollapsed }} }">
                        {{-- Icon Calculator --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:text-[#5F65DB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M12 17h.01M15 17h.01M9 10h.01M12 10h.01M15 10h.01M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-2M6 7V5a2 2 0 012-2h8a2 2 0 012 2v2M16 7a2 2 0 012 2v1" /> </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Gaji</span>
                </a>
            </div>
        </div>
<!-- Separator horizontal sebelum Team Members -->
<div class="w-full px-2 mt-2" :class="{ 'pl-2': !{{ $isCollapsed }}, 'pl-0': {{ $isCollapsed }} }">
    <hr class="border-t border-gray-300">
</div>

        {{-- Menu Lainnya (Team, Aktivitas, Pengaturan) Tetap Sama --}}
        <!-- Team Members -->
        <div class="mt-2 flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
            <a href="{{ route('projects.team', $projectId) }}" class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
                :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/team') }"
                :style="activeMenu.includes('/projects/{{ $currentProjectId }}/team') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Team Members</span>
            </a>
        </div>

        <!-- Aktivitas -->
        <div class="mt-2 flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
            <a href="{{ route('projects.activity', $currentProjectId) }}" class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
                :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/activity') }" {{-- Ubah dari aktivitas ke activity --}}
                :style="activeMenu.includes('/projects/{{ $currentProjectId }}/activity') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Aktivitas</span>
            </a>
        </div>

        <!-- Pengaturan -->
        <div class="mt-2 flex flex-col items-center" :class="{ 'items-start': !{{ $isCollapsed }} }">
            <a href="{{ route('projects.pengaturan', $currentProjectId) }}" class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
                :class="{ 'bg-[#E2EBFD]': activeMenu.includes('/projects/{{ $currentProjectId }}/settings') || activeMenu.includes('/projects/{{ $currentProjectId }}/wage-standards') }"
                :style="(activeMenu.includes('/projects/{{ $currentProjectId }}/settings') || activeMenu.includes('/projects/{{ $currentProjectId }}/wage-standards')) ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V15a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] transition-opacity duration-200" x-bind:class="{ 'hidden': {{ $isCollapsed }} }">Pengaturan</span>
            </a>
        </div>
    </div>
@endif