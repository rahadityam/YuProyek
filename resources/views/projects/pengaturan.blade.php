<x-app-layout>
    {{-- AlpineJS Data Setup --}}
    <div x-data="projectSettings({
            // Data awal dari Controller
            project: {{ Js::from($project->load('projectPositions')) }}, // LOAD RELASI projectPositions
            initialWageStandards: {{ Js::from($wageStandards) }},
            initialMembers: {{ Js::from($members) }},
            initialPaymentTerms: {{ Js::from($paymentTerms) }},
            initialDifficultyLevels: {{ Js::from($difficultyLevels) }},
            initialPriorityLevels: {{ Js::from($priorityLevels) }},
            projectFiles: {{ Js::from($projectFiles->items()) }},
            projectFilesPagination: {{ Js::from($projectFiles->links('vendor.pagination.tailwind')->toHtml()) }},
            successCriteria: {{ Js::from(session('success_criteria')) }},
            successFinancial: {{ Js::from(session('success_financial')) }},
            successInfo: {{ Js::from(session('success_info')) }},
            isProjectLocked: {{ Js::from($isProjectLocked) }}, 
            
            initialErrors: {{ Js::from($errors->getMessages()) }},
            // URLs & Tokens
            projectUpdateUrl: '{{ route('projects.pengaturan.info.update', $project) }}',
            paymentTypeUpdateUrl: '{{ route('projects.pengaturan.payment.update', $project) }}',
            paymentTermsUpdateUrl: '{{ route('projects.settings.terms.update', $project) }}',
            weightsUpdateUrl: '{{ route('projects.settings.weights.update', $project) }}',
            levelUpdateOrderUrl: '{{ route('projects.settings.levels.order', $project) }}',
            // memberWageUpdateUrlTemplate: '{{ route('projects.settings.team.wage.update', [$project, ':userId']) }}', // Mungkin tidak dipakai
            batchMemberWageUpdateUrl: '{{ route('projects.settings.team.wages.batch-update', $project) }}',
            wageStandardStoreUrlTemplate: '{{ route('projects.wage-standards.store', $project) }}',
            wageStandardUpdateUrlTemplate: '{{ route('projects.wage-standards.update', [$project, ':standardId']) }}',
            wageStandardDestroyUrlTemplate: '{{ route('projects.wage-standards.destroy', [$project, ':standardId']) }}',
            csrfToken: '{{ csrf_token() }}',
            // State Management
            activeTab: '{{ session('active_tab', 'project') }}',
            isLevelModalOpen: false,
            isLevelSubmitting: false,
            memberWageStatus: {},
            isEditingMemberWages: false,
            memberAssignedWages: {},
            originalMemberAssignedWages: {},
            isSubmittingMemberWages: false,
            paymentTerms: [], // Akan diisi di initComponent
            
            // State untuk Project Positions di Tab Info Proyek
            editableProjectPositions: [], // Array untuk menampung posisi saat diedit
            
            isWageStandardModalOpen: false,
            wageStandardModalType: 'add',
            currentWageStandard: { id: null, job_category: '', task_price: '' },
            wageStandardFormErrors: {},
            isWageStandardSubmitting: false,
            flashMessage: '{{ session('success_info') ?: session('success_financial') ?: session('success_criteria') ?: session('success_files') ?: session('success_wage_standards') ?: session('general_error') ?: '' }}',
            isFlashSuccess: {{ session('success_info') || session('success_financial') || session('success_criteria') || session('success_files') || session('success_wage_standards') ? 'true' : 'false' }},
            levelStatusMessage: '',
            isLevelStatusSuccess: true,
            levelModalType: 'difficulty',
            levelToEdit: null,
            currentLevel: { id: null, name: '', value: 1, color: '#cccccc' },
            levelFormErrors: {}
         })"
         x-init="initComponent()"
         class="py-8 px-4 sm:px-6 lg:px-8 bg-gray-100 min-h-screen">

        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Pengaturan Proyek: <span class="font-bold">{{ $project->name }}</span></h1>
        </div>

        <div class="bg-white shadow-md sm:rounded-lg overflow-hidden">
            {{-- Tab Navigation --}}
             <div class="border-b border-gray-200 bg-gray-50">
                 <nav class="-mb-px flex space-x-6 px-4 sm:px-6" aria-label="Tabs">
                     <button @click="activeTab = 'project'" type="button" :class="activeTab === 'project' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                        <svg class="h-4 w-4 mr-1.5" :class="activeTab === 'project' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                         Info Proyek
                     </button>
                     <button @click="activeTab = 'financial'" type="button" :class="activeTab === 'financial' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                          <svg class="h-4 w-4 mr-1.5" :class="activeTab === 'financial' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0c-1.657 0-3-.895-3-2s1.343-2 3-2 3-.895 3-2 1.343-2 3-2m0 8c1.11 0 2.08-.402 2.599-1M12 16v1m0-1v-4m0 4H9m3 0h3m-3 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" /></svg>
                         Finansial & Gaji
                     </button>
                     <button @click="activeTab = 'criteria'" type="button" :class="activeTab === 'criteria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                          <svg class="h-4 w-4 mr-1.5" :class="activeTab === 'criteria' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M12 17h.01M15 17h.01M9 10h.01M12 10h.01M15 10h.01M3 4a1 1 0 011-1h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V4z" /></svg>
                         Kriteria WSM
                     </button>
                     {{-- BARU: Tombol Tab File Proyek --}}
                     <button @click="activeTab = 'files'" type="button"
                             :class="activeTab === 'files' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                             class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" :class="activeTab === 'files' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                         File Proyek
                     </button>
                 </nav>
            </div>

            {{-- Tab Content Area --}}
            <div class="p-5 sm:p-8">

                {{-- Flash Message & Error Display --}}
                {{-- ... (Kode flash message tetap sama) ... --}}
                <div class="mb-6 space-y-4">
                     <div x-show="flashMessage" x-cloak
                          class="border px-4 py-3 rounded relative" role="alert"
                          :class="isFlashSuccess ? 'bg-green-50 border-green-300 text-green-700' : 'bg-red-50 border-red-300 text-red-700'"
                          x-transition>
                          <strong class="font-semibold" x-text="isFlashSuccess ? 'Sukses!' : 'Error!'"></strong>
                          <span class="block sm:inline ml-1" x-text="flashMessage"></span>
                          <button @click="flashMessage = ''" type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-xl font-semibold leading-none hover:text-opacity-75">×</button>
                     </div>
                     <div x-show="levelStatusMessage" x-cloak
                          class="border px-4 py-3 rounded relative" role="alert"
                          :class="isLevelStatusSuccess ? 'bg-green-50 border-green-300 text-green-700' : 'bg-red-50 border-red-300 text-red-700'"
                          x-transition>
                           <strong class="font-semibold" x-text="isLevelStatusSuccess ? 'Info:' : 'Error!'"></strong>
                           <span class="block sm:inline ml-1" x-text="levelStatusMessage"></span>
                           <button @click="levelStatusMessage = ''" type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-xl font-semibold leading-none hover:text-opacity-75">×</button>
                     </div>
                    @if($errors->any())
                         <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded relative" role="alert">
                              <p class="font-semibold">Oops! Terjadi kesalahan:</p>
                              <ul class="list-disc list-inside mt-1 text-sm">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                         </div>
                    @endif
                     {{-- Error khusus dari update termin --}}
                     @error('general')
                          <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded relative" role="alert">
                              <p class="font-semibold">Error!</p>
                              <span class="block sm:inline ml-1">{{ $message }}</span>
                          </div>
                     @enderror
                     <div x-show="isProjectLocked" x-cloak
                        class="bg-yellow-50 border-l-4 border-yellow-400 p-4" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.031-1.742 3.031H4.42c-1.532 0-2.492-1.697-1.742-3.031l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Pengaturan proyek ini telah dikunci karena sudah ada slip gaji yang disetujui. Perubahan pada informasi finansial, kriteria, dan data sensitif lainnya tidak diizinkan untuk menjaga integritas data.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================== --}}
                {{-- 1. Tab Info Proyek        --}}
                {{-- ========================== --}}
                <div x-show="activeTab === 'project'" x-transition.opacity>
                    <form action="{{ route('projects.pengaturan.info.update', $project) }}" method="POST" class="space-y-6">
                         @csrf
                         @method('PATCH')
                         <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Informasi Dasar Proyek</h3>
                         
                         {{-- Input name, status, dates, budget, wip --}}
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                             <div>
                                 <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Proyek</label>
                                 <input type="text" name="name" id="name" x-model="project.name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                             </div>
                             <div>
                                 <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                 <select name="status" id="status" x-model="project.status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                     @foreach(['open', 'in_progress', 'completed', 'cancelled'] as $s)
                                         <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                     @endforeach
                                 </select>
                                 @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                             </div>
                             <div>
                                 <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                 <input type="date" name="start_date" id="start_date" x-model="project.start_date" :disabled="isProjectLocked" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 @error('start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                             </div>
                             <div>
                                 <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                 <input type="date" name="end_date" id="end_date" x-model="project.end_date" :disabled="isProjectLocked"  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 @error('end_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                             </div>
                             <div>
                                 <label for="budget" class="block text-sm font-medium text-gray-700 mb-1">Anggaran (Budget)</label>
                                 <input type="number" name="budget" id="budget" step="any" min="0" x-model.number="project.budget" placeholder="Contoh: 5000000" :disabled="isProjectLocked" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 @error('budget')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                             </div>
                             <div>
                                 <label for="wip_limits" class="block text-sm font-medium text-gray-700 mb-1">WIP Limit Kanban (Opsional)</label>
                                 <input type="number" name="wip_limits" id="wip_limits" min="1" x-model.number="project.wip_limits" placeholder="Maks task 'In Progress'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                 @error('wip_limits')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                             </div>
                         </div>
                         <div>
                              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                              <textarea name="description" id="description" rows="4" x-model="project.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                              @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                         </div>

                        {{-- BARU: Kebutuhan Posisi Pekerja --}}
                        <fieldset class="border border-gray-300 p-4 rounded-md mt-6">
                            <legend class="text-md font-medium text-gray-800 px-2">Kebutuhan Posisi Pekerja</legend>
                            <div class="mt-2 space-y-3">
                                <template x-for="(position, index) in editableProjectPositions" :key="position.id || 'new-' + index">
                                    {{-- TAMBAHKAN x-bind:class DI SINI --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-9 gap-x-3 gap-y-2 items-end border-b pb-2.5 border-gray-200 transition-opacity duration-300"
                                         :class="{ 'opacity-50': position.markedForDeletion, 'line-through': position.markedForDeletion }">
                                        
                                        {{-- Hidden ID untuk posisi yang sudah ada --}}
                                        <input type="hidden" :name="`positions[${index}][id]`" :value="position.id">
                                        <div class="sm:col-span-4">
                                            <label :for="`pos_name_${index}`" class="block text-xs font-medium text-gray-600">Nama Posisi</label>
                                            <input type="text" :id="`pos_name_${index}`" :name="`positions[${index}][name]`" x-model="position.name" required placeholder="Contoh: Programmer" 
                                                   class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
                                                   :disabled="position.markedForDeletion">
                                            @php $posErrorPrefix = "positions." . "{index}" . ".name"; @endphp
                                            @error($posErrorPrefix) <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                            {{-- Menampilkan error spesifik dari backend agak tricky dengan x-for, bisa dihandle dengan $errors->get("positions.{$index}.name") jika direct blade, atau listen event error --}}
                                            </div>
                                        <div class="sm:col-span-3">
                                            <label :for="`pos_count_${index}`" class="block text-xs font-medium text-gray-600">Jumlah Dibutuhkan</label>
                                            <input type="number" :id="`pos_count_${index}`" :name="`positions[${index}][count]`" x-model.number="position.count" required min="1" 
                                                   class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
                                                   :disabled="position.markedForDeletion">
                                            @php $posErrorPrefixCount = "positions." . "{index}" . ".count"; @endphp
                                            @error($posErrorPrefixCount) <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                            </div>
                                        <div class="sm:col-span-2 flex items-end">
                                             {{-- Hidden input untuk delete flag --}}
                                             <input type="hidden" :name="`positions[${index}][delete]`" :value="position.markedForDeletion ? '1' : '0'">
                                            <button type="button" @click="removeEditableProjectPosition(index)" 
                                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 rounded-md focus:outline-none" 
                                                    title="Hapus Posisi"
                                                    :class="{ 'hidden': position.markedForDeletion }"> {{-- Sembunyikan tombol hapus jika sudah ditandai --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                            {{-- Opsional: Tombol Undo jika ingin --}}
                                            <button x-show="position.markedForDeletion" type="button" @click="undoRemoveEditableProjectPosition(index)"
                                                    class="p-1.5 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-100 rounded-md focus:outline-none" 
                                                    title="Batalkan Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <button type="button" @click="addEditableProjectPosition" class="mt-2 inline-flex items-center px-2.5 py-1 border border-dashed border-gray-400 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                    Tambah Posisi
                                </button>
                                @error('positions') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror {{-- Error general untuk array positions --}}
                            </div>
                        </fieldset>


                        {{-- Tombol Simpan --}}
                         <div class="pt-5 border-t border-gray-200 mt-6">
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                    Simpan Informasi Proyek
                                </button>
                            </div>
                         </div>
                    </form>
                </div>

                {{-- ========================== --}}
                {{-- 2. Tab Finansial & Gaji   --}}
                {{-- ========================== --}}
                <div x-show="activeTab === 'financial'" x-transition.opacity>
                    <div class="space-y-8">

                        {{-- Card Metode Kalkulasi Pembayaran --}}
                        <div class="bg-gray-50 shadow sm:rounded-lg border border-gray-200">
                            <form action="{{ route('projects.pengaturan.payment.update', $project) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="px-4 py-5 sm:p-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Metode Kalkulasi Pembayaran Utama</h3>
                                    <div class="mt-3 space-y-4">
                                        <p class="text-sm text-gray-600">Pilih cara utama perhitungan pembayaran task. Opsi bonus/lainnya tetap tersedia.</p>
                                        <fieldset :disabled="isProjectLocked">
                                            <legend class="sr-only">Metode Pembayaran</legend>
                                            <div class="space-y-3">
                                                {{-- Pilihan Termin --}}
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input id="payment_termin_fin" name="payment_calculation_type" type="radio" value="termin"
                                                               x-model="project.payment_calculation_type"
                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="payment_termin_fin" class="font-medium text-gray-700">Pembayaran Per Termin</label>
                                                        <p class="text-gray-500 text-xs">Definisikan termin pembayaran di bawah ini. Saat buat slip, pilih termin, lalu pilih task yang masuk periode termin tersebut.</p>
                                                    </div>
                                                </div>
                                                {{-- Pilihan Full --}}
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input id="payment_full_fin" name="payment_calculation_type" type="radio" value="full"
                                                               x-model="project.payment_calculation_type"
                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="payment_full_fin" class="font-medium text-gray-700">Pembayaran Full</label>
                                                        <p class="text-gray-500 text-xs">Langsung masukkan jumlah nominal saat buat slip gaji.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @error('payment_calculation_type')<p class="mt-1 text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-100 text-right sm:px-6 border-t border-gray-200">
                                    <button type="submit" :disabled="isProjectLocked" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                        Simpan Metode Pembayaran
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- --- BARU: Form Kelola Termin (muncul jika tipe 'termin') --- --}}
                        <div x-show="project.payment_calculation_type === 'termin'" x-transition.opacity class="bg-gray-50 shadow sm:rounded-lg border border-gray-200">
                            <form action="{{ route('projects.settings.terms.update', $project) }}" method="POST" @submit.prevent="validateTerms() && $el.submit()">
                                @csrf
                                @method('PATCH')
                                <div class="px-4 py-5 sm:p-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-1">Kelola Termin Pembayaran</h3>
                                    <p class="text-sm text-gray-600 mb-4">Definisikan nama dan periode setiap termin pembayaran.</p>
                                    <fieldset :disabled="isProjectLocked"></fieldset>
                                    {{-- List Termin yang Ada & Baru --}}
                                    <div class="space-y-4" x-ref="termsContainer">
                                        <template x-for="(term, index) in paymentTerms" :key="index">
                                            <div class="grid grid-cols-1 sm:grid-cols-10 gap-x-4 gap-y-2 items-end border-b pb-3 border-gray-200">
                                                {{-- Hidden ID for existing terms --}}
                                                <input type="hidden" :name="`terms[${index}][id]`" :value="term.id">
                                                {{-- Name --}}
                                                <div class="sm:col-span-3">
                                                    <label :for="`term_name_${index}`" class="block text-xs font-medium text-gray-600">Nama Termin</label>
                                                    <input type="text" :id="`term_name_${index}`" :name="`terms[${index}][name]`" x-model="term.name" required placeholder="Contoh: Termin 1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                                    <template x-if="errors && errors[`terms.${index}.name`]">
                                                        <p class="mt-1 text-xs text-red-500" x-text="errors[`terms.${index}.name`][0]"></p>
                                                    </template>
                                                </div>
                                                {{-- Start Date --}}
                                                <div class="sm:col-span-3">
                                                    <label :for="`term_start_${index}`" class="block text-xs font-medium text-gray-600">Tanggal Mulai</label>
                                                    <input type="date" :id="`term_start_${index}`" :name="`terms[${index}][start_date]`" x-model="term.start_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                                    <template x-if="errors && errors[`terms.${index}.start_date`]">
                                                        <p class="mt-1 text-xs text-red-500" x-text="errors[`terms.${index}.start_date`][0]"></p>
                                                    </template>
                                                </div>
                                                {{-- End Date --}}
                                                <div class="sm:col-span-3">
                                                    <label :for="`term_end_${index}`" class="block text-xs font-medium text-gray-600">Tanggal Akhir</label>
                                                    <input type="date" :id="`term_end_${index}`" :name="`terms[${index}][end_date]`" x-model="term.end_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                                    <template x-if="errors && errors[`terms.${index}.end_date`]">
                                                        <p class="mt-1 text-xs text-red-500" x-text="errors[`terms.${index}.end_date`][0]"></p>
                                                    </template>
                                                </div>
                                                {{-- Delete Button --}}
                                                <div class="sm:col-span-1 flex justify-end">
                                                     {{-- Hidden input for delete flag --}}
                                                     <input type="hidden" :name="`terms[${index}][delete]`" :value="term.markedForDeletion ? '1' : '0'">
                                                     <button type="button" @click="removeTerm(index)" :disabled="isProjectLocked" 
                                                             class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500" title="Hapus Termin">
                                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                     </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Tombol Tambah Termin --}}
                                    <div class="mt-4">
                                        <button type="button" @click="addTerm()" :disabled="isProjectLocked" class="inline-flex items-center px-3 py-1.5 border border-dashed border-gray-400 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500">
                                            <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                            Tambah Termin Baru
                                        </button>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-100 text-right sm:px-6 border-t border-gray-200">
                                    <button type="submit" :disabled="isProjectLocked" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                        Simpan Data Termin
                                    </button>
                                </div>
                            </form>
                        </div>
                        {{-- --- END BARU --- --}}

                        {{-- Card Kelola Standar Upah (Sama seperti sebelumnya) --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <div class="px-4 py-5 sm:p-6">
                                  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                                      <div>
                                          <h3 class="text-lg font-medium text-gray-900">Standar Upah Proyek</h3>
                                          <p class="text-sm text-gray-600 mt-1">Definisikan harga dasar per task untuk setiap kategori pekerjaan.</p>
                                      </div>
                                      {{-- BARU: Tombol Tambah memanggil modal --}}
                                      <button @click="openWageStandardModal('add')" :disabled="isProjectLocked" type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex-shrink-0">
                                          <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                          Tambah Standar
                                      </button>
                                  </div>
                                   <div class="overflow-x-auto -mx-4 sm:-mx-6">
                                       <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                           <div class="shadow border-b border-gray-200 sm:rounded-lg">
                                               <table class="min-w-full divide-y divide-gray-200">
                                                   <thead class="bg-gray-50">
                                                       <tr>
                                                           <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori Pekerjaan</th>
                                                           <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga Dasar Task</th>
                                                           <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                                                       </tr>
                                                   </thead>
                                                   <tbody class="bg-white divide-y divide-gray-200">
                                                       <template x-if="wageStandards.length === 0">
                                                           <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 italic">Belum ada standar upah.</td></tr>
                                                       </template>
                                                       <template x-for="standard in wageStandards" :key="standard.id">
                                                           <tr>
                                                               <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800" x-text="standard.job_category"></td>
                                                               <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" x-text="'Rp ' + Number(standard.task_price).toLocaleString('id-ID')"></td>
                                                               <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                                                   {{-- BARU: Tombol Edit memanggil modal --}}
                                                                   <button @click="openWageStandardModal('edit', standard)" :disabled="isProjectLocked" type="button" class="text-indigo-600 hover:text-indigo-800 mr-4">Edit</button>
                                                                    {{-- Form hapus tetap, atau bisa diubah ke AJAX juga nanti --}}
                                                                    <form :action="wageStandardDestroyUrlTemplate.replace(':standardId', standard.id)" method="POST" class="inline" @submit="if(!confirm('Yakin hapus standar ini?')) $event.preventDefault()">
                                                                        @csrf @method('DELETE')
                                                                        <button type="submit" :disabled="isProjectLocked" class="text-red-600 hover:text-red-800">Hapus</button>
                                                                   </form>
                                                               </td>
                                                           </tr>
                                                       </template>
                                                   </tbody>
                                               </table>
                                           </div>
                                       </div>
                                   </div>
                             </div>
                        </div>

                        {{-- Card Assign Standar Upah (Sama seperti sebelumnya) --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Assign Standar Upah Anggota Tim</h3>
                                        <p class="text-sm text-gray-600 mt-1">Tentukan standar upah dasar yang berlaku untuk perhitungan gaji per task.</p>
                                    </div>
                                    <div class="flex space-x-2 flex-shrink-0">
                                        <template x-if="!isEditingMemberWages">
                                            <button @click="toggleEditMemberWages" type="button" :disabled="isProjectLocked" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Edit Standar Tim
                                            </button>
                                        </template>
                                        <template x-if="isEditingMemberWages">
                                            <button @click="saveMemberWages" type="button" :disabled="isSubmittingMemberWages || isProjectLocked" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                                <svg x-show="!isSubmittingMemberWages" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                <svg x-show="isSubmittingMemberWages" class="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <span x-text="isSubmittingMemberWages ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                            </button>
                                            <button @click="cancelEditMemberWages" type="button" :disabled="isSubmittingMemberWages" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                                Batal
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="overflow-x-auto -mx-4 sm:-mx-6">
                                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                        <div class="shadow border-b border-gray-200 sm:rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/2">Standar Upah</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <template x-if="members.length === 0">
                                                        <tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500 italic">Belum ada anggota tim aktif.</td></tr>
                                                    </template>
                                                    <template x-for="member in members" :key="member.id">
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800" x-text="member.name"></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                                <template x-if="!isEditingMemberWages">
                                                                    <span x-text="getWageStandardName(member.pivot ? member.pivot.wage_standard_id : null)"></span>
                                                                </template>
                                                                <template x-if="isEditingMemberWages">
                                                                    <select x-model="memberAssignedWages[member.id]" :disabled="!isEditingMemberWages || isProjectLocked"
                                                                            class="mt-1 block w-full max-w-xs pl-3 pr-10 py-2 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                                                                        <option value="">-- Tidak Ditentukan --</option>
                                                                        <template x-for="standard in wageStandards" :key="standard.id">
                                                                            <option :value="String(standard.id)"
                                                                                    x-text="`${standard.job_category} (Rp ${Number(standard.task_price).toLocaleString('id-ID')})`">
                                                                            </option>
                                                                        </template>
                                                                    </select>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-3">Catatan: Perubahan standar upah berlaku untuk perhitungan task yang dibuat setelahnya.</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ========================== --}}
                {{-- 3. Tab Kriteria WSM       --}}
                {{-- ========================== --}}
                <div x-show="activeTab === 'criteria'" x-transition.opacity>
                    {{-- ... (Kode Bobot dan Kelola Level tidak berubah) ... --}}
                    <div class="space-y-8">
                        {{-- Card Bobot WSM --}}
                         <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <form action="{{ route('projects.settings.weights.update', $project) }}" method="POST">
                                 @csrf @method('PATCH')
                                 <div class="px-4 py-5 sm:p-6">
                                     <h3 class="text-lg font-medium leading-6 text-gray-900 mb-1">Bobot Kriteria WSM</h3>
                                     <p class="text-sm text-gray-600 mb-4">Total bobot kesulitan dan prioritas harus 100%.</p>
                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                         <div>
                                             <label for="difficulty_weight" class="block text-sm font-medium text-gray-700 mb-1">Bobot Kesulitan (%)</label>
                                             <input type="number" name="difficulty_weight" id="difficulty_weight" min="0" max="100" value="{{ old('difficulty_weight', $project->difficulty_weight) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :disabled="isProjectLocked">
                                             @error('difficulty_weight')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                         </div>
                                         <div>
                                             <label for="priority_weight" class="block text-sm font-medium text-gray-700 mb-1">Bobot Prioritas (%)</label>
                                             <input type="number" name="priority_weight" id="priority_weight" min="0" max="100" value="{{ old('priority_weight', $project->priority_weight) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :disabled="isProjectLocked">
                                             @error('priority_weight')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                         </div>
                                     </div>
                                     @error('weights')<p class="mt-1 text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
                                 </div>
                                  <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t border-gray-200">
                                       <button type="submit" :disabled="isProjectLocked" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                           Simpan Bobot
                                       </button>
                                   </div>
                             </form>
                         </div>
                         {{-- Card Kelola Level --}}
                          <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <div class="px-4 py-5 sm:p-6">
                                  <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Kelola Level Kriteria</h3>
                                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                      {{-- Difficulty Levels Section --}}
                                      <div>
                                          <div class="flex justify-between items-center mb-3">
                                              <h4 class="text-md font-semibold text-gray-800">Tingkat Kesulitan</h4>
                                              <button @click="openLevelModal('difficulty')" :disabled="isProjectLocked" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                  <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                                  Tambah
                                              </button>
                                          </div>
                                          <div class="border border-gray-200 rounded-md overflow-hidden">
                                              <ul x-ref="difficultyList" role="list" class="divide-y divide-gray-200">
                                                  <template x-for="level in difficultyLevels" :key="level.id">
                                                       <li :data-id="level.id" class="px-4 py-3 flex justify-between items-center group hover:bg-gray-50 cursor-grab">
                                                           <div class="flex items-center flex-grow min-w-0 mr-4">
                                                               <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 12h16m-7 6h7"></path></svg>
                                                               <span class="inline-block w-4 h-4 rounded-md border border-gray-400 flex-shrink-0" :style="{ backgroundColor: level.color }"></span>
                                                               <div class="min-w-0 ml-3">
                                                                   <span class="font-medium text-gray-800 block truncate text-sm" x-text="level.name"></span>
                                                                   <span class="text-xs text-gray-500" x-text="'Nilai: ' + level.value"></span>
                                                               </div>
                                                           </div>
                                                           <div class="flex space-x-3 flex-shrink-0 items-center">
                                                               <button @click="editLevel('difficulty', level)" :disabled="isProjectLocked" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Edit</button>
                                                               <form @submit.prevent="deleteLevel($event, 'difficulty', level.id)"> @csrf <button type="submit" :disabled="isProjectLocked" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button> </form>
                                                           </div>
                                                       </li>
                                                  </template>
                                                  <template x-if="difficultyLevels.length === 0">
                                                      <li class="px-4 py-4 text-center text-sm text-gray-500 italic">Belum ada level.</li>
                                                  </template>
                                              </ul>
                                          </div>
                                          <p class="text-xs text-gray-500 mt-2">* Drag & drop untuk mengubah urutan.</p>
                                      </div>
                                      {{-- Priority Levels Section --}}
                                      <div>
                                           <div class="flex justify-between items-center mb-3">
                                              <h4 class="text-md font-semibold text-gray-800">Prioritas</h4>
                                              <button @click="openLevelModal('priority')" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                  <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                                  Tambah
                                              </button>
                                           </div>
                                           <div class="border border-gray-200 rounded-md overflow-hidden">
                                               <ul x-ref="priorityList" role="list" class="divide-y divide-gray-200">
                                                   <template x-for="level in priorityLevels" :key="level.id">
                                                        <li :data-id="level.id" class="px-4 py-3 flex justify-between items-center group hover:bg-gray-50 cursor-grab">
                                                           <div class="flex items-center flex-grow min-w-0 mr-4">
                                                               <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 12h16m-7 6h7"></path></svg>
                                                               <span class="inline-block w-4 h-4 rounded-md border border-gray-400 flex-shrink-0" :style="{ backgroundColor: level.color }"></span>
                                                               <div class="min-w-0 ml-3">
                                                                   <span class="font-medium text-gray-800 block truncate text-sm" x-text="level.name"></span>
                                                                   <span class="text-xs text-gray-500" x-text="'Nilai: ' + level.value"></span>
                                                               </div>
                                                           </div>
                                                           <div class="flex space-x-3 flex-shrink-0 items-center">
                                                               <button @click="editLevel('priority', level)" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Edit</button>
                                                               <form @submit.prevent="deleteLevel($event, 'priority', level.id)"> @csrf <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button> </form>
                                                           </div>
                                                       </li>
                                                   </template>
                                                   <template x-if="priorityLevels.length === 0">
                                                       <li class="px-4 py-4 text-center text-sm text-gray-500 italic">Belum ada level.</li>
                                                   </template>
                                               </ul>
                                           </div>
                                           <p class="text-xs text-gray-500 mt-2">* Drag & drop untuk mengubah urutan.</p>
                                      </div>
                                 </div>
                             </div>
                          </div>
                    </div>
                </div>

                <div x-show="activeTab === 'files'" x-transition.opacity>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-6">Manajemen File Proyek</h3>

                    {{-- Form Unggah File --}}
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Unggah File Baru</h4>
                        <form action="{{ route('projects.settings.files.store', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label for="file_upload_input" class="block text-sm font-medium text-gray-700 mb-1">Pilih File</label>
                                <input type="file" name="file" id="file_upload_input" required
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Tampilan (Opsional)</label>
                                <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}" placeholder="Biarkan kosong untuk menggunakan nama asli file"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('display_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="file_description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (Opsional)</label>
                                <textarea name="description" id="file_description" rows="2" placeholder="Deskripsi singkat tentang file"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Unggah File
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Daftar File yang Sudah Diunggah --}}
                    <div>
                        <h4 class="text-md font-semibold text-gray-800 mb-4">File Tersimpan</h4>
                        @if($projectFiles->count() > 0)
                            <div class="space-y-4">
                                @foreach($projectFiles as $file)
                                <div class="border rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between hover:shadow-md transition-shadow duration-150">
                                    <div class="flex-1 min-w-0 mb-3 sm:mb-0">
                                        <div class="flex items-center">
                                            @if($file->isImage())
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            @elseif($file->isPdf())
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v4a2 2 0 002 2h4" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 10h6M9 13h6M9 16h3" /></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                            @endif
                                            <a href="{{ $file->url }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 truncate" title="Lihat/Unduh: {{ $file->file_name }}">
                                                {{ $file->display_name ?: $file->file_name }}
                                            </a>
                                        </div>
                                        @if($file->description)
                                            <p class="text-xs text-gray-500 mt-1 ml-8">{{ Str::limit($file->description, 80) }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-1 ml-8">
                                            Ukuran: {{ $file->formatted_size }} | Diunggah: {{ $file->created_at->format('d M Y') }} oleh {{ $file->user->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        <a href="{{ $file->url }}" target="_blank" class="p-1.5 text-blue-600 hover:text-blue-800 rounded-md hover:bg-blue-50" title="Lihat/Unduh File">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 12z" /></svg>
                                        </a>
                                        {{-- Tambah tombol edit di sini jika perlu --}}
                                        <form action="{{ route('projects.settings.files.destroy', [$project, $file]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus file \'{{ e($file->display_name ?: $file->file_name) }}\'? Tindakan ini tidak dapat diurungkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 rounded-md hover:bg-red-50" title="Hapus File">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            {{-- Pagination untuk File --}}
                            <div class="mt-6" x-html="projectFilesPagination">
                                {{-- Akan diisi oleh Alpine jika menggunakan fetch, atau render langsung jika dari controller --}}
                                {{ $projectFiles->links('vendor.pagination.tailwind') }}
                            </div>
                        @else
                            <p class="text-gray-500 italic text-center py-4">Belum ada file yang diunggah untuk proyek ini.</p>
                        @endif
                    </div>
                </div>

            </div> {{-- End Tab Content Area --}}

        </div> {{-- End Container Utama Pengaturan --}}

        {{-- Level Add/Edit Modal (Sama seperti sebelumnya) --}}
        <div x-show="isLevelModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- ... (Kode modal tidak berubah) ... --}}
             <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                 <div x-show="isLevelModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeLevelModal()" aria-hidden="true"></div>
                 <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                 <div x-show="isLevelModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                      <form @submit.prevent="submitLevelForm">
                           <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4"> <div class="sm:flex sm:items-start w-full"> <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                               <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-text="levelToEdit ? 'Edit Level ' + levelModalTypeLabel : 'Tambah Level ' + levelModalTypeLabel"></h3>
                               <div class="mt-4 space-y-4">
                                   <div> <label for="level-name-modal" class="block text-sm font-medium text-gray-700 mb-1">Nama Level</label> <input type="text" name="name" id="level-name-modal" x-model="currentLevel.name" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"> <template x-if="levelFormErrors.name"><p class="mt-1 text-xs text-red-600" x-text="levelFormErrors.name[0]"></p></template> </div>
                                   <div> <label for="level-value-modal" class="block text-sm font-medium text-gray-700 mb-1">Nilai Numerik</label> <input type="number" name="value" id="level-value-modal" x-model.number="currentLevel.value" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"> <template x-if="levelFormErrors.value"><p class="mt-1 text-xs text-red-600" x-text="levelFormErrors.value[0]"></p></template> </div>
                                   <div> <label for="level-color-modal" class="block text-sm font-medium text-gray-700 mb-1">Warna</label> <div class="mt-1 flex items-center space-x-3"> <input type="color" name="color" id="level-color-modal" x-model="currentLevel.color" required pattern="^#[a-fA-F0-9]{6}$" class="h-8 w-10 border-gray-300 rounded-md p-0 cursor-pointer shadow-sm"> <input type="text" x-model="currentLevel.color" @input="currentLevel.color = $event.target.value.startsWith('#') ? $event.target.value : '#' + $event.target.value" required pattern="^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" placeholder="#rrggbb" maxlength="7" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"> </div> <template x-if="levelFormErrors.color"><p class="mt-1 text-xs text-red-600" x-text="levelFormErrors.color[0]"></p></template> </div>
                               </div>
                           </div></div></div>
                           <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                               <button type="submit" :disabled="isLevelSubmitting" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"> <span x-show="!isLevelSubmitting" x-text="levelToEdit ? 'Simpan Perubahan' : 'Tambah Level'"></span> <span x-show="isLevelSubmitting" class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Menyimpan...</span> </button>
                               <button type="button" @click="closeLevelModal()" :disabled="isLevelSubmitting" class="mt-3 inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"> Batal </button>
                           </div>
                      </form>
                 </div>
            </div>
        </div>
        {{-- End Modal --}}

        <div x-show="isWageStandardModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="wage-standard-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div x-show="isWageStandardModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeWageStandardModal()" aria-hidden="true"></div>
                {{-- Modal panel --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                <div x-show="isWageStandardModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitWageStandardForm">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start w-full">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="wage-standard-modal-title" x-text="wageStandardModalType === 'add' ? 'Tambah Standar Upah' : 'Edit Standar Upah'"></h3>
                                    <div class="mt-4 space-y-4">
                                        {{-- Job Category --}}
                                        <div>
                                            <label for="ws-job-category" class="block text-sm font-medium text-gray-700 mb-1">Kategori Pekerjaan</label>
                                            <input type="text" name="job_category" id="ws-job-category" x-model="currentWageStandard.job_category" required maxlength="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <template x-if="wageStandardFormErrors.job_category"><p class="mt-1 text-xs text-red-600" x-text="wageStandardFormErrors.job_category[0]"></p></template>
                                        </div>
                                        {{-- Task Price --}}
                                        <div>
                                            <label for="ws-task-price" class="block text-sm font-medium text-gray-700 mb-1">Harga Task</label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                                </div>
                                                <input type="number" name="task_price" id="ws-task-price" x-model.number="currentWageStandard.task_price" required min="0" step="any" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 sm:text-sm border-gray-300 rounded-md" placeholder="0">
                                            </div>
                                            <template x-if="wageStandardFormErrors.task_price"><p class="mt-1 text-xs text-red-600" x-text="wageStandardFormErrors.task_price[0]"></p></template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button type="submit" :disabled="isWageStandardSubmitting" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-show="!isWageStandardSubmitting" x-text="wageStandardModalType === 'add' ? 'Simpan' : 'Simpan Perubahan'"></span>
                                <span x-show="isWageStandardSubmitting" class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Menyimpan...</span>
                            </button>
                            <button type="button" @click="closeWageStandardModal()" :disabled="isWageStandardSubmitting" class="mt-3 inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Include SortableJS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    {{-- AlpineJS Logic --}}
    <script>
        function projectSettings(config) {
            return {
                project: config.project,
                // categories: config.initialCategories,
                // selectedCategories: config.initialSelectedCategories,
                wageStandards: config.initialWageStandards, // Ini akan diupdate oleh modal
                members: [],
                paymentTerms: [],
                difficultyLevels: config.initialDifficultyLevels,
                priorityLevels: config.initialPriorityLevels,
                errors: config.initialErrors || {},
                projectUpdateUrl: config.projectUpdateUrl,
                paymentTypeUpdateUrl: config.paymentTypeUpdateUrl,
                paymentTermsUpdateUrl: config.paymentTermsUpdateUrl,
                weightsUpdateUrl: config.weightsUpdateUrl,
                levelUpdateOrderUrl: config.levelUpdateOrderUrl,
                memberWageUpdateUrlTemplate: config.memberWageUpdateUrlTemplate,
                
                // BARU: URL untuk Standar Upah
                wageStandardStoreUrlTemplate: config.wageStandardStoreUrlTemplate,
                wageStandardUpdateUrlTemplate: config.wageStandardUpdateUrlTemplate,
                wageStandardDestroyUrlTemplate: config.wageStandardDestroyUrlTemplate,
                
                batchMemberWageUpdateUrl: config.batchMemberWageUpdateUrl,

                csrfToken: config.csrfToken,
                activeTab: config.activeTab,
                isLevelModalOpen: false,
                isLevelSubmitting: false,
                memberWageStatus: {},
                flashMessage: config.flashMessage,
                isFlashSuccess: config.isFlashSuccess,
                levelStatusMessage: '',
                isLevelStatusSuccess: true,
                levelModalType: 'difficulty',
                levelToEdit: null,
                currentLevel: { id: null, name: '', value: 1, color: '#cccccc' },
                levelFormErrors: {},
                projectFiles: config.projectFiles || [],
                projectFilesPagination: config.projectFilesPagination || '',
                
                editableProjectPositions: [],
                // BARU: State untuk Modal Standar Upah
                isWageStandardModalOpen: false,
                wageStandardModalType: 'add', // 'add' or 'edit'
                currentWageStandard: { id: null, job_category: '', task_price: null }, // task_price bisa null atau 0
                wageStandardFormErrors: {},
                isWageStandardSubmitting: false,

                // State untuk Assign Standar Upah Batch
                isEditingMemberWages: false,
                memberAssignedWages: {}, 
                originalMemberAssignedWages: {},
                isSubmittingMemberWages: false,

                isProjectLocked: config.isProjectLocked,

                get nextTermIndex() { return this.paymentTerms.length; },
                get levelModalTypeLabel() { return this.levelModalType === 'difficulty' ? 'Kesulitan' : 'Prioritas'; },

            // Methods
            initComponent() {
    console.log('[Pengaturan Proyek] Menginisialisasi komponen Alpine...');

    // 1. Inisialisasi data dari config
    // Kita melakukan ini terlebih dahulu untuk memastikan semua data mentah tersedia.
    
    // Data utama proyek, dengan format tanggal yang benar
    this.project.start_date = this.formatDateForInput(config.project.start_date);
    this.project.end_date = this.formatDateForInput(config.project.end_date);
    
    // Data termin, dengan format tanggal dan flag tambahan
    this.paymentTerms = (config.initialPaymentTerms || []).map(term => ({
        ...term,
        start_date: this.formatDateForInput(term.start_date),
        end_date: this.formatDateForInput(term.end_date),
        markedForDeletion: false
    }));
    
    // Data standar upah
    this.wageStandards = JSON.parse(JSON.stringify(config.initialWageStandards || []));

    // Data level (Difficulty & Priority)
    // Tidak perlu diubah karena sudah di-pass langsung dari config
    
    // Data Anggota Tim (Members) dan standar upah mereka
    this.members = JSON.parse(JSON.stringify(config.initialMembers || []));
    this.members.forEach(member => {
        const currentVal = (member.pivot && member.pivot.wage_standard_id !== null) ? String(member.pivot.wage_standard_id) : '';
        this.memberAssignedWages[member.id] = currentVal;
    });

    // ==========================================================
    // ===== INI BAGIAN PERBAIKAN UTAMA =====
    // ==========================================================
    // Selalu reset 'editableProjectPositions' dengan data terbaru dari config.
    // Ini akan memperbaiki masalah di mana posisi tidak muncul setelah navigasi Turbo.
    const initialPositions = config.project.project_positions || [];
    this.editableProjectPositions = JSON.parse(JSON.stringify(initialPositions));
    this.editableProjectPositions.forEach(pos => pos.markedForDeletion = false);
    console.log(`[Pengaturan Proyek] Data Posisi dimuat: ${this.editableProjectPositions.length} item.`);
    // ==========================================================
    // ===== AKHIR PERBAIKAN =====
    // ==========================================================
    
    // 2. Inisialisasi UI dan state lainnya
    
    // Setup SortableJS
    if (typeof Sortable === 'undefined') {
        console.error('SortableJS tidak termuat.');
    } else {
        this.$nextTick(() => { // Pastikan DOM sudah siap sebelum menginisialisasi Sortable
            this.initSortable();
        });
    }

    // Handle flash message
    if (this.flashMessage) {
        setTimeout(() => { this.flashMessage = ''; }, 5000);
    }

    // Atur tab aktif berdasarkan sesi dari backend
    if (config.successCriteria) this.activeTab = 'criteria';
    if (config.successFinancial) this.activeTab = 'financial';
    if (config.successInfo) this.activeTab = 'project';
    // (Anda bisa tambahkan untuk tab 'files' jika perlu)

    // Reset state modal
    this.isLevelModalOpen = false;
    this.isLevelSubmitting = false;

    console.log('[Pengaturan Proyek] Inisialisasi komponen selesai.');
    this.isProjectLocked = config.isProjectLocked; 
},

            formatDateForInput(dateString) {
                if (!dateString) return '';
                try { return new Date(dateString).toISOString().split('T')[0]; }
                catch (e) { console.warn("Could not parse date:", dateString); return ''; }
            },

            addTerm() {
    // Ensure project has start_date
    if (!this.project.start_date) {
        this.showFlashMessage('Proyek harus memiliki tanggal mulai sebelum menambah termin.', false);
        return;
    }

    let defaultStartDate = '';
    let defaultEndDate = '';

    // Find the last non-deleted term
    const activeTerms = this.paymentTerms.filter(term => !term.markedForDeletion);
    if (activeTerms.length > 0) {
        const lastTerm = activeTerms[activeTerms.length - 1];
        if (lastTerm.end_date) {
            try {
                const nextDay = new Date(lastTerm.end_date);
                nextDay.setDate(nextDay.getDate() + 1);
                // Ensure the suggested start date is within project end_date
                if (this.project.end_date && nextDay <= new Date(this.project.end_date)) {
                    defaultStartDate = this.formatDateForInput(nextDay);
                } else {
                    this.showFlashMessage('Tidak dapat menambah termin karena melebihi tanggal akhir proyek.', false);
                    return;
                }
            } catch (e) {
                console.error('Error calculating next term start date:', e);
            }
        }
    } else {
        // If no terms exist, start from project start_date
        defaultStartDate = this.formatDateForInput(this.project.start_date);
    }

    // Suggest end date (e.g., 7 days after start date or project end_date)
    if (defaultStartDate) {
        try {
            const endPlusWeek = new Date(defaultStartDate);
            endPlusWeek.setDate(endPlusWeek.getDate() + 7);
            const projectEndDate = new Date(this.project.end_date);
            defaultEndDate = this.formatDateForInput(
                endPlusWeek <= projectEndDate ? endPlusWeek : projectEndDate
            );
        } catch (e) {
            console.error('Error calculating default end date:', e);
        }
    }

    this.paymentTerms.push({
        id: null,
        name: `Termin ${this.paymentTerms.length + 1}`,
        start_date: defaultStartDate,
        end_date: defaultEndDate,
        markedForDeletion: false
    });

    this.$nextTick(() => {
        if (this.$refs.termsContainer) {
            this.$refs.termsContainer.scrollTop = this.$refs.termsContainer.scrollHeight;
        }
    });
},
validateTerms() {
    this.errors = {};
    const activeTerms = this.paymentTerms.filter(term => !term.markedForDeletion);
    const sortedTerms = [...activeTerms].sort((a, b) => new Date(a.start_date) - new Date(b.start_date));

    // Check project date boundaries
    const projectStart = new Date(this.project.start_date);
    const projectEnd = new Date(this.project.end_date);

    sortedTerms.forEach((term, index) => {
        const startDate = new Date(term.start_date);
        const endDate = new Date(term.end_date);

        // Validate within project dates
        if (startDate < projectStart) {
            this.errors[`terms.${index}.start_date`] = ['Tanggal mulai termin harus di atau setelah tanggal mulai proyek.'];
        }
        if (endDate > projectEnd) {
            this.errors[`terms.${index}.end_date`] = ['Tanggal akhir termin tidak boleh melebihi tanggal selesai proyek.'];
        }
        if (endDate < startDate) {
            this.errors[`terms.${index}.end_date`] = ['Tanggal akhir harus di atau setelah tanggal mulai.'];
        }

        // Check overlaps with next term
        if (index < sortedTerms.length - 1) {
            const nextTerm = sortedTerms[index + 1];
            const nextStartDate = new Date(nextTerm.start_date);
            if (nextStartDate <= endDate) {
                this.errors[`terms.${this.paymentTerms.indexOf(nextTerm)}.start_date`] = [
                    `Tanggal mulai termin '${nextTerm.name}' tidak boleh sebelum atau sama dengan tanggal akhir '${term.name}'.`
                ];
            }
        }
    });

    return Object.keys(this.errors).length === 0;
},
            removeTerm(index) {
                 if (this.paymentTerms[index].id) {
                     this.$set(this.paymentTerms[index], 'markedForDeletion', true);
                     this.showFlashMessage(`Termin '${this.paymentTerms[index].name}' akan dihapus saat disimpan.`, true);
                 } else {
                     this.paymentTerms.splice(index, 1);
                 }
             },
             openLevelModal(type, level = null) {
                 this.levelModalType = type;
                 this.levelToEdit = level;
                 this.levelFormErrors = {};
                 this.levelStatusMessage = '';
                 if (level) { this.currentLevel = { ...level }; }
                 else { this.currentLevel = { id: null, name: '', value: 1, color: '#cccccc' }; }
                 this.isLevelModalOpen = true;
             },
             closeLevelModal() { this.isLevelModalOpen = false; },
             editLevel(type, level) { this.openLevelModal(type, level); },
             submitLevelForm() {
                 this.isLevelSubmitting = true; this.levelFormErrors = {};
                 let url, method, body = { ...this.currentLevel, _token: this.csrfToken };
                 if (this.levelToEdit) {
                     url = `/projects/${this.project.id}/settings/levels/${this.levelModalType}/${this.levelToEdit.id}`;
                     body._method = 'PATCH'; method = 'POST';
                 } else {
                     url = `/projects/${this.project.id}/settings/levels/${this.levelModalType}`;
                     method = 'POST'; if (!this.levelToEdit) delete body.id;
                 }
                 fetch(url, { method: method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify(body) })
                 .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                 .then(({ status, ok, body }) => {
                     if (ok && body.success) {
                          const listKey = this.levelModalType + 'Levels';
                          if (this.levelToEdit) { const index = this[listKey].findIndex(l => l.id === this.levelToEdit.id); if (index !== -1) this[listKey].splice(index, 1, body.level || this.currentLevel); }
                          else { this[listKey].push(body.level || this.currentLevel); this.$nextTick(() => this.initSortable()); }
                          this.closeLevelModal(); this.showLevelFlash(body.message || 'Level disimpan.', true);
                     } else if (status === 422) { this.levelFormErrors = body.errors; this.showLevelFlash(body.message || 'Validasi gagal.', false); }
                     else { this.showLevelFlash(body.message || 'Gagal menyimpan.', false); }
                 })
                 .catch(err => { console.error(err); this.showLevelFlash('Request error.', false); })
                 .finally(() => { this.isLevelSubmitting = false; });
             },
             deleteLevel(event, type, levelId) {
                 event.preventDefault(); if (!confirm('Yakin hapus level ini? Task yang menggunakan level ini mungkin akan terpengaruh.')) return;
                 const url = `/projects/${this.project.id}/settings/levels/${type}/${levelId}`;
                 const button = event.currentTarget.querySelector('button[type="submit"]');
                 if(button) button.disabled = true;

                 fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken } })
                 .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                 .then(({ status, ok, body }) => {
                     if (ok && body.success) {
                          const listKey = type + 'Levels'; this[listKey] = this[listKey].filter(l => l.id !== levelId);
                          this.showLevelFlash(body.message || 'Level dihapus.', true);
                     } else { this.showLevelFlash(body.message || 'Gagal hapus.', false); }
                 })
                 .catch(err => { this.showLevelFlash('Request error.', false); })
                 .finally(() => { if(button) button.disabled = false; });
             },
              showLevelFlash(message, success = true) {
                  this.levelStatusMessage = message;
                  this.isLevelStatusSuccess = success;
                  setTimeout(() => { this.levelStatusMessage = ''; }, 5000);
             },
              initSortable() {
                const self = this;
                ['difficulty', 'priority'].forEach(type => {
                    const listEl = this.$refs[type + 'List'];
                    if (listEl) {
                        if (listEl.sortableInstance) {
                            try { listEl.sortableInstance.destroy(); } catch(e) { console.error('Error destroying Sortable instance:', e); }
                        }
                        try {
                            listEl.sortableInstance = new Sortable(listEl, {
                                animation: 150, handle: '.cursor-move', ghostClass: 'sortable-ghost',
                                onEnd: (evt) => {
                                    const orderedIds = Array.from(evt.to.children).map(item => item.dataset.id).filter(id => id);
                                    if (orderedIds.length > 0) {
                                        const listKey = type + 'Levels';
                                        self[listKey] = orderedIds.map(id => self[listKey].find(l => l.id == id));
                                        self.saveOrder(type, orderedIds);
                                    }
                                }
                            });
                        } catch(e) { console.error('Error initializing Sortable:', e); }
                    }
                });
            },
             saveOrder(levelType, orderedIds) {
                fetch(this.levelUpdateOrderUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify({ _method: 'PATCH', level_type: levelType, ordered_ids: orderedIds }) })
                .then(res => res.json())
                .then(data => { if (data.success) this.showLevelFlash('Urutan disimpan.', true); else this.showLevelFlash(data.message || 'Gagal simpan urutan.', false); })
                .catch(err => { this.showLevelFlash('Request error urutan.', false); });
             },

             getWageStandardName(standardId) {
                    if (standardId === null || standardId === '' || standardId === undefined) {
                        return '-- Tidak Ditentukan --';
                    }
                    const standard = this.wageStandards.find(ws => String(ws.id) === String(standardId));
                    return standard ? `${standard.job_category} (Rp ${Number(standard.task_price).toLocaleString('id-ID')})` : '-- Standar Tidak Valid --';
                },

                toggleEditMemberWages() {
                    this.isEditingMemberWages = !this.isEditingMemberWages;
                    if (this.isEditingMemberWages) {
                        this.originalMemberAssignedWages = {};
                        this.members.forEach(member => {
                            const currentVal = (member.pivot && member.pivot.wage_standard_id !== null) ? String(member.pivot.wage_standard_id) : '';
                            this.memberAssignedWages[member.id] = currentVal; // Pastikan ini selalu string untuk select
                            this.originalMemberAssignedWages[member.id] = currentVal;
                        });
                    }
                },

                cancelEditMemberWages() {
                    this.isEditingMemberWages = false;
                    // Kembalikan nilai yang di-bind ke form ke nilai asli sebelum edit
                    this.memberAssignedWages = JSON.parse(JSON.stringify(this.originalMemberAssignedWages));
                    this.showFlashMessage('Perubahan standar upah anggota dibatalkan.', true);
                },

                saveMemberWages() {
                    this.isSubmittingMemberWages = true;
                    const payloadAssignments = [];
                    for (const userId in this.memberAssignedWages) {
                        payloadAssignments.push({
                            user_id: parseInt(userId),
                            // Jika string kosong, kirim null. Jika tidak, parse ke integer.
                            wage_standard_id: this.memberAssignedWages[userId] === '' ? null : parseInt(this.memberAssignedWages[userId])
                        });
                    }

                    fetch(this.batchMemberWageUpdateUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({ assignments: payloadAssignments })
                    })
                    .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                    .then(({ status, ok, body }) => {
                        if (ok && body.success) {
                            // Update data utama 'members' di Alpine
                            body.updatedAssignments.forEach(assignment => {
                                const memberIndex = this.members.findIndex(m => m.id === assignment.user_id);
                                if (memberIndex !== -1) {
                                    if (!this.members[memberIndex].pivot) {
                                        this.members[memberIndex].pivot = {}; // Inisialisasi pivot jika belum ada
                                    }
                                    this.members[memberIndex].pivot.wage_standard_id = assignment.wage_standard_id;
                                }
                            });
                            this.isEditingMemberWages = false;
                            this.showFlashMessage(body.message || 'Standar upah anggota tim berhasil disimpan.', true);
                        } else {
                            let errorMsg = body.message || 'Gagal menyimpan standar upah anggota tim.';
                            if (status === 422 && body.errors) {
                                // Jika ada error validasi spesifik per item, Anda bisa coba tampilkan
                                // Untuk sekarang, tampilkan pesan umum saja
                                console.error("Validation errors:", body.errors);
                                errorMsg = "Terdapat data yang tidak valid. Mohon periksa kembali.";
                            }
                            this.showFlashMessage(errorMsg, false);
                        }
                    })
                    .catch(err => {
                        console.error('Error saving member wages:', err);
                        this.showFlashMessage('Terjadi kesalahan request saat menyimpan standar upah anggota.', false);
                    })
                    .finally(() => {
                        this.isSubmittingMemberWages = false;
                    });
                },
              updateMemberWage(memberId, wageStandardId) {
                  wageStandardId = wageStandardId === "" ? null : wageStandardId;
                  const url = this.memberWageUpdateUrlTemplate.replace(':userId', memberId);
                  this.memberWageStatus[memberId] = 'saving';
                  fetch(url, {  method: 'PATCH',  headers: {  'Content-Type': 'application/json',  'Accept': 'application/json',  'X-CSRF-TOKEN': this.csrfToken  },  body: JSON.stringify({ wage_standard_id: wageStandardId })  })
                  .then(res => res.json().then(data => ({ ok: res.ok, body: data })))
                  .then(({ ok, body }) => {
                      if (ok && body.success) {
                          this.memberWageStatus[memberId] = 'success';
                          const index = this.members.findIndex(m => m.id === memberId);
                          if (index !== -1 && this.members[index].pivot) { // Check if pivot exists
                              this.members[index].pivot.wage_standard_id = wageStandardId;
                          } else if (index !== -1) { // If pivot doesn't exist, create it (might need adjustment based on actual data structure)
                            this.members[index].pivot = { wage_standard_id: wageStandardId };
                          }
                          setTimeout(() => this.memberWageStatus[memberId] = null, 2500);
                      } else {  this.memberWageStatus[memberId] = 'error';  this.showFlashMessage(body.message || 'Gagal update.', false);  setTimeout(() => this.memberWageStatus[memberId] = null, 3500);  }
                  })
                  .catch(err => {  this.memberWageStatus[memberId] = 'error';  this.showFlashMessage('Request error.', false);  setTimeout(() => this.memberWageStatus[memberId] = null, 3500);  });
              },
              showFlashMessage(message, success = true) {
                 this.flashMessage = message;
                 this.isFlashSuccess = success;
                 this.levelStatusMessage = '';
                 setTimeout(() => { this.flashMessage = ''; }, 5000);
             },
             
             openWageStandardModal(type, standard = null) {
                    this.wageStandardModalType = type;
                    this.wageStandardFormErrors = {};
                    this.levelStatusMessage = ''; // Clear other messages
                    if (standard) {
                        // Pastikan task_price adalah angka jika diedit
                        this.currentWageStandard = { ...standard, task_price: parseFloat(standard.task_price) || 0 };
                    } else {
                        this.currentWageStandard = { id: null, job_category: '', task_price: null };
                    }
                    this.isWageStandardModalOpen = true;
                },

                closeWageStandardModal() {
                    this.isWageStandardModalOpen = false;
                    this.currentWageStandard = { id: null, job_category: '', task_price: null }; // Reset
                    this.wageStandardFormErrors = {};
                },

                submitWageStandardForm() {
                    this.isWageStandardSubmitting = true;
                    this.wageStandardFormErrors = {};
                    
                    let url, method;
                    const payload = { ...this.currentWageStandard, _token: this.csrfToken };

                    if (this.wageStandardModalType === 'edit') {
                        url = this.wageStandardUpdateUrlTemplate.replace(':standardId', this.currentWageStandard.id);
                        payload._method = 'PATCH'; // atau PUT, sesuaikan dengan route
                        method = 'POST'; // Form method tetap POST, _method akan dihandle Laravel
                    } else { // add
                        url = this.wageStandardStoreUrlTemplate;
                        method = 'POST';
                        if(payload.id === null) delete payload.id; // Hapus id jika null saat tambah baru
                    }
                    
                    // Pastikan task_price adalah string agar validasi backend (numeric) tidak gagal jika input kosong/null
                    if (payload.task_price === null || payload.task_price === '') {
                         payload.task_price = ''; // Kirim string kosong jika null/kosong agar validasi numeric bisa berjalan
                    } else {
                         payload.task_price = String(payload.task_price); // Kirim sebagai string
                    }


                    fetch(url, {
                        method: method,
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
                            const newOrUpdatedStandard = body.wageStandard;
                            if (this.wageStandardModalType === 'add') {
                                this.wageStandards.push(newOrUpdatedStandard);
                            } else { // edit
                                const index = this.wageStandards.findIndex(ws => ws.id === newOrUpdatedStandard.id);
                                if (index !== -1) {
                                    this.wageStandards.splice(index, 1, newOrUpdatedStandard);
                                } else { // Jika tidak ketemu (jarang terjadi), tambahkan saja
                                    this.wageStandards.push(newOrUpdatedStandard);
                                }
                            }
                            // Urutkan kembali berdasarkan job_category setelah modifikasi
                            this.wageStandards.sort((a, b) => a.job_category.localeCompare(b.job_category));

                            this.closeWageStandardModal();
                            this.showFlashMessage(body.message || 'Standar upah berhasil disimpan.', true);
                            // Set active tab agar user tidak bingung
                            this.activeTab = 'financial';
                        } else if (status === 422) { // Validation error
                            this.wageStandardFormErrors = body.errors || {};
                            this.showFlashMessage(body.message || 'Input tidak valid. Mohon periksa kembali.', false);
                        } else { // Other server errors
                            this.showFlashMessage(body.message || 'Terjadi kesalahan saat menyimpan standar upah.', false);
                        }
                    })
                    .catch(err => {
                        console.error('Wage Standard form submission error:', err);
                        this.showFlashMessage('Request error. Tidak dapat menghubungi server.', false);
                    })
                    .finally(() => {
                        this.isWageStandardSubmitting = false;
                    });
                },
                
                addEditableProjectPosition() {
                    this.editableProjectPositions.push({ id: null, name: '', count: 1, markedForDeletion: false });
                },
                removeEditableProjectPosition(index) {
                    const position = this.editableProjectPositions[index];
                    if (position.id) {
                        this.editableProjectPositions[index].markedForDeletion = true;
                        // Flash message bisa di-delay atau dikondisikan agar tidak terlalu mengganggu jika ada undo
                        // this.showFlashMessage(`Posisi '${position.name}' akan dihapus saat disimpan.`, true);
                    } else {
                        this.editableProjectPositions.splice(index, 1);
                    }
                },
                // BARU: Fungsi untuk membatalkan penghapusan posisi
                undoRemoveEditableProjectPosition(index) {
                    if (this.editableProjectPositions[index] && this.editableProjectPositions[index].markedForDeletion) {
                        this.editableProjectPositions[index].markedForDeletion = false;
                        this.showFlashMessage(`Penghapusan posisi '${this.editableProjectPositions[index].name}' dibatalkan.`, true);
                    }
                }
            };
        }
    </script>
    @push('styles')
        <style>
            .sortable-ghost { background-color: #e9ecef; opacity: 0.7; border: 1px dashed #adb5bd; }
            .cursor-grab { cursor: grab; }
            .cursor-move { cursor: move; }
            /* Style untuk termin yang ditandai hapus */
            .term-marked-for-deletion {
                opacity: 0.5;
                text-decoration: line-through;
            }
            .term-marked-for-deletion input,
            .term-marked-for-deletion button:not(.remove-term) {
                pointer-events: none;
            }
        </style>
    @endpush
</x-app-layout>