<div> {{-- Root Element Wajib --}}
    {{-- Container padding (bisa dihapus jika sudah ada di layout) --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Tambah Standar Upah - {{ $project->name }}</h2>
        </div>

        <!-- Back Button (Gunakan wire:navigate jika halaman index juga Livewire) -->
        <div class="mb-6">
            <a href="{{ route('projects.wage-standards.index', $project) }}" wire:navigate class="btn-secondary">
                <svg class="btn-icon-left"> <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /> </svg>
                Kembali ke Daftar Standar Upah
            </a>
        </div>

        <!-- Form (Target ke Controller Biasa) -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                {{-- Form action menargetkan route controller store --}}
                <form action="{{ route('projects.wage-standards.store', $project) }}" method="POST">
                    @csrf {{-- Tetap perlu CSRF token --}}

                    <!-- Job Category -->
                    <div class="mb-4">
                        <label for="job_category" class="label-text">Kategori Pekerjaan</label>
                        {{-- Nama input sesuai dengan yang diharapkan controller --}}
                        <input type="text" name="job_category" id="job_category" value="{{ old('job_category') }}" required
                               class="input-field w-full @error('job_category') input-error @enderror">
                        {{-- Menampilkan error dari validasi controller --}}
                        @error('job_category')
                            <p class="input-error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Task Price -->
                    <div class="mb-4">
                        <label for="task_price" class="label-text">Harga Dasar Task</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="task_price" id="task_price" value="{{ old('task_price') }}" required
                                   class="input-field w-full pl-10 pr-12 @error('task_price') input-error @enderror"
                                   placeholder="0" step="1" min="0"> {{-- Step 1 untuk bilangan bulat --}}
                        </div>
                        @error('task_price')
                            <p class="input-error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">
                            Simpan Standar Upah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     {{-- Tambahkan CSS utility jika belum ada global --}}
     @push('styles')
        <style>
            .input-field { @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm; }
            .input-error { @apply border-red-500 ring-red-500; }
            .input-error-msg { @apply mt-1 text-xs text-red-600; }
            .label-text { @apply block text-sm font-medium text-gray-700 mb-1; }
            .btn-primary { @apply inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50; }
            .btn-secondary { @apply inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
            .btn-icon-left { @apply -ml-1 mr-2 h-5 w-5 text-gray-500; } /* Sesuaikan warna ikon jika perlu */
        </style>
     @endpush

</div>