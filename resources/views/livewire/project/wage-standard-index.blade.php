<div> {{-- Root Element --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Master Standar Upah - {{ $project->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">Kelola harga dasar task untuk setiap kategori pekerjaan.</p>
            </div>
            {{-- Tombol Kembali & Tambah --}}
            <div class="flex flex-shrink-0 space-x-2">
                {{-- Link ke halaman pengaturan utama (sudah Livewire) --}}
                 <a href="{{ route('projects.pengaturan', $project) }}" wire:navigate class="btn-secondary">
                     <svg class="btn-icon-left"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                     Kembali ke Pengaturan
                 </a>
                 {{-- Link ke halaman tambah standar upah (Livewire) --}}
                <a href="{{ route('projects.wage-standards.create', $project) }}" wire:navigate class="btn-primary">
                    <svg class="btn-icon-left"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                    Tambah Standar
                </a>
            </div>
        </div>

        {{-- Pesan Sukses/Error dari Aksi Livewire --}}
        @if (session()->has('success_message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert" x-transition>
                <span class="block sm:inline">{{ session('success_message') }}</span>
            </div>
        @endif
        @if (session()->has('error_message'))
             <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                  class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert" x-transition>
                 <span class="block sm:inline">{{ session('error_message') }}</span>
             </div>
        @endif

        {{-- Search Bar (Opsional) --}}
        <div class="mb-4">
            <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Cari kategori pekerjaan..." class="input-field w-full md:w-1/3">
        </div>

        <!-- Wage Standards List Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="overflow-x-auto">
                 {{-- Loading Indicator --}}
                 <div wire:loading.flex class="py-4 px-6 text-gray-500 italic"> Loading standards... </div>

                <table class="min-w-full divide-y divide-gray-200" wire:loading.remove>
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- Sorting bisa ditambahkan jika perlu --}}
                            <th scope="col" class="th-cell"> Kategori Pekerjaan </th>
                            <th scope="col" class="th-cell"> Harga Dasar Task (Rp) </th>
                            <th scope="col" class="th-cell text-right"> Aksi </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($wageStandards as $wageStandard)
                            <tr wire:key="ws-{{ $wageStandard->id }}">
                                <td class="td-cell font-medium text-gray-900"> {{ $wageStandard->job_category }} </td>
                                <td class="td-cell text-gray-700"> {{ number_format($wageStandard->task_price, 0, ',', '.') }} </td>
                                <td class="td-cell text-right font-medium">
                                    <div class="flex justify-end space-x-3">
                                        {{-- Link ke halaman edit (Livewire) --}}
                                        <a href="{{ route('projects.wage-standards.edit', [$project, $wageStandard]) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <svg class="h-5 w-5">...</svg> {{-- Icon Edit --}}
                                        </a>
                                        {{-- Tombol hapus (panggil method Livewire) --}}
                                        <button type="button"
                                                wire:click="deleteWageStandard({{ $wageStandard->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus standar '{{ $wageStandard->job_category }}'? Tindakan ini tidak dapat dibatalkan."
                                                class="text-red-600 hover:text-red-900"
                                                title="Hapus">
                                                <svg class="h-5 w-5">...</svg> {{-- Icon Hapus --}}
                                                {{-- Indikator loading spesifik untuk tombol delete --}}
                                                <span wire:loading wire:target="deleteWageStandard({{ $wageStandard->id }})" class="animate-spin inline-block w-3 h-3 border-t-2 border-red-600 rounded-full ml-1"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                                    Belum ada standar upah yang ditambahkan untuk proyek ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination Livewire --}}
            @if ($wageStandards->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $wageStandards->links() }}
            </div>
            @endif
        </div>
    </div>

     {{-- Tambahkan CSS utility jika belum ada global --}}
     @push('styles')
        <style>
            .input-field { @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm; }
            .btn-primary { @apply inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50; }
            .btn-secondary { @apply inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
            .btn-icon-left { @apply -ml-1 mr-2 h-5 w-5; }
            .th-cell { @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider; }
            .td-cell { @apply px-6 py-4 whitespace-nowrap text-sm; }
        </style>
     @endpush
</div>