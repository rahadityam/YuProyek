<x-app-layout>
    <div x-data="{ showLoading: true }" class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            @if($isOwner)
                <h1 class="text-3xl font-bold text-gray-800">Proyek yang Saya Kelola</h1>
            @else
                <h1 class="text-3xl font-bold text-gray-800">Proyek yang Saya Ikuti</h1>
            @endif
            
            @if($isOwner)
                <a href="{{ route('projects.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg shadow-md transition duration-300 ease-in-out"
                   x-on:mouseenter="$el.classList.add('shadow-lg', 'transform', 'scale-105')" 
                   x-on:mouseleave="$el.classList.remove('shadow-lg', 'transform', 'scale-105')">
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Buat Proyek Baru
                    </span>
                </a>
            @endif
        </div>

        <!-- Filter dan Search Section -->
        <form method="GET" action="{{ route('projects.my-projects') }}" class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div class="col-span-2">
                    <input type="text" name="search" placeholder="Cari proyek..." 
                           value="{{ request('search') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">Semua Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Buka</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <!-- Sorting -->
                <select name="sort" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Terbaru</option>
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama Proyek</option>
                    <option value="start_date" {{ request('sort') == 'start_date' ? 'selected' : '' }}>Tanggal Mulai</option>
                </select>

                <!-- Submit Button -->
                <div class="col-span-4 flex items-center space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('projects.my-projects') }}" class="text-gray-600 hover:underline">
                        Reset Filter
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

        <!-- Daftar Proyek -->
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
                        <p class="text-gray-600">{{ Str::limit($project->description, 100) }}</p>
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
                        
                        <!-- Anggaran -->
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="text-sm text-gray-500">Anggaran</span>
                                <p class="font-medium">Rp {{ number_format($project->budget, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        
                        <!-- Tanggal -->
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="text-sm text-gray-500">Periode</span>
                                <p class="font-medium">{{ date('d M Y', strtotime($project->start_date)) }} - {{ date('d M Y', strtotime($project->end_date)) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer dengan tombol -->
                    <div class="px-5 py-4 bg-gray-50 flex justify-end">
                        <a href="{{ route('projects.dashboard', $project->id) }}" 
                        onclick="@if($project->status === 'blocked') event.preventDefault();  @endif"
                         class="inline-flex items-center px-4 py-2 
                        {{ $project->status === 'blocked' ? 'bg-blue-600 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700' }} 
                        text-white rounded-lg transition duration-200 shadow">
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
    </div>
</x-app-layout>