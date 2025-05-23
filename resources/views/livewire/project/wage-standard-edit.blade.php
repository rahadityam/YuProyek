<div> {{-- Root Element Wajib --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Edit Standar Upah - {{ $project->name }}</h2>
        </div>

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('projects.wage-standards.index', $project) }}" wire:navigate class="btn-secondary">
                <svg class="btn-icon-left">...</svg> {{-- Icon back --}}
                Kembali ke Daftar Standar Upah
            </a>
        </div>

        <!-- Form (Target ke Controller Biasa) -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                {{-- Form action menargetkan route controller update --}}
                <form action="{{ route('projects.wage-standards.update', [$project, $wageStandard]) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Method spoofing untuk update --}}

                    <!-- Job Category -->
                    <div class="mb-4">
                        <label for="job_category" class="label-text">Kategori Pekerjaan</label>
                        {{-- Isi value dari properti Livewire ATAU old() --}}
                        <input type="text" name="job_category" id="job_category"
                               value="{{ old('job_category', $job_category) }}" required {{-- Ambil dari $job_category public property --}}
                               class="input-field w-full @error('job_category') input-error @enderror">
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
                            {{-- Isi value dari properti Livewire ATAU old() --}}
                            <input type="number" name="task_price" id="task_price"
                                   value="{{ old('task_price', $task_price) }}" required {{-- Ambil dari $task_price public property --}}
                                   class="input-field w-full pl-10 pr-12 @error('task_price') input-error @enderror"
                                   placeholder="0" step="1" min="0"> {{-- step="0.01" jika perlu desimal --}}
                        </div>
                        @error('task_price')
                            <p class="input-error-msg">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">
                            Simpan Perubahan
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
            .btn-icon-left { @apply -ml-1 mr-2 h-5 w-5 text-gray-500; }
        </style>
     @endpush

</div>