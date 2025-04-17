<x-app-layout>
    {{-- AlpineJS Data Setup --}}
    <div x-data="projectSettings({
            // Data awal dari Controller
            project: {{ Js::from($project) }},
            initialCategories: {{ Js::from($categories) }},
            initialSelectedCategories: {{ Js::from($selectedCategories) }},
            initialWageStandards: {{ Js::from($wageStandards) }},
            initialMembers: {{ Js::from($members) }},
            initialDifficultyLevels: {{ Js::from($difficultyLevels) }},
            initialPriorityLevels: {{ Js::from($priorityLevels) }},
            // URLs & Tokens
            projectUpdateUrl: '{{ route('projects.pengaturan.update', $project) }}',
            weightsUpdateUrl: '{{ route('projects.settings.weights.update', $project) }}',
            levelUpdateOrderUrl: '{{ route('projects.settings.levels.order', $project) }}',
            memberWageUpdateUrlTemplate: '{{ route('projects.settings.team.wage.update', [$project, ':userId']) }}',
            csrfToken: '{{ csrf_token() }}',
            // State Management
            activeTab: '{{ session('active_tab', 'project') }}',
            // === PERBAIKAN: Pastikan state awal modal false ===
            isLevelModalOpen: false,
            isLevelSubmitting: false,
            // ====================================================
            memberWageStatus: {},
            flashMessage: '{{ session('success') ?: session('success_criteria') ?: session('success_financial') ?: '' }}',
            isFlashSuccess: {{ session('success') || session('success_criteria') || session('success_financial') ? 'true' : 'false' }},
            levelStatusMessage: '',
            isLevelStatusSuccess: true,
            // Level Modal State
            levelModalType: 'difficulty',
            levelToEdit: null,
            currentLevel: { id: null, name: '', value: 1, color: '#cccccc' },
            levelFormErrors: {}
         })"
         x-init="initComponent()"
         class="py-8 px-4 sm:px-6 lg:px-8 bg-gray-100 min-h-screen">

        {{-- Header Halaman --}}
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Pengaturan Proyek: <span class="font-bold">{{ $project->name }}</span></h1>
        </div>

        {{-- Container Utama Pengaturan --}}
        <div class="bg-white shadow-md sm:rounded-lg overflow-hidden">

            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="-mb-px flex space-x-6 px-4 sm:px-6" aria-label="Tabs">
                    {{-- Tombol Tab Project --}}
                    <button @click="activeTab = 'project'" type="button"
                            :class="activeTab === 'project' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 transition-colors duration-150" :class="activeTab === 'project' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        Info Proyek
                    </button>
                    {{-- Tombol Tab Finansial --}}
                    <button @click="activeTab = 'financial'" type="button"
                            :class="activeTab === 'financial' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 transition-colors duration-150" :class="activeTab === 'financial' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0c-1.657 0-3-.895-3-2s1.343-2 3-2 3-.895 3-2 1.343-2 3-2m0 8c1.11 0 2.08-.402 2.599-1M12 16v1m0-1v-4m0 4H9m3 0h3m-3 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" /></svg>
                        Finansial & Gaji
                    </button>
                    {{-- Tombol Tab Kriteria --}}
                    <button @click="activeTab = 'criteria'" type="button"
                            :class="activeTab === 'criteria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 transition-colors duration-150" :class="activeTab === 'criteria' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 14h.01M12 17h.01M15 17h.01M9 10h.01M12 10h.01M15 10h.01M3 4a1 1 0 011-1h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V4z" /></svg>
                        Kriteria WSM
                    </button>
                </nav>
            </div>

            {{-- Tab Content Area --}}
            <div class="p-5 sm:p-8">

                {{-- Flash Message & Error Display --}}
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
                </div>

                {{-- ========================== --}}
                {{-- 1. Tab Info Proyek        --}}
                {{-- ========================== --}}
                <div x-show="activeTab === 'project'" x-transition.opacity>
                    <form action="{{ route('projects.pengaturan.info.update', $project) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Informasi Dasar Proyek</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Proyek</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach(['open', 'in_progress', 'completed', 'cancelled'] as $s)
                                        <option value="{{ $s }}" @selected(old('status', $project->status) == $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                    @endforeach
                                </select>
                                @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('end_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="budget" class="block text-sm font-medium text-gray-700 mb-1">Anggaran (Budget)</label>
                                <input type="number" name="budget" id="budget" step="any" min="0" value="{{ old('budget', $project->budget) }}" placeholder="Contoh: 5000000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('budget')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="wip_limits" class="block text-sm font-medium text-gray-700 mb-1">WIP Limit Kanban (Opsional)</label>
                                <input type="number" name="wip_limits" id="wip_limits" min="1" value="{{ old('wip_limits', $project->wip_limits) }}" placeholder="Maks task 'In Progress'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('wip_limits')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                             <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                             <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $project->description) }}</textarea>
                             @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                             <label for="categories" class="block text-sm font-medium text-gray-700 mb-1">Kategori Proyek</label>
                             <select name="categories[]" id="categories" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-40">
                                 @foreach($categories as $category)
                                     <option value="{{ $category->id }}" @selected(in_array($category->id, old('categories', $selectedCategories)))>{{ $category->name }}</option>
                                 @endforeach
                             </select>
                             <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</p>
                             @error('categories')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                             @error('categories.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

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
                            <form action="{{ route('projects.pengaturan.payment.update', $project) }}" method="POST"> {{-- Tetap submit ke route utama --}}
                                @csrf
                                @method('PATCH')
                                <div class="px-4 py-5 sm:p-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Metode Kalkulasi Pembayaran Utama</h3>
                                    <div class="mt-3 space-y-4">
                                        <p class="text-sm text-gray-600">Pilih cara utama perhitungan pembayaran task. Opsi bonus/lainnya tetap tersedia.</p>
                                        <fieldset>
                                            <legend class="sr-only">Metode Pembayaran</legend>
                                            <div class="space-y-3">
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5"><input id="payment_task_fin" name="payment_calculation_type" type="radio" value="task" @checked(old('payment_calculation_type', $project->payment_calculation_type) == 'task') class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"></div>
                                                    <div class="ml-3 text-sm"><label for="payment_task_fin" class="font-medium text-gray-700">Per Task (Default)</label><p class="text-gray-500 text-xs">Bayar berdasarkan nilai task yang dipilih saat upload.</p></div>
                                                </div>
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5"><input id="payment_termin_fin" name="payment_calculation_type" type="radio" value="termin" @checked(old('payment_calculation_type', $project->payment_calculation_type) == 'termin') class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"></div>
                                                    <div class="ml-3 text-sm"><label for="payment_termin_fin" class="font-medium text-gray-700">Per Termin/Periode</label><p class="text-gray-500 text-xs">Pilih periode tanggal, lalu pilih task relevan.</p></div>
                                                </div>
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5"><input id="payment_full_fin" name="payment_calculation_type" type="radio" value="full" @checked(old('payment_calculation_type', $project->payment_calculation_type) == 'full') class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"></div>
                                                    <div class="ml-3 text-sm"><label for="payment_full_fin" class="font-medium text-gray-700">Jumlah Tetap (Tanpa Task)</label><p class="text-gray-500 text-xs">Langsung masukkan jumlah nominal bayar (selain bonus).</p></div>
                                                </div>
                                            </div>
                                            @error('payment_calculation_type')<p class="mt-1 text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-100 text-right sm:px-6 border-t border-gray-200">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                        Simpan Metode Pembayaran
                                    </button>
                                </div>
                            </form>
                         </div>

                        {{-- Card Kelola Standar Upah --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <div class="px-4 py-5 sm:p-6">
                                  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                                      <div>
                                          <h3 class="text-lg font-medium text-gray-900">Standar Upah Proyek</h3>
                                          <p class="text-sm text-gray-600 mt-1">Definisikan harga dasar per task untuk setiap kategori pekerjaan.</p>
                                      </div>
                                      <a href="{{ route('projects.wage-standards.create', $project) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex-shrink-0">
                                          <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                          Tambah Standar
                                      </a>
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
                                                                   <a :href="`/projects/${project.id}/wage-standards/${standard.id}/edit`" class="text-indigo-600 hover:text-indigo-800 mr-4">Edit</a>
                                                                    <form :action="`/projects/${project.id}/wage-standards/${standard.id}`" method="POST" class="inline" @submit="if(!confirm('Yakin hapus standar ini?')) $event.preventDefault()">
                                                                        @csrf @method('DELETE')
                                                                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
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

                        {{-- Card Assign Standar Upah --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <div class="px-4 py-5 sm:p-6">
                                 <h3 class="text-lg font-medium text-gray-900">Assign Standar Upah Anggota Tim</h3>
                                 <p class="text-sm text-gray-600 mt-1 mb-4">Tentukan standar upah dasar yang berlaku untuk perhitungan gaji per task.</p>
                                 <div class="overflow-x-auto -mx-4 sm:-mx-6">
                                      <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                          <div class="shadow border-b border-gray-200 sm:rounded-lg">
                                              <table class="min-w-full divide-y divide-gray-200">
                                                  <thead class="bg-gray-50">
                                                       <tr>
                                                          <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                                                          <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/2">Standar Upah</th>
                                                          <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Simpan</th>
                                                      </tr>
                                                  </thead>
                                                  <tbody class="bg-white divide-y divide-gray-200">
                                                       <template x-if="members.length === 0">
                                                           <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 italic">Belum ada anggota tim aktif.</td></tr>
                                                       </template>
                                                      <template x-for="member in members" :key="member.id">
                                                          <tr>
                                                              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800" x-text="member.name"></td>
                                                              <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                                  <select name="wage_standard_id"
                                                                          @change="updateMemberWage(member.id, $event.target.value)"
                                                                          class="mt-1 block w-full max-w-xs pl-3 pr-10 py-2 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                                                                          :disabled="memberWageStatus[member.id] === 'saving'">
                                                                      <option value="">-- Tidak Ditentukan --</option>
                                                                      <template x-for="standard in wageStandards" :key="standard.id">
                                                                          {{-- Gunakan member.pivot.wage_standard_id untuk selected state --}}
                                                                          <option :value="standard.id" :selected="member.pivot && member.pivot.wage_standard_id == standard.id"
                                                                                  x-text="`${standard.job_category} (Rp ${Number(standard.task_price).toLocaleString('id-ID')})`">
                                                                          </option>
                                                                      </template>
                                                                  </select>
                                                              </td>
                                                              <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                                  {{-- Status Indicator --}}
                                                                  <template x-if="memberWageStatus[member.id] === 'saving'">
                                                                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 animate-pulse"> Menyimpan... </span>
                                                                  </template>
                                                                  <template x-if="memberWageStatus[member.id] === 'success'">
                                                                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"> Tersimpan </span>
                                                                  </template>
                                                                  <template x-if="memberWageStatus[member.id] === 'error'">
                                                                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"> Gagal </span>
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
                    <div class="space-y-8">

                        {{-- Card Bobot WSM --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <form action="{{ route('projects.settings.weights.update', $project) }}" method="POST"> {{-- Method POST, di-override @method --}}
                                 @csrf
                                 @method('PATCH')
                                 <div class="px-4 py-5 sm:p-6">
                                     <h3 class="text-lg font-medium leading-6 text-gray-900 mb-1">Bobot Kriteria WSM</h3>
                                     <p class="text-sm text-gray-600 mb-4">Total bobot kesulitan dan prioritas harus 100%.</p>
                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                         <div>
                                             <label for="difficulty_weight" class="block text-sm font-medium text-gray-700 mb-1">Bobot Kesulitan (%)</label>
                                             <input type="number" name="difficulty_weight" id="difficulty_weight" min="0" max="100" value="{{ old('difficulty_weight', $project->difficulty_weight) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                             @error('difficulty_weight')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                         </div>
                                         <div>
                                             <label for="priority_weight" class="block text-sm font-medium text-gray-700 mb-1">Bobot Prioritas (%)</label>
                                             <input type="number" name="priority_weight" id="priority_weight" min="0" max="100" value="{{ old('priority_weight', $project->priority_weight) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                             @error('priority_weight')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                         </div>
                                     </div>
                                     @error('weights')<p class="mt-1 text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
                                 </div>
                                  <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t border-gray-200">
                                       <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                           Simpan Bobot
                                       </button>
                                   </div>
                             </form>
                        </div>

                        {{-- Card Kelola Level --}}
                         <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                            <div class="px-4 py-5 sm:p-6">
                                 <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Kelola Level Kriteria</h3>
                                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-8"> {{-- Layout 2 kolom untuk level --}}
                                     {{-- Difficulty Levels Section --}}
                                     <div>
                                         <div class="flex justify-between items-center mb-3">
                                             <h4 class="text-md font-semibold text-gray-800">Tingkat Kesulitan</h4>
                                             <button @click="openLevelModal('difficulty')" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                                                              <button @click="editLevel('difficulty', level)" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Edit</button>
                                                              <form @submit.prevent="deleteLevel($event, 'difficulty', level.id)"> @csrf <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button> </form>
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

            </div> {{-- End Tab Content Area --}}

        </div> {{-- End Container Utama Pengaturan --}}

        {{-- Level Add/Edit Modal --}}
        <div x-show="isLevelModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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

    </div>

    {{-- Include SortableJS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    {{-- AlpineJS Logic (Sama seperti sebelumnya) --}}
    <script>
        function projectSettings(config) {
            return {
                project: config.project,
                categories: config.initialCategories,
                selectedCategories: config.initialSelectedCategories,
                wageStandards: config.initialWageStandards,
                members: config.initialMembers,
                difficultyLevels: config.initialDifficultyLevels,
                priorityLevels: config.initialPriorityLevels,
                projectUpdateUrl: config.projectUpdateUrl,
                weightsUpdateUrl: config.weightsUpdateUrl,
                levelUpdateOrderUrl: config.levelUpdateOrderUrl,
                memberWageUpdateUrlTemplate: config.memberWageUpdateUrlTemplate,
                csrfToken: config.csrfToken,
                activeTab: config.activeTab,
                // Pastikan state awal benar
                isLevelModalOpen: false, // <-- Pastikan false
                isLevelSubmitting: false,// <-- Pastikan false
                memberWageStatus: {},
                flashMessage: config.flashMessage,
                isFlashSuccess: config.isFlashSuccess,
                levelStatusMessage: '',
                isLevelStatusSuccess: true,
                levelModalType: 'difficulty',
                levelToEdit: null,
                currentLevel: { id: null, name: '', value: 1, color: '#cccccc' },
                levelFormErrors: {},

                get levelModalTypeLabel() { return this.levelModalType === 'difficulty' ? 'Kesulitan' : 'Prioritas'; },

                initComponent() {
                    this.initSortable();
                    if (this.flashMessage) {
                        setTimeout(() => { this.flashMessage = ''; }, 5000);
                    }
                    if ( @json(session('success_criteria')) ) this.activeTab = 'criteria';
                    if ( @json(session('success_financial')) ) this.activeTab = 'financial';
                    this.members.forEach(member => { this.$set(this.memberWageStatus, member.id, null); });
                     // Pastikan modal tidak terbuka saat init
                     this.isLevelModalOpen = false;
                     this.isLevelSubmitting = false;
                },

                openLevelModal(type, level = null) {
                    this.levelModalType = type;
                    this.levelToEdit = level;
                    this.levelFormErrors = {};
                    this.levelStatusMessage = '';
                    if (level) {
                        this.currentLevel = { ...level };
                    } else {
                        this.currentLevel = { id: null, name: '', value: 1, color: '#cccccc' };
                    }
                    this.isLevelModalOpen = true; // Buka modal HANYA saat fungsi ini dipanggil
                },
                closeLevelModal() { this.isLevelModalOpen = false; },
                editLevel(type, level) { this.openLevelModal(type, level); },
                submitLevelForm() {
                    this.isLevelSubmitting = true; // Set loading saat submit
                    // ... (Logika fetch POST/PATCH level) ...
                    // Pastikan di .finally() set isLevelSubmitting = false
                     let url, method, body = { ...this.currentLevel, _token: this.csrfToken };
                     if (this.levelToEdit) {
                         url = `/projects/${this.project.id}/settings/levels/${this.levelModalType}/${this.levelToEdit.id}`;
                         body._method = 'PATCH'; method = 'POST';
                     } else {
                         url = `/projects/${this.project.id}/settings/levels/${this.levelModalType}`;
                         method = 'POST'; if (!this.levelToEdit) delete body.id;
                     }
                     fetch(url, { method: method, headers: { /* headers */ 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify(body) })
                     .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                     .then(({ status, ok, body }) => {
                         if (ok && body.success) {
                             // ... (update list level) ...
                              const listKey = this.levelModalType + 'Levels';
                              if (this.levelToEdit) { const index = this[listKey].findIndex(l => l.id === this.levelToEdit.id); if (index !== -1) this[listKey].splice(index, 1, body.level || this.currentLevel); }
                              else { this[listKey].push(body.level || this.currentLevel); this.$nextTick(() => this.initSortable()); }
                             this.closeLevelModal(); this.showLevelFlash(body.message || 'Level disimpan.', true);
                         } else if (status === 422) { this.levelFormErrors = body.errors; this.showLevelFlash(body.message || 'Validasi gagal.', false); }
                         else { this.showLevelFlash(body.message || 'Gagal menyimpan.', false); }
                     })
                     .catch(err => { console.error(err); this.showLevelFlash('Request error.', false); })
                     .finally(() => { this.isLevelSubmitting = false; }); // <-- Pastikan ini ada
                },
                deleteLevel(event, type, levelId) {
                    // ... (Logika fetch DELETE level) ...
                    // Pastikan ada konfirmasi
                     event.preventDefault(); if (!confirm('Yakin hapus?')) return;
                     const url = `/projects/${this.project.id}/settings/levels/${type}/${levelId}`;
                     // ... fetch ...
                     fetch(url, { method: 'DELETE', headers: { /* headers */ 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken } })
                     .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                     .then(({ status, ok, body }) => {
                         if (ok && body.success) {
                             // ... (hapus dari list) ...
                              const listKey = type + 'Levels'; this[listKey] = this[listKey].filter(l => l.id !== levelId);
                             this.showLevelFlash(body.message || 'Level dihapus.', true);
                         } else { /* error handling */ this.showLevelFlash(body.message || 'Gagal hapus.', false); }
                     })
                     .catch(err => { /* error handling */ this.showLevelFlash('Request error.', false); });
                 },
                 showLevelFlash(message, success = true) {
                      this.levelStatusMessage = message;
                      this.isLevelStatusSuccess = success;
                      setTimeout(() => { this.levelStatusMessage = ''; }, 5000); // Waktu tampil lebih lama
                 },
                 initSortable() {
                     const self = this;
                     ['difficulty', 'priority'].forEach(type => {
                         const listEl = this.$refs[type + 'List']; // Nama ref diubah di HTML
                         if (listEl) {
                              if (listEl.sortableInstance) { try { listEl.sortableInstance.destroy(); } catch(e){} }
                              listEl.sortableInstance = new Sortable(listEl, {
                                  animation: 150, handle: '.cursor-move', // Target handle baru
                                  ghostClass: 'sortable-ghost',
                                  onEnd: (evt) => {
                                      const orderedIds = Array.from(evt.to.children).map(item => item.dataset.id).filter(id => id);
                                      if (orderedIds.length > 0) {
                                          const listKey = type + 'Levels';
                                          // Update local array order immediately
                                          self[listKey] = orderedIds.map(id => self[listKey].find(l => l.id == id));
                                          self.saveOrder(type, orderedIds);
                                      }
                                  }
                              });
                         }
                     });
                 },
                saveOrder(levelType, orderedIds) {
                    // ... (Logika fetch PATCH order) ...
                    fetch(this.levelUpdateOrderUrl, { method: 'POST', headers: { /* headers */ 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify({ _method: 'PATCH', level_type: levelType, ordered_ids: orderedIds }) })
                    .then(res => res.json())
                    .then(data => { if (data.success) this.showLevelFlash('Urutan disimpan.', true); else this.showLevelFlash(data.message || 'Gagal simpan urutan.', false); })
                    .catch(err => { this.showLevelFlash('Request error urutan.', false); });
                 },
                 updateMemberWage(memberId, wageStandardId) { // Argumen dibalik agar sesuai event select
                     // ... (Logika fetch PATCH member wage) ...
                      wageStandardId = wageStandardId === "" ? null : wageStandardId; // Handle string kosong
                      const url = this.memberWageUpdateUrlTemplate.replace(':userId', memberId);
                      this.$set(this.memberWageStatus, memberId, 'saving');
                      fetch(url, { method: 'PATCH', headers: { /* headers */ 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify({ wage_standard_id: wageStandardId }) })
                      .then(res => res.json().then(data => ({ ok: res.ok, body: data })))
                      .then(({ ok, body }) => {
                          if (ok && body.success) {
                              this.$set(this.memberWageStatus, memberId, 'success');
                              // Update data lokal juga
                              const index = this.members.findIndex(m => m.id === memberId);
                              if (index !== -1) this.$set(this.members[index].pivot, 'wage_standard_id', wageStandardId);
                              setTimeout(() => this.$set(this.memberWageStatus, memberId, null), 2500);
                          } else { this.$set(this.memberWageStatus, memberId, 'error'); this.showFlashMessage(body.message || 'Gagal update.', false); setTimeout(() => this.$set(this.memberWageStatus, memberId, null), 3500); }
                      })
                      .catch(err => { this.$set(this.memberWageStatus, memberId, 'error'); this.showFlashMessage('Request error.', false); setTimeout(() => this.$set(this.memberWageStatus, memberId, null), 3500); });
                 },
                 showFlashMessage(message, success = true) {
                     this.flashMessage = message;
                     this.isFlashSuccess = success;
                     this.levelStatusMessage = '';
                     setTimeout(() => { this.flashMessage = ''; }, 5000);
                 },
            }
        }
    </script>
    {{-- === HAPUS BLOK @push('styles') === --}}
</x-app-layout>