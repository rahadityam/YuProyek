@php
    // Ambil ID proyek dari URL jika ada
    $projectId = $projectId ?? request()->segment(2);
    $currentProjectId = request()->segment(2) ?? null;
    $isInProject = request()->is('projects/*'); // Cek apakah sedang dalam proyek
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
        <h2 class="text-lg font-bold mb-4 text-[#6D6D6D]">Proyek: {{ $currentProjectId }}</h2>

        <!-- Kelola Proyek Dropdown -->
        <div x-data>
            <div class="flex items-center p-2 rounded-md cursor-pointer group" @click="toggleManageProjectDropdown">
                <i data-lucide="folder" class="w-5 h-5 group-hover:text-[#5F65DB]"></i>
                <span class="ml-3 group-hover:text-[#5F65DB]">Kelola Proyek</span>
                <i data-lucide="chevron-right" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openManageProjectDropdown }"></i>
            </div>

            <div class="ml-6 overflow-hidden transition-all duration-300"
                x-bind:style="openManageProjectDropdown ? 'max-height: 200px' : 'max-height: 0px'">
                <!-- Dashboard -->
                <a href="/projects/{{ $currentProjectId }}/dashboard" class="flex items-center p-2 rounded-md cursor-pointer group"
                    :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/dashboard') }">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 group-hover:text-[#5F65DB]"></i>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Dashboard</span>
                </a>

                <!-- Papan Kanban -->
                <a href="/projects/{{ $currentProjectId }}/kanban" class="flex items-center p-2 rounded-md cursor-pointer group"
                    :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/kanban') }">
                    <i data-lucide="kanban" class="w-5 h-5 group-hover:text-[#5F65DB]"></i>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Papan Kanban</span>
                </a>
            </div>
        </div>

        <!-- Keuangan -->
        <div x-data>
            <div class="flex items-center p-2 rounded-md cursor-pointer group" @click="toggleDropdown">
                <i data-lucide="folder" class="w-5 h-5 group-hover:text-[#5F65DB]"></i>
                <span class="ml-3 group-hover:text-[#5F65DB]">Keuangan</span>
                <i data-lucide="chevron-right" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openDropdown }"></i>
            </div>

            <div class="ml-6 overflow-hidden transition-all duration-300"
                x-bind:style="openDropdown ? 'max-height: 100px' : 'max-height: 0px'">
                <a href="/projects/{{ $currentProjectId }}/penggajian" class="flex items-center p-2 rounded-md cursor-pointer group"
                    :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/penggajian') }">
                    <i data-lucide="dollar-sign" class="w-5 h-5 group-hover:text-[#5F65DB]"></i>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Penggajian</span>
                </a>

                <a href="/projects/{{ $currentProjectId }}/pembayaran" class="flex items-center p-2 rounded-md cursor-pointer group"
                    :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/pembayaran') }">
                    <i data-lucide="credit-card" class="w-5 h-5 group-hover:text-[#5F65DB]"></i>
                    <span class="ml-3 group-hover:text-[#5F65DB]">Pembayaran</span>
                </a>
            </div>
        </div>

        <!-- Pengaturan -->
        <a href="/projects/{{ $currentProjectId }}/pengaturan" class="flex items-center p-2 rounded-md cursor-pointer group"
            :class="{ 'bg-[#E2EBFD] border-l-4 border-[#5F65DB] text-[#5F65DB]': activeMenu.includes('/projects/{{ $currentProjectId }}/pengaturan') }">
            <i data-lucide="settings" class="w-5 h-5 group-hover:text-[#5F65DB]"></i>
            <span class="ml-3 group-hover:text-[#5F65DB]">Pengaturan</span>
        </a>
    </div>
@endif