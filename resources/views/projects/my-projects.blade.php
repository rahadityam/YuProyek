<x-app-layout>
    <div x-data="{ showLoading: true }" class="container mx-auto px-4 py-8">
        @if($isOwner)
            <h1 class="text-3xl font-bold mb-6">Proyek yang Saya Kelola</h1>
        @else
            <h1 class="text-3xl font-bold mb-6">Proyek yang Saya Ikuti</h1>
        @endif

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
             class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($projects as $project)
                <div class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition-shadow duration-300"
                     x-data="{ showDetails: false }">
                    <h2 class="text-xl font-semibold mb-2">{{ $project->name }}</h2>
                    <p class="text-gray-600 mb-4">{{ Str::limit($project->description, 100) }}</p>

                    <!-- Toggle Details Button -->
                    <button @click="showDetails = !showDetails" class="text-blue-500 mb-2 flex items-center">
                        <span x-text="showDetails ? 'Sembunyikan Detail' : 'Tampilkan Detail'"></span>
                        <svg x-bind:class="showDetails ? 'transform rotate-180' : ''" class="w-4 h-4 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Informasi Proyek (Collapsible) -->
                    <div x-show="showDetails" x-collapse x-cloak class="space-y-2 border-t pt-2 mt-2">
                        @if(!$isOwner)
                            <p><span class="font-medium">Pemilik:</span> {{ $project->owner->name }}</p>
                        @endif
                        <p><span class="font-medium">Tanggal Mulai:</span> {{ $project->start_date }}</p>
                        <p><span class="font-medium">Tanggal Selesai:</span> {{ $project->end_date }}</p>
                        <p><span class="font-medium">Anggaran:</span> Rp {{ number_format($project->budget, 2) }}</p>
                        <p>
                            <span class="font-medium">Status:</span> 
                            <span x-data="{ status: '{{ $project->status }}' }" class="px-2 py-1 text-sm rounded"
                                x-bind:class="{
                                    'bg-green-200 text-green-800': status === 'open',
                                    'bg-yellow-200 text-yellow-800': status === 'in_progress',
                                    'bg-blue-200 text-blue-800': status === 'completed',
                                    'bg-gray-200 text-gray-800': !['open', 'in_progress', 'completed'].includes(status)
                                }">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </p>
                    </div>

                    <!-- Tombol Detail -->
                    <a href="{{ route('projects.show', $project->id) }}" 
                       class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-200">
                        Lihat Detail
                    </a>
                </div>
            @endforeach
        </div>
        
        <!-- Empty State Message -->
        <div x-show="!showLoading && {{ count($projects) }} === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            @if($isOwner)
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum memiliki proyek</h3>
                <p class="mt-1 text-sm text-gray-500">Anda belum membuat proyek apapun</p>
                <div class="mt-6">
                    <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Buat Proyek Baru
                    </a>
                </div>
            @else
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum mengikuti proyek</h3>
                <p class="mt-1 text-sm text-gray-500">Anda belum mengikuti proyek apapun</p>
            @endif
        </div>
    </div>
</x-app-layout>