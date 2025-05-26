{{-- resources/views/components/sidebar.blade.php --}}
@php
    // $projectId sudah di-pass dari layout dan sudah divalidasi di sana.
    $currentProjectId = $projectId; // Seharusnya ini sudah ID yang valid
    $projectObject = $currentProjectId ? \App\Models\Project::find($currentProjectId) : null;
    $projectName = $projectObject ? $projectObject->name : ($currentProjectId ? 'Project Loading...' : 'No Project Selected'); // Default jika project belum ter-load
    $isInProjectValid = (bool) $projectObject; // Cast ke boolean untuk @if

    // Kunci localStorage tetap menggunakan ID proyek yang valid
    // $validProjectIdKey digunakan untuk memastikan nama handler unik jika diperlukan
    $validProjectIdKey = is_numeric($currentProjectId) ? $currentProjectId : 'unknown_project';
    $activeMenuStoreKey = "activeMenu_turbo_{$validProjectIdKey}";
    $dropdownManageProjectStoreKey = "openManageProjectDropdown_turbo_{$validProjectIdKey}";
    $dropdownFinanceStoreKey = "openFinanceDropdown_turbo_{$validProjectIdKey}";
@endphp

{{-- Hanya render jika kita berada dalam konteks proyek yang valid --}}
@if ($isInProjectValid)
    <div class="h-full flex flex-col overflow-x-hidden"
         x-data="{
            // State isCollapsed diambil dari $store.sidebar.isCollapsed
            // State lokal untuk menu dropdown dan menu aktif
            activeMenu: localStorage.getItem('{{ $activeMenuStoreKey }}') || window.location.pathname,
            openManageProjectDropdown: localStorage.getItem('{{ $dropdownManageProjectStoreKey }}') === 'true',
            openFinanceDropdown: localStorage.getItem('{{ $dropdownFinanceStoreKey }}') === 'true',

            updateActiveMenuState() {
                this.activeMenu = window.location.pathname;
                localStorage.setItem('{{ $activeMenuStoreKey }}', this.activeMenu);
                // console.log('Sidebar activeMenu updated:', this.activeMenu);
            },
            isActive(pathSegment) {
                return this.activeMenu.includes(pathSegment);
            },
            toggleManageProjectDropdown() {
                this.openManageProjectDropdown = !this.openManageProjectDropdown;
                localStorage.setItem('{{ $dropdownManageProjectStoreKey }}', this.openManageProjectDropdown);
            },
            toggleFinanceDropdown() {
                this.openFinanceDropdown = !this.openFinanceDropdown;
                localStorage.setItem('{{ $dropdownFinanceStoreKey }}', this.openFinanceDropdown);
            }
         }"
         x-init="
            updateActiveMenuState(); // Panggil saat init

            // Definisikan handler unik untuk event turbo:load
            const turboLoadHandlerForThisSidebar = () => {
                if (typeof Alpine !== 'undefined' && $el && Alpine.$data($el)) {
                     Alpine.$data($el).updateActiveMenuState();
                } else {
                    // Hapus listener jika elemen tidak lagi ada (seharusnya tidak terjadi dengan data-turbo-permanent)
                    document.removeEventListener('turbo:load', turboLoadHandlerForThisSidebar);
                }
            };

            // Pastikan listener hanya ditambahkan sekali untuk elemen ini
            // dengan menggunakan nama yang unik di window object jika perlu
            const handlerKey = 'turboSidebarLoadHandler_{{ $validProjectIdKey }}_instance';
            if (window[handlerKey]) {
                document.removeEventListener('turbo:load', window[handlerKey]);
            }
            window[handlerKey] = turboLoadHandlerForThisSidebar;
            document.addEventListener('turbo:load', window[handlerKey]);

            // Opsional: Cleanup listener saat elemen Alpine dihancurkan
            // $el.addEventListener('alpine:destroy', () => {
            //    if (window[handlerKey]) {
            //        document.removeEventListener('turbo:load', window[handlerKey]);
            //    }
            // });
         ">

        {{-- Judul Proyek, dikontrol oleh $store.sidebar.isCollapsed --}}
        <h2 class="text-lg font-bold ml-4 mb-4 text-[#6D6D6D] truncate"
            x-show="!$store.sidebar.isCollapsed"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            >{{ $projectName }}</h2>

        {{-- Kelola Proyek Dropdown --}}
        <div class="flex flex-col" :class="{ 'items-center': $store.sidebar.isCollapsed, 'items-start': !$store.sidebar.isCollapsed }">
            <div class="flex items-center pr-2 pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full" {{-- Adjusted padding for consistency --}}
                 @click="toggleManageProjectDropdown">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                      :style="$store.sidebar.isCollapsed ? 'max-width: 0; opacity: 0; margin-left:0;' : 'max-width: 200px; opacity: 1;'"
                      >Kelola Proyek</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]"
                     :class="{ 'rotate-90': openManageProjectDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     x-show="!$store.sidebar.isCollapsed"
                     x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     > <polyline points="9 18 15 12 9 6"></polyline> </svg>
            </div>

            <div class="overflow-hidden transition-all duration-300 w-full"
                :class="{ 'pl-0': $store.sidebar.isCollapsed, 'pl-6': !$store.sidebar.isCollapsed }" {{-- Adjusted padding left --}}
                x-bind:style="openManageProjectDropdown ? ($store.sidebar.isCollapsed ? 'max-height: 200px;' : 'max-height: 200px;') : 'max-height: 0px;'">
                {{-- Dashboard --}}
                <a href="{{ route('projects.dashboard', $currentProjectId) }}"
                   class="flex items-center pr-2 pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
                   :class="{ 'bg-[#E2EBFD]': isActive('/projects/{{ $currentProjectId }}/dashboard'), 'justify-center': $store.sidebar.isCollapsed }"
                   :style="isActive('/projects/{{ $currentProjectId }}/dashboard') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''"
                   title="Dashboard">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect> </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                          :style="$store.sidebar.isCollapsed ? 'max-width: 0; opacity: 0; margin-left:0;' : 'max-width: 200px; opacity: 1;'"
                          >Dashboard</span>
                </a>
                 {{-- Papan Kanban --}}
                <a href="{{ route('projects.kanban', $currentProjectId) }}"
                   class="flex items-center pr-2 pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
                   :class="{ 'bg-[#E2EBFD]': isActive('/projects/{{ $currentProjectId }}/kanban'), 'justify-center': $store.sidebar.isCollapsed }"
                   :style="isActive('/projects/{{ $currentProjectId }}/kanban') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''"
                   title="Papan Kanban">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="3" y="3" width="5" height="18" rx="1"></rect> <rect x="9" y="7" width="5" height="14" rx="1"></rect> <rect x="15" y="5" width="5" height="16" rx="1"></rect> </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                          :style="$store.sidebar.isCollapsed ? 'max-width: 0; opacity: 0; margin-left:0;' : 'max-width: 200px; opacity: 1;'"
                          >Papan Kanban</span>
                </a>
                {{-- Gaji --}}
                <a href="{{ route('projects.payroll.calculate', $currentProjectId) }}"
                   class="flex items-center pr-2 pl-2 pb-1 pt-1 rounded-md cursor-pointer group w-full"
                   :class="{ 'bg-[#E2EBFD]': isActive('/projects/{{ $currentProjectId }}/payroll') || isActive('/projects/{{ $currentProjectId }}/payslips'), 'justify-center': $store.sidebar.isCollapsed }"
                   :style="isActive('/projects/{{ $currentProjectId }}/payroll') || isActive('/projects/{{ $currentProjectId }}/payslips') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''"
                   title="Gaji">
                    <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:text-[#5F65DB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M12 17h.01M15 17h.01M9 10h.01M12 10h.01M15 10h.01M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-2M6 7V5a2 2 0 012-2h8a2 2 0 012 2v2M16 7a2 2 0 012 2v1" /> </svg>
                    </div>
                    <span class="ml-3 group-hover:text-[#5F65DB] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                          :style="$store.sidebar.isCollapsed ? 'max-width: 0; opacity: 0; margin-left:0;' : 'max-width: 200px; opacity: 1;'"
                          >Gaji</span>
                </a>
            </div>
        </div>

        {{-- Separator --}}
        <div class="w-full px-2 mt-2" :class="{ 'pl-2': !$store.sidebar.isCollapsed, 'pl-0': $store.sidebar.isCollapsed }">
            <hr class="border-t border-gray-300">
        </div>

        {{-- Menu Lainnya --}}
        {{-- Team Members --}}
        <div class="mt-2 flex flex-col" :class="{ 'items-center': $store.sidebar.isCollapsed, 'items-start': !$store.sidebar.isCollapsed }">
            <a href="{{ route('projects.team', $currentProjectId) }}"
               class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
               :class="{ 'bg-[#E2EBFD]': isActive('/projects/{{ $currentProjectId }}/team'), 'justify-center': $store.sidebar.isCollapsed }"
               :style="isActive('/projects/{{ $currentProjectId }}/team') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''"
               title="Team Members">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                      :style="$store.sidebar.isCollapsed ? 'max-width: 0; opacity: 0; margin-left:0;' : 'max-width: 200px; opacity: 1;'"
                      >Team Members</span>
            </a>
        </div>
        {{-- Aktivitas --}}
        <div class="mt-2 flex flex-col" :class="{ 'items-center': $store.sidebar.isCollapsed, 'items-start': !$store.sidebar.isCollapsed }">
            <a href="{{ route('projects.activity', $currentProjectId) }}"
               class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
               :class="{ 'bg-[#E2EBFD]': isActive('/projects/{{ $currentProjectId }}/activity'), 'justify-center': $store.sidebar.isCollapsed }"
               :style="isActive('/projects/{{ $currentProjectId }}/activity') ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''"
               title="Aktivitas">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                      :style="$store.sidebar.isCollapsed ? 'max-width: 0; opacity: 0; margin-left:0;' : 'max-width: 200px; opacity: 1;'"
                      >Aktivitas</span>
            </a>
        </div>
        {{-- Pengaturan --}}
        <div class="mt-2 flex flex-col" :class="{ 'items-center': $store.sidebar.isCollapsed, 'items-start': !$store.sidebar.isCollapsed }">
            @can('viewSettings', $projectObject)
            <a href="{{ route('projects.pengaturan', $currentProjectId) }}"
               class="flex items-center pl-2 pr-2 pt-1 pb-1 rounded-md cursor-pointer group w-full"
               :class="{ 'bg-[#E2EBFD]': isActive('/projects/{{ $currentProjectId }}/settings') || isActive('/projects/{{ $currentProjectId }}/pengaturan') || isActive('/projects/{{ $currentProjectId }}/wage-standards'), 'justify-center': $store.sidebar.isCollapsed }"
               :style="(isActive('/projects/{{ $currentProjectId }}/settings') || isActive('/projects/{{ $currentProjectId }}/pengaturan') || isActive('/projects/{{ $currentProjectId }}/wage-standards')) ? 'box-shadow: inset 4px 0 0 0 #5F65DB; color: #5F65DB;' : ''"
               title="Pengaturan">
                <div class="flex-shrink-0 flex justify-center items-center w-8 h-8">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:text-[#5F65DB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06-.06a1.65 1.65 0 0 0-.33 1.82V15a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path> </svg>
                </div>
                <span class="ml-3 group-hover:text-[#5F65DB] whitespace-nowrap overflow-hidden transition-all duration-200 ease-in-out"
                      :style="$store.sidebar.isCollapsed ? 'max-width: 0; opacity: 0; margin-left:0;' : 'max-width: 200px; opacity: 1;'"
                      >Pengaturan</span>
            </a>
            @endcan
        </div>
    </div>
@else
    <p class="text-center text-sm text-gray-500 px-4 py-2"
       x-show="!$store.sidebar.isCollapsed"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       >
        @if(!$currentProjectId)
            Pilih proyek untuk melihat menu.
        @elseif(!$projectObject)
            Proyek tidak valid.
        @endif
    </p>
@endif