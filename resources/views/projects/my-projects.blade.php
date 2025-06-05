<x-app-layout>
    <div x-data="myProjectsPage({
            initialProjects: {{ Js::from($projects->items()) }},
            projectsPagination: {{ Js::from($projects->links('vendor.pagination.tailwind')->toHtml()) }},
            isOwner: {{ Js::from($isOwner) }},
            projectStoreUrl: '{{ route('projects.store') }}',
            csrfToken: '{{ csrf_token() }}',
            // Data untuk filter dan sort
            filterSearch: '{{ request('search', '') }}',
            filterStatus: '{{ request('status', 'all') }}',
            filterSort: '{{ request('sort', 'created_at') }}'
        })"
         x-init="initPage" class="container mx-auto px-4 py-8">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800" x-text="isOwner ? 'Proyek yang Saya Kelola' : 'Proyek yang Saya Ikuti'"></h1>
            
            <template x-if="isOwner">
                <button @click="openCreateProjectModal" 
                   type="button"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Buat Proyek Baru
                    </span>
                </button>
            </template>
        </div>

        {{-- Flash Message untuk sukses buat proyek dari modal --}}
        <div x-show="createProjectFlash.message" x-cloak
             class="mb-4 border px-4 py-3 rounded relative" role="alert"
             :class="createProjectFlash.success ? 'bg-green-50 border-green-300 text-green-700' : 'bg-red-50 border-red-300 text-red-700'"
             x-transition>
            <strong class="font-semibold" x-text="createProjectFlash.success ? 'Sukses!' : 'Error!'"></strong>
            <span class="block sm:inline ml-1" x-text="createProjectFlash.message"></span>
            <button @click="createProjectFlash.message = ''" type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-xl font-semibold leading-none hover:text-opacity-75">×</button>
        </div>

        <!-- Filter dan Search Section (DIMODIFIKASI) -->
        <form method="GET" action="{{ route('projects.my-projects') }}" class="mb-8" x-ref="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Search Input -->
                <div class="md:col-span-1">
                    <label for="search_filter" class="block text-sm font-medium text-gray-700 mb-1">Cari Proyek</label>
                    <input type="text" name="search" id="search_filter" placeholder="Nama atau deskripsi..." 
                           x-model="filterSearch"
                           @input.debounce.500ms="submitFilterForm" {{-- Submit setelah 500ms tidak ada input --}}
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status_filter" 
                            x-model="filterStatus" 
                            @change="submitFilterForm"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Semua Status</option>
                        <option value="open">Buka</option>
                        <option value="in_progress">Sedang Berjalan</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <!-- Sorting -->
                <div>
                    <label for="sort_filter" class="block text-sm font-medium text-gray-700 mb-1">Urutkan Berdasarkan</label>
                    <select name="sort" id="sort_filter" 
                            x-model="filterSort"
                            @change="submitFilterForm"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="created_at">Terbaru</option>
                        <option value="name">Nama Proyek</option>
                        <option value="start_date">Tanggal Mulai</option>
                    </select>
                </div>
                
                {{-- Tombol Terapkan Filter Dihapus --}}
                {{-- Tombol Reset Filter bisa tetap ada --}}
                <div class="md:col-span-3 mt-2">
                    <a href="{{ route('projects.my-projects') }}" class="text-sm text-gray-600 hover:text-blue-600 hover:underline">
                        Reset Semua Filter
                    </a>
                </div>
            </div>
        </form>

        <!-- Loading State -->
        <div x-show="showLoading" x-init="setTimeout(() => showLoading = false, 500)" class="flex justify-center py-12">
            <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Daftar Proyek (Anggaran Dihilangkan) -->
        <div x-show="!showLoading" x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform scale-95" 
             x-transition:enter-end="opacity-100 transform scale-100"
             class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($projects as $project)
                <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <!-- Header dengan nama proyek dan status -->
                    <div class="relative p-5 bg-gradient-to-r from-blue-500 to-blue-600">
                        <h2 class="text-xl font-bold text-white mb-1 truncate">{{ $project->name }}</h2>
                        <span x-data="{ status: '{{ $project->status }}' }" 
                             class="absolute top-5 right-5 px-3 py-1 text-sm font-semibold rounded-full"
                             x-bind:class="{
                                 'bg-green-100 text-green-800': status === 'open',
                                 'bg-yellow-100 text-yellow-800': status === 'in_progress',
                                 'bg-blue-100 text-blue-800': status === 'completed',
                                 'bg-gray-100 text-gray-800': !['open', 'in_progress', 'completed'].includes(status)
                             }">
                             {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </div>
                    
                    <!-- Deskripsi -->
                    <div class="p-5 border-b border-gray-100">
                        <p class="text-gray-600 h-16 overflow-hidden text-ellipsis">{{ Str::limit($project->description, 120) }}</p>
                    </div>

                    <!-- Informasi Proyek dengan ikon -->
                    <div class="p-5 space-y-3">
                        <!-- Pemilik (hanya untuk worker) -->
                        @if(!$isOwner)
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <span class="text-sm text-gray-500">Pemilik</span>
                                    <p class="font-medium">{{ $project->owner->name }}</p>
                                </div>
                            </div>
                        @endif
                        
                        {{-- Anggaran DIHILANGKAN --}}
                        {{-- <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="text-sm text-gray-500">Anggaran</span>
                                <p class="font-medium">Rp {{ number_format($project->budget, 0, ',', '.') }}</p>
                            </div>
                        </div> --}}
                        
                        <!-- Tanggal -->
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="text-sm text-gray-500">Periode</span>
                                <p class="font-medium">{{ $project->start_date ? date('d M Y', strtotime($project->start_date)) : 'N/A' }} - {{ $project->end_date ? date('d M Y', strtotime($project->end_date)) : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer dengan tombol -->
                    <div class="px-5 py-4 bg-gray-50 flex justify-end">
                        <a href="{{ route('projects.dashboard', $project->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 shadow">
                            <span>Lihat Detail</span>
                        </a>
                    </div>
                </div>
            @empty
                <!-- Empty State Message -->
                <div class="col-span-full bg-white rounded-xl shadow-md p-8 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    @if($isOwner)
                        <h3 class="mt-3 text-lg font-medium text-gray-900">Belum memiliki proyek</h3>
                        <p class="mt-2 text-gray-500">Anda belum membuat proyek apapun</p>
                        <div class="mt-6">
                            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-5 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 transition duration-300">
                                <svg class="-ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Buat Proyek Baru
                            </a>
                        </div>
                    @else
                        <h3 class="mt-3 text-lg font-medium text-gray-900">Belum mengikuti proyek</h3>
                        <p class="mt-2 text-gray-500">Anda belum mengikuti proyek apapun</p>
                    @endif
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
            {{ $projects->links('vendor.pagination.tailwind') }}
        </div>

        {{-- MODAL BUAT PROYEK BARU --}}
        <div x-show="isCreateProjectModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="create-project-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isCreateProjectModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeCreateProjectModal" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                <div x-show="isCreateProjectModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <form @submit.prevent="submitCreateProjectForm">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start w-full">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-xl leading-6 font-semibold text-gray-900 mb-6 border-b pb-3" id="create-project-modal-title">
                                        Buat Proyek Baru
                                    </h3>
                                    
                                    {{-- Form Error Global --}}
                                    <template x-if="createFormErrors.general">
                                        <div class="mb-4 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded relative" role="alert">
                                            <p class="font-semibold">Error!</p>
                                            <span class="block sm:inline ml-1" x-text="createFormErrors.general"></span>
                                        </div>
                                    </template>

                                    <div class="space-y-5">
                                        {{-- Nama Proyek --}}
                                        <div>
                                            <label for="cp-name" class="block text-sm font-medium text-gray-700 mb-1">Nama Proyek <span class="text-red-500">*</span></label>
                                            <input type="text" id="cp-name" x-model="newProject.name" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" :class="{'border-red-500': createFormErrors.name}">
                                            <template x-if="createFormErrors.name"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.name[0]"></p></template>
                                        </div>

                                        {{-- Deskripsi Proyek --}}
                                        <div>
                                            <label for="cp-description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Proyek</label>
                                            <textarea id="cp-description" x-model="newProject.description" rows="3" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" :class="{'border-red-500': createFormErrors.description}"></textarea>
                                            <template x-if="createFormErrors.description"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.description[0]"></p></template>
                                        </div>

                                        {{-- Tanggal (Mulai dan Akhir) & Anggaran --}}
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="cp-start_date" class="block text-sm font-medium text-gray-700 mb-1">Tgl Mulai <span class="text-red-500">*</span></label>
                                                <input type="date" id="cp-start_date" x-model="newProject.start_date" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" :class="{'border-red-500': createFormErrors.start_date}">
                                                <template x-if="createFormErrors.start_date"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.start_date[0]"></p></template>
                                            </div>
                                            <div>
                                                <label for="cp-end_date" class="block text-sm font-medium text-gray-700 mb-1">Tgl Akhir <span class="text-red-500">*</span></label>
                                                <input type="date" id="cp-end_date" x-model="newProject.end_date" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" :class="{'border-red-500': createFormErrors.end_date}">
                                                <template x-if="createFormErrors.end_date"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.end_date[0]"></p></template>
                                            </div>
                                            <div>
                                                <label for="cp-budget" class="block text-sm font-medium text-gray-700 mb-1">Anggaran (Rp) <span class="text-red-500">*</span></label>
                                                <input type="number" id="cp-budget" x-model.number="newProject.budget" required min="0" step="any" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" :class="{'border-red-500': createFormErrors.budget}">
                                                <template x-if="createFormErrors.budget"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.budget[0]"></p></template>
                                            </div>
                                        </div>

                                        {{-- WIP Limit & Bobot WSM --}}
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="cp-wip_limits" class="block text-sm font-medium text-gray-700 mb-1">WIP Limit Kanban</label>
                                                <input type="number" id="cp-wip_limits" x-model.number="newProject.wip_limits" min="1" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" placeholder="Opsional" :class="{'border-red-500': createFormErrors.wip_limits}">
                                                <template x-if="createFormErrors.wip_limits"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.wip_limits[0]"></p></template>
                                            </div>
                                            <div>
                                                <label for="cp-difficulty_weight" class="block text-sm font-medium text-gray-700 mb-1">Bobot Kesulitan (%)</label>
                                                <input type="number" id="cp-difficulty_weight" x-model.number="newProject.difficulty_weight" min="0" max="100" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" placeholder="Default: 65" :class="{'border-red-500': createFormErrors.difficulty_weight}">
                                                <template x-if="createFormErrors.difficulty_weight"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.difficulty_weight[0]"></p></template>
                                            </div>
                                            <div>
                                                <label for="cp-priority_weight" class="block text-sm font-medium text-gray-700 mb-1">Bobot Prioritas (%)</label>
                                                <input type="number" id="cp-priority_weight" x-model.number="newProject.priority_weight" min="0" max="100" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" placeholder="Default: 35" :class="{'border-red-500': createFormErrors.priority_weight}">
                                                <template x-if="createFormErrors.priority_weight"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.priority_weight[0]"></p></template>
                                            </div>
                                            <template x-if="createFormErrors.weights_total"><p class="col-span-3 mt-1 text-xs text-red-500" x-text="createFormErrors.weights_total"></p></template>
                                        </div>
                                        
                                        {{-- Pengaturan Pembayaran --}}
                                        <fieldset class="border border-gray-300 p-4 rounded-md">
                                        <legend class="text-md font-medium text-gray-800 px-2">Pengaturan Pembayaran</legend>
                                        <div class="mt-2 space-y-3">
                                            <label for="cp-payment_calculation_type" class="block text-sm font-medium text-gray-700 mb-1">Metode Kalkulasi Utama <span class="text-red-500">*</span></label>
                                            <select id="cp-payment_calculation_type" x-model="newProject.payment_calculation_type" required 
                                                    @change="if (newProject.payment_calculation_type !== 'termin') newProject.payment_terms = []" {{-- Kosongkan termin jika bukan 'termin' --}}
                                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm" 
                                                    :class="{'border-red-500': createFormErrors.payment_calculation_type}">
                                                {{-- <option value="task">Per Task (Default)</option> --}} {{-- HILANGKAN INI --}}
                                                <option value="termin">Per Termin/Periode</option>
                                                <option value="full">Jumlah Tetap (Tanpa Task)</option>
                                            </select>
                                            <template x-if="createFormErrors.payment_calculation_type"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.payment_calculation_type[0]"></p></template>
                                            <template x-if="createFormErrors.payment_terms && newProject.payment_calculation_type === 'termin'"><p class="mt-1 text-xs text-red-500" x-text="createFormErrors.payment_terms[0]"></p></template>

                                                {{-- Bagian Termin (jika dipilih 'termin') --}}
                                                <div x-show="newProject.payment_calculation_type === 'termin'" x-transition.opacity class="mt-4 space-y-3">
                                                    <h4 class="text-sm font-semibold text-gray-700">Definisi Termin:</h4>
                                                    <template x-for="(term, index) in newProject.payment_terms" :key="index">
                                                        <div class="grid grid-cols-1 sm:grid-cols-9 gap-x-3 gap-y-2 items-end border-b pb-2.5 border-gray-200">
                                                            <div class="sm:col-span-3">
                                                                <label :for="`cp_term_name_${index}`" class="block text-xs font-medium text-gray-600">Nama Termin</label>
                                                                <input type="text" :id="`cp_term_name_${index}`" x-model="term.name" required placeholder="Contoh: Termin 1" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" :class="{'border-red-500': createFormErrors[`payment_terms.${index}.name`]}">
                                                                <template x-if="createFormErrors[`payment_terms.${index}.name`]"><p class="mt-0.5 text-xs text-red-500" x-text="createFormErrors[`payment_terms.${index}.name`][0]"></p></template>
                                                            </div>
                                                            <div class="sm:col-span-2">
                                                                <label :for="`cp_term_start_${index}`" class="block text-xs font-medium text-gray-600">Tgl Mulai</label>
                                                                <input type="date" :id="`cp_term_start_${index}`" x-model="term.start_date" required class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" :class="{'border-red-500': createFormErrors[`payment_terms.${index}.start_date`]}">
                                                                <template x-if="createFormErrors[`payment_terms.${index}.start_date`]"><p class="mt-0.5 text-xs text-red-500" x-text="createFormErrors[`payment_terms.${index}.start_date`][0]"></p></template>
                                                            </div>
                                                            <div class="sm:col-span-2">
                                                                <label :for="`cp_term_end_${index}`" class="block text-xs font-medium text-gray-600">Tgl Akhir</label>
                                                                <input type="date" :id="`cp_term_end_${index}`" x-model="term.end_date" required class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" :class="{'border-red-500': createFormErrors[`payment_terms.${index}.end_date`]}">
                                                                <template x-if="createFormErrors[`payment_terms.${index}.end_date`]"><p class="mt-0.5 text-xs text-red-500" x-text="createFormErrors[`payment_terms.${index}.end_date`][0]"></p></template>
                                                            </div>
                                                            <div class="sm:col-span-2 flex items-end">
                                                                <button type="button" @click="removePaymentTerm(index)" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 rounded-md focus:outline-none" title="Hapus Termin">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <button type="button" @click="addPaymentTerm" class="mt-2 inline-flex items-center px-2.5 py-1 border border-dashed border-gray-400 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                                        <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                                        Tambah Termin
                                                    </button>
                                                </div>
                                            </div>
                                        </fieldset>

                                        {{-- Posisi Pekerja --}}
                                        <fieldset class="border border-gray-300 p-4 rounded-md">
                                            <legend class="text-md font-medium text-gray-800 px-2">Kebutuhan Posisi Pekerja</legend>
                                            <div class="mt-2 space-y-3">
                                                <template x-for="(position, index) in newProject.positions" :key="index">
                                                    <div class="grid grid-cols-1 sm:grid-cols-9 gap-x-3 gap-y-2 items-end border-b pb-2.5 border-gray-200">
                                                        <div class="sm:col-span-4">
                                                            <label :for="`cp_pos_name_${index}`" class="block text-xs font-medium text-gray-600">Nama Posisi</label>
                                                            <input type="text" :id="`cp_pos_name_${index}`" x-model="position.name" required placeholder="Contoh: Programmer" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" :class="{'border-red-500': createFormErrors[`positions.${index}.name`]}">
                                                            <template x-if="createFormErrors[`positions.${index}.name`]"><p class="mt-0.5 text-xs text-red-500" x-text="createFormErrors[`positions.${index}.name`][0]"></p></template>
                                                        </div>
                                                        <div class="sm:col-span-3">
                                                            <label :for="`cp_pos_count_${index}`" class="block text-xs font-medium text-gray-600">Jumlah Dibutuhkan</label>
                                                            <input type="number" :id="`cp_pos_count_${index}`" x-model.number="position.count" required min="1" class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" :class="{'border-red-500': createFormErrors[`positions.${index}.count`]}">
                                                            <template x-if="createFormErrors[`positions.${index}.count`]"><p class="mt-0.5 text-xs text-red-500" x-text="createFormErrors[`positions.${index}.count`][0]"></p></template>
                                                        </div>
                                                        <div class="sm:col-span-2 flex items-end">
                                                            <button type="button" @click="removeProjectPosition(index)" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 rounded-md focus:outline-none" title="Hapus Posisi">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                <button type="button" @click="addProjectPosition" class="mt-2 inline-flex items-center px-2.5 py-1 border border-dashed border-gray-400 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                                    <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                                    Tambah Posisi
                                                </button>
                                            </div>
                                        </fieldset>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button type="submit" :disabled="isSubmittingCreateProject" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-show="!isSubmittingCreateProject">Buat Proyek</span>
                                <span x-show="isSubmittingCreateProject" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Memproses...
                                </span>
                            </button>
                            <button type="button" @click="closeCreateProjectModal" :disabled="isSubmittingCreateProject" class="mt-3 inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    function myProjectsPage(config) {
        return {
            projects: config.initialProjects,
            projectsPagination: config.projectsPagination,
            isOwner: config.isOwner,
            projectStoreUrl: config.projectStoreUrl,
            csrfToken: config.csrfToken,
            
            showLoading: true,
            
            filterSearch: config.filterSearch,
            filterStatus: config.filterStatus,
            filterSort: config.filterSort,

            // State untuk Modal Buat Proyek
            isCreateProjectModalOpen: false,
            isSubmittingCreateProject: false,
            newProject: {
                name: '',
                description: '',
                start_date: new Date().toISOString().slice(0,10),
                end_date: new Date(new Date().setDate(new Date().getDate() + 7)).toISOString().slice(0,10),
                budget: 1000000,
                status: 'open',
                wip_limits: null,
                difficulty_weight: 65,
                priority_weight: 35,
                payment_calculation_type: 'termin', // UBAH DEFAULT JIKA PERLU (misal 'termin' atau 'full')
                payment_terms: [],
                positions: []
            },
            createFormErrors: {},
            createProjectFlash: { message: '', success: false },

            initPage() {
                setTimeout(() => { this.showLoading = false; }, 300); // Kurangi delay sedikit

                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('project_created_success')) {
                    this.showCreateProjectFlash(urlParams.get('project_created_success'), true);
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
                // Tidak perlu watch lagi, karena @input dan @change akan memanggil submitFilterForm
            },

            submitFilterForm() {
                // Fungsi ini akan dipanggil oleh @input atau @change pada elemen form
                // Kita bisa menggunakan $refs untuk mendapatkan form dan men-submitnya
                this.$refs.filterForm.submit();
            },

            showCreateProjectFlash(message, success) {
                this.createProjectFlash.message = message;
                this.createProjectFlash.success = success;
                setTimeout(() => {
                    this.createProjectFlash.message = '';
                }, 5000);
            },

            openCreateProjectModal() {
                this.newProject = { // Reset form
                    name: '', description: '',
                    start_date: new Date().toISOString().slice(0,10),
                    end_date: new Date(new Date().setDate(new Date().getDate() + 7)).toISOString().slice(0,10),
                    budget: 1000000, status: 'open',
                    wip_limits: null, difficulty_weight: 65, priority_weight: 35,
                    payment_calculation_type: 'termin', // Sesuaikan default ini jika perlu
                    payment_terms: [], positions: []
                };
                if (this.newProject.payment_calculation_type === 'termin' && this.newProject.payment_terms.length === 0) {
                    this.addPaymentTerm(); // Otomatis tambah 1 termin jika defaultnya termin
                }
                this.createFormErrors = {};
                this.isCreateProjectModalOpen = true;
            },
            closeCreateProjectModal() {
                this.isCreateProjectModalOpen = false;
            },

            addPaymentTerm() {
                let defaultStartDate = this.newProject.start_date || new Date().toISOString().slice(0,10);
                let defaultEndDate = '';
                if (this.newProject.payment_terms.length > 0) {
                    const lastTerm = this.newProject.payment_terms[this.newProject.payment_terms.length - 1];
                    if (lastTerm.end_date) {
                        try {
                            const nextDay = new Date(lastTerm.end_date);
                            nextDay.setDate(nextDay.getDate() + 1);
                            defaultStartDate = nextDay.toISOString().slice(0,10);
                        } catch (e) {}
                    }
                }
                try {
                    const endPlusWeek = new Date(defaultStartDate);
                    endPlusWeek.setDate(endPlusWeek.getDate() + 7);
                    defaultEndDate = endPlusWeek.toISOString().slice(0,10);
                } catch (e) {}

                this.newProject.payment_terms.push({
                    name: `Termin ${this.newProject.payment_terms.length + 1}`,
                    start_date: defaultStartDate,
                    end_date: defaultEndDate,
                });
            },
            removePaymentTerm(index) {
                this.newProject.payment_terms.splice(index, 1);
            },

            addProjectPosition() {
                this.newProject.positions.push({ name: '', count: 1 });
            },
            removeProjectPosition(index) {
                this.newProject.positions.splice(index, 1);
            },

            submitCreateProjectForm() {
                this.isSubmittingCreateProject = true;
                this.createFormErrors = {};

                if ((this.newProject.difficulty_weight || 0) + (this.newProject.priority_weight || 0) > 100) {
                    this.createFormErrors.weights_total = 'Total bobot Kesulitan dan Prioritas tidak boleh lebih dari 100%.';
                    this.isSubmittingCreateProject = false;
                    return;
                }
                // Modifikasi validasi client-side untuk termin
                if (this.newProject.payment_calculation_type === 'termin' && this.newProject.payment_terms.length === 0) {
                    // Pesan ini sekarang akan ditampilkan oleh template x-if createFormErrors.payment_terms
                    this.createFormErrors.payment_terms = ['Minimal harus ada satu termin jika metode pembayaran adalah termin.'];
                    this.isSubmittingCreateProject = false;
                    return;
                }

                // Saat mengirim, jika bukan 'termin', pastikan payment_terms kosong
                const payload = { ...this.newProject };
                if (payload.payment_calculation_type !== 'termin') {
                    payload.payment_terms = [];
                }

                fetch(this.projectStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                .then(({ status, ok, body }) => {
                    if (ok && body.success) {
                        this.closeCreateProjectModal();
                        this.showCreateProjectFlash(body.message || 'Proyek berhasil dibuat!', true);
                        // Idealnya, kita refresh daftar proyek. Bisa dengan reload halaman atau fetch ulang data.
                        // Untuk simple, kita bisa redirect atau user diminta refresh.
                        // Jika ingin AJAX update list, perlu lebih banyak logika.
                        // Contoh: window.location.reload();
                        // Atau, jika controller mengembalikan data proyek baru:
                        if (body.project) {
                            // this.projects.unshift(body.project); // Tambah ke awal list (perlu penyesuaian jika ada paginasi)
                             window.location.href = "{{ route('projects.my-projects') }}?project_created_success=" + encodeURIComponent(body.message);
                        } else {
                            window.location.reload(); // Fallback reload
                        }
                    } else if (status === 422) { // Validation error
                        this.createFormErrors = body.errors || {};
                        this.showCreateProjectFlash(body.message || 'Input tidak valid. Mohon periksa kembali.', false);
                    } else { // Other server errors
                        this.createFormErrors.general = body.message || 'Terjadi kesalahan saat membuat proyek.';
                        this.showCreateProjectFlash(body.message || 'Terjadi kesalahan server.', false);
                    }
                })
                .catch(err => {
                    console.error('Create project error:', err);
                    this.createFormErrors.general = 'Tidak dapat menghubungi server. Periksa koneksi Anda.';
                    this.showCreateProjectFlash('Request error.', false);
                })
                .finally(() => {
                    this.isSubmittingCreateProject = false;
                });
            }
        }
    }
    </script>
</x-app-layout>