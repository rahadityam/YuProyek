<div> {{-- Root element wajib --}}
    {{-- Container utama dengan padding (bisa dihapus jika sudah di layout) --}}
    <div class="py-8 px-4 sm:px-6 lg:px-8 bg-gray-100 min-h-screen">

        {{-- Header Halaman --}}
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Pengaturan Proyek: <span class="font-bold">{{ $project->name }}</span></h1>
        </div>

        {{-- Container Utama Pengaturan --}}
        <div class="bg-white shadow-md sm:rounded-lg overflow-hidden">

            {{-- Tab Navigation (Gunakan wire:click) --}}
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="-mb-px flex space-x-6 px-4 sm:px-6" aria-label="Tabs">
                    <button wire:click="switchTab('project')" type="button"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group
                                   {{ $activeTab === 'project' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <svg class="h-4 w-4 mr-1.5 transition-colors duration-150 {{ $activeTab === 'project' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}">...</svg>
                        Info Proyek
                    </button>
                    <button wire:click="switchTab('financial')" type="button"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group
                                   {{ $activeTab === 'financial' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                         <svg class="h-4 w-4 mr-1.5 transition-colors duration-150 {{ $activeTab === 'financial' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}">...</svg>
                        Finansial & Gaji
                    </button>
                    <button wire:click="switchTab('criteria')" type="button"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group
                                   {{ $activeTab === 'criteria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                         <svg class="h-4 w-4 mr-1.5 transition-colors duration-150 {{ $activeTab === 'criteria' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}">...</svg>
                        Kriteria WSM
                    </button>
                </nav>
            </div>

            {{-- Tab Content Area --}}
            <div class="p-5 sm:p-8">

                {{-- Flash Message (Contoh menggunakan event browser) --}}
                <div x-data="{ show: false, message: '', success: true }"
                     x-init="
                         @this.on('show-flash', (eventData) => {
                             message = eventData.message;
                             success = eventData.success;
                             show = true;
                             setTimeout(() => show = false, 5000);
                         });
                         // Tampilkan flash dari session PHP saat load awal
                         let initialFlash = '{{ session('success_info') ?: session('success_financial') ?: session('success_criteria') ?: session('general_error') ?: '' }}';
                         let initialSuccess = {{ session('success_info') || session('success_financial') || session('success_criteria') ? 'true' : 'false' }};
                         if (initialFlash) {
                             message = initialFlash;
                             success = initialSuccess;
                             show = true;
                             setTimeout(() => show = false, 5000);
                         }
                     "
                     x-show="show" x-cloak
                     class="mb-6 border px-4 py-3 rounded relative" role="alert"
                     :class="success ? 'bg-green-50 border-green-300 text-green-700' : 'bg-red-50 border-red-300 text-red-700'"
                     x-transition>
                    <strong class="font-semibold" x-text="success ? 'Sukses!' : 'Error!'"></strong>
                    <span class="block sm:inline ml-1" x-text="message"></span>
                    <button @click="show = false" type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-xl font-semibold leading-none hover:text-opacity-75">×</button>
                </div>
                {{-- Display standard validation errors --}}
                @if($errors->any())
                     <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded relative" role="alert">
                          <p class="font-semibold">Oops! Terjadi kesalahan:</p>
                          <ul class="list-disc list-inside mt-1 text-sm">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                     </div>
                @endif

                {{-- ========================== --}}
                {{-- 1. Tab Info Proyek        --}}
                {{-- ========================== --}}
                <div wire:key="tab-project" x-show="activeTab === 'project'" x-transition.opacity>
                     {{-- Form ini tetap mengarah ke Controller biasa karena tidak perlu interaksi Livewire --}}
                    <form action="{{ route('projects.pengaturan.info.update', $project) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Informasi Dasar Proyek</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Proyek</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required class="input-field @error('name') input-error @enderror">
                                @error('name')<p class="input-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" required class="input-field @error('status') input-error @enderror">
                                    @foreach(['open', 'in_progress', 'completed', 'cancelled'] as $s)
                                        <option value="{{ $s }}" @selected(old('status', $project->status) == $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                    @endforeach
                                </select>
                                @error('status')<p class="input-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" class="input-field @error('start_date') input-error @enderror">
                                @error('start_date')<p class="input-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}" class="input-field @error('end_date') input-error @enderror">
                                @error('end_date')<p class="input-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="budget" class="block text-sm font-medium text-gray-700 mb-1">Anggaran (Budget)</label>
                                <input type="number" name="budget" id="budget" step="any" min="0" value="{{ old('budget', $project->budget) }}" placeholder="Contoh: 5000000" class="input-field @error('budget') input-error @enderror">
                                @error('budget')<p class="input-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="wip_limits" class="block text-sm font-medium text-gray-700 mb-1">WIP Limit Kanban (Opsional)</label>
                                <input type="number" name="wip_limits" id="wip_limits" min="1" value="{{ old('wip_limits', $project->wip_limits) }}" placeholder="Maks task 'In Progress'" class="input-field @error('wip_limits') input-error @enderror">
                                @error('wip_limits')<p class="input-error-msg">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                             <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                             <textarea name="description" id="description" rows="4" class="input-field @error('description') input-error @enderror">{{ old('description', $project->description) }}</textarea>
                             @error('description')<p class="input-error-msg">{{ $message }}</p>@enderror
                        </div>
                        <div>
                             <label for="categories" class="block text-sm font-medium text-gray-700 mb-1">Kategori Proyek</label>
                              {{-- Menggunakan wire:model untuk selectedCategories --}}
                             <select name="categories[]" id="categories" multiple wire:model="selectedCategories" class="input-field h-40 @error('categories') input-error @enderror @error('categories.*') input-error @enderror">
                                 @foreach($allCategories as $category) {{-- Loop dari properti Livewire --}}
                                     <option value="{{ $category->id }}">{{ $category->name }}</option>
                                 @endforeach
                             </select>
                             <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</p>
                             @error('categories')<p class="input-error-msg">{{ $message }}</p>@enderror
                             @error('categories.*')<p class="input-error-msg">{{ $message }}</p>@enderror
                        </div>
                        <div class="pt-5 border-t border-gray-200 mt-6">
                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary"> Simpan Informasi Proyek </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ========================== --}}
                {{-- 2. Tab Finansial & Gaji   --}}
                {{-- ========================== --}}
                <div wire:key="tab-financial" x-show="activeTab === 'financial'" x-transition.opacity>
                    <div class="space-y-8">

                        {{-- Card Metode Kalkulasi Pembayaran (Form biasa) --}}
                        <div class="bg-gray-50 shadow sm:rounded-lg border border-gray-200">
                            <form action="{{ route('projects.pengaturan.payment.update', $project) }}" method="POST">
                                @csrf @method('PATCH')
                                <div class="px-4 py-5 sm:p-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Metode Kalkulasi Pembayaran Utama</h3>
                                    <div class="mt-3 space-y-4">
                                        <p class="text-sm text-gray-600">Pilih cara utama perhitungan pembayaran.</p>
                                        <fieldset> <legend class="sr-only">Metode Pembayaran</legend>
                                            <div class="space-y-3">
                                                <div class="flex items-start"> <div class="flex items-center h-5"><input id="payment_task_fin" name="payment_calculation_type" type="radio" value="task" @checked(old('payment_calculation_type', $project->payment_calculation_type) == 'task') class="radio-input"></div> <div class="ml-3 text-sm"><label for="payment_task_fin" class="font-medium text-gray-700">Per Task (Default)</label><p class="text-gray-500 text-xs">Bayar berdasarkan task yang dipilih.</p></div> </div>
                                                <div class="flex items-start"> <div class="flex items-center h-5"><input id="payment_termin_fin" name="payment_calculation_type" type="radio" value="termin" @checked(old('payment_calculation_type', $project->payment_calculation_type) == 'termin') class="radio-input"></div> <div class="ml-3 text-sm"><label for="payment_termin_fin" class="font-medium text-gray-700">Per Termin/Periode</label><p class="text-gray-500 text-xs">Definisikan termin di bawah, lalu pilih task per termin.</p></div> </div>
                                                <div class="flex items-start"> <div class="flex items-center h-5"><input id="payment_full_fin" name="payment_calculation_type" type="radio" value="full" @checked(old('payment_calculation_type', $project->payment_calculation_type) == 'full') class="radio-input"></div> <div class="ml-3 text-sm"><label for="payment_full_fin" class="font-medium text-gray-700">Jumlah Tetap</label><p class="text-gray-500 text-xs">Langsung masukkan jumlah bayar.</p></div> </div>
                                            </div>
                                            @error('payment_calculation_type')<p class="input-error-msg mt-2">{{ $message }}</p>@enderror
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-100 text-right sm:px-6 border-t"> <button type="submit" class="btn-primary"> Simpan Metode </button> </div>
                            </form>
                         </div>

                        {{-- Card Kelola Termin (Livewire) --}}
                        {{-- Tampilkan hanya jika metode proyek adalah termin --}}
                         @if($project->payment_calculation_type === 'termin')
                         <div class="bg-gray-50 shadow sm:rounded-lg border border-gray-200" wire:key="payment-terms-section">
                            {{-- Form ini menargetkan Controller biasa, tapi inputnya dikelola Livewire --}}
                            <form action="{{ route('projects.settings.terms.update', $project) }}" method="POST">
                                @csrf @method('PATCH')
                                <div class="px-4 py-5 sm:p-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-1">Kelola Termin Pembayaran</h3>
                                    <p class="text-sm text-gray-600 mb-4">Definisikan nama dan periode setiap termin.</p>
                                    <div class="space-y-4" wire:sortable="updateTermOrder"> {{-- Contoh wire:sortable jika pakai plugin --}}
                                        @forelse($paymentTerms as $index => $term)
                                            {{-- Tampilkan hanya jika tidak ditandai hapus di frontend --}}
                                            <div wire:key="term-{{ $term['id'] ?? $index }}"
                                                 class="grid grid-cols-1 sm:grid-cols-10 gap-x-4 gap-y-2 items-end border-b pb-3 border-gray-200 {{ $term['markedForDeletion'] ? 'opacity-50' : '' }}">
                                                {{-- Hidden ID for existing terms --}}
                                                @if($term['id'])
                                                <input type="hidden" name="terms[{{ $index }}][id]" value="{{ $term['id'] }}">
                                                @endif
                                                {{-- Name --}}
                                                <div class="sm:col-span-3">
                                                    <label for="term_name_{{ $index }}" class="block text-xs font-medium text-gray-600">Nama Termin</label>
                                                    {{-- Gunakan wire:model.defer --}}
                                                    <input type="text" id="term_name_{{ $index }}" name="terms[{{ $index }}][name]"
                                                           wire:model.defer="paymentTerms.{{ $index }}.name" required placeholder="Contoh: Termin 1"
                                                           class="mt-1 block w-full input-field @error('paymentTerms.'.$index.'.name') input-error @enderror"
                                                           {{ $term['markedForDeletion'] ? 'disabled' : '' }}>
                                                    @error('paymentTerms.'.$index.'.name') <span class="input-error-msg">{{ $message }}</span> @enderror
                                                </div>
                                                {{-- Start Date --}}
                                                <div class="sm:col-span-3">
                                                    <label for="term_start_{{ $index }}" class="block text-xs font-medium text-gray-600">Tgl Mulai</label>
                                                    <input type="date" id="term_start_{{ $index }}" name="terms[{{ $index }}][start_date]"
                                                           wire:model.defer="paymentTerms.{{ $index }}.start_date" required
                                                           class="mt-1 block w-full input-field @error('paymentTerms.'.$index.'.start_date') input-error @enderror"
                                                           {{ $term['markedForDeletion'] ? 'disabled' : '' }}>
                                                    @error('paymentTerms.'.$index.'.start_date') <span class="input-error-msg">{{ $message }}</span> @enderror
                                                </div>
                                                {{-- End Date --}}
                                                <div class="sm:col-span-3">
                                                    <label for="term_end_{{ $index }}" class="block text-xs font-medium text-gray-600">Tgl Akhir</label>
                                                    <input type="date" id="term_end_{{ $index }}" name="terms[{{ $index }}][end_date]"
                                                           wire:model.defer="paymentTerms.{{ $index }}.end_date" required
                                                           class="mt-1 block w-full input-field @error('paymentTerms.'.$index.'.end_date') input-error @enderror"
                                                           {{ $term['markedForDeletion'] ? 'disabled' : '' }}>
                                                    @error('paymentTerms.'.$index.'.end_date') <span class="input-error-msg">{{ $message }}</span> @enderror
                                                </div>
                                                {{-- Delete Button --}}
                                                <div class="sm:col-span-1 flex justify-end">
                                                     {{-- Hidden input untuk delete flag (wajib ada!) --}}
                                                     <input type="hidden" name="terms[{{ $index }}][delete]" value="{{ $term['markedForDeletion'] ? '1' : '0' }}">
                                                     <button type="button" wire:click="removeTerm({{ $index }})"
                                                             class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500" title="Hapus Termin">
                                                         <svg class="h-4 w-4">...</svg> {{-- Icon hapus --}}
                                                     </button>
                                                </div>
                                                {{-- Tampilkan pesan jika ditandai hapus --}}
                                                 @if($term['markedForDeletion'])
                                                    <div class="sm:col-span-10 text-xs text-red-600 italic text-right">Akan dihapus saat disimpan</div>
                                                 @endif
                                            </div>
                                        @empty
                                            {{-- Tidak perlu ditampilkan jika loop kosong --}}
                                        @endforelse
                                    </div>
                                    {{-- Tombol Tambah Termin --}}
                                    <div class="mt-4">
                                        <button type="button" wire:click="addTerm" class="btn-secondary btn-sm">
                                            <svg class="-ml-0.5 mr-1 h-3.5 w-3.5">...</svg> Tambah Termin
                                        </button>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-100 text-right sm:px-6 border-t">
                                     <span wire:loading wire:target="savePaymentTerms" class="text-sm text-gray-500 italic mr-2">Menyimpan...</span>
                                     {{-- Ganti submit biasa dengan wire:click --}}
                                    <button type="submit" {{-- Ganti wire:click="savePaymentTerms" dengan submit biasa --}}
                                            wire:loading.attr="disabled"
                                            class="btn-primary" >
                                        Simpan Data Termin
                                    </button>
                                </div>
                            </form>
                         </div>
                         @else
                         {{-- Pesan jika tipe bukan termin --}}
                         <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded text-sm">
                             Metode pembayaran saat ini bukan 'Termin'. Pengaturan termin tidak tersedia. Ubah metode pembayaran di atas jika ingin mengelola termin.
                         </div>
                         @endif


                        {{-- Card Kelola Standar Upah (Link ke halaman Livewire) --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <div class="px-4 py-5 sm:p-6">
                                  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                                      <div>
                                          <h3 class="text-lg font-medium text-gray-900">Standar Upah Proyek</h3>
                                          <p class="text-sm text-gray-600 mt-1">Definisikan harga dasar per task.</p>
                                      </div>
                                      {{-- Link ke halaman Livewire WageStandardIndex --}}
                                      <a href="{{ route('projects.wage-standards.index', $project) }}" wire:navigate class="btn-secondary btn-sm flex-shrink-0">
                                          Kelola Standar Upah
                                      </a>
                                  </div>
                                   {{-- Preview standar upah (opsional) --}}
                                   @if($allWageStandards->count() > 0)
                                   <div class="text-sm text-gray-500">
                                       Beberapa standar:
                                       <ul class="list-disc list-inside ml-4">
                                           @foreach($allWageStandards->take(3) as $std)
                                                <li>{{ $std->job_category }}: {{ number_format($std->task_price, 0, ',', '.') }}</li>
                                           @endforeach
                                           @if($allWageStandards->count() > 3)...@endif
                                       </ul>
                                   </div>
                                   @else
                                   <p class="text-sm text-gray-500 italic">Belum ada standar upah.</p>
                                   @endif
                             </div>
                        </div>

                        {{-- Card Assign Standar Upah (Interaksi AJAX ke Controller) --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200" wire:key="assign-wage-section">
                              <div class="px-4 py-5 sm:p-6">
                                 <h3 class="text-lg font-medium text-gray-900">Assign Standar Upah Anggota Tim</h3>
                                 <p class="text-sm text-gray-600 mt-1 mb-4">Tentukan standar upah dasar untuk perhitungan gaji per task.</p>
                                 <div class="overflow-x-auto -mx-4 sm:-mx-6">
                                      <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                          <div class="shadow border-b border-gray-200 sm:rounded-lg">
                                              <table class="min-w-full divide-y divide-gray-200">
                                                  <thead class="bg-gray-50"> <tr> <th scope="col" class="th-cell">Nama Anggota</th> <th scope="col" class="th-cell w-1/2">Standar Upah</th> <th scope="col" class="th-cell">Status Simpan</th> </tr> </thead>
                                                  <tbody class="bg-white divide-y divide-gray-200">
                                                       @if($members->isEmpty())
                                                           <tr><td colspan="3" class="td-cell text-center italic">Belum ada anggota tim aktif.</td></tr>
                                                       @endif
                                                      @foreach($members as $member)
                                                          <tr wire:key="member-wage-{{ $member->id }}">
                                                              <td class="td-cell font-medium text-gray-800">{{ $member->name }}</td>
                                                              <td class="td-cell">
                                                                  {{-- Select dengan event @change untuk AJAX --}}
                                                                  <select name="wage_standard_id"
                                                                          id="member_wage_{{ $member->id }}"
                                                                          {{-- Panggil method Livewire ATAU Javascript untuk AJAX --}}
                                                                          @change="$wire.updateMemberWage({{ $member->id }}, $event.target.value)"
                                                                          {{-- Atau panggil fungsi JS jika pakai Alpine + fetch --}}
                                                                          {{-- @change="updateMemberWageViaJS({{ $member->id }}, $event.target.value)" --}}
                                                                          class="mt-1 block w-full max-w-xs input-field-sm py-1"
                                                                          wire:loading.attr="disabled" wire:target="updateMemberWage({{ $member->id }}, $event.target.value)">
                                                                      <option value="">-- Tidak Ditentukan --</option>
                                                                      @foreach($allWageStandards as $standard)
                                                                          <option value="{{ $standard->id }}" @selected($member->pivot->wage_standard_id == $standard->id)>
                                                                              {{ $standard->job_category }} ({{ number_format($standard->task_price, 0, ',', '.') }})
                                                                          </option>
                                                                      @endforeach
                                                                  </select>
                                                              </td>
                                                              <td class="td-cell">
                                                                   {{-- Status Indicator Livewire --}}
                                                                    <div wire:loading wire:target="updateMemberWage({{ $member->id }}, $event.target.value)">
                                                                        <span class="badge-yellow animate-pulse">Menyimpan...</span>
                                                                    </div>
                                                                    {{-- Status dari state (jika diperlukan feedback lebih lama) --}}
                                                                    {{-- <span x-show="memberWageStatus[{{ $member->id }}] === 'success'" class="badge-green">Tersimpan</span> --}}
                                                                    {{-- <span x-show="memberWageStatus[{{ $member->id }}] === 'error'" class="badge-red">Gagal</span> --}}
                                                              </td>
                                                          </tr>
                                                      @endforeach
                                                  </tbody>
                                              </table>
                                          </div>
                                      </div>
                                  </div>
                                  <p class="text-xs text-gray-500 mt-3">Catatan: Perubahan standar upah berlaku untuk perhitungan task baru.</p>
                             </div>
                        </div>

                    </div> {{-- End space-y-8 --}}
                </div> {{-- End Financial Tab --}}

                {{-- ========================== --}}
                {{-- 3. Tab Kriteria WSM       --}}
                {{-- ========================== --}}
                <div wire:key="tab-criteria" x-show="activeTab === 'criteria'" x-transition.opacity>
                    <div class="space-y-8">
                        {{-- Card Bobot WSM (Form Biasa) --}}
                        <div class="bg-white shadow sm:rounded-lg border border-gray-200">
                             <form action="{{ route('projects.settings.weights.update', $project) }}" method="POST">
                                 @csrf @method('PATCH')
                                 <div class="px-4 py-5 sm:p-6">
                                     <h3 class="text-lg font-medium leading-6 text-gray-900 mb-1">Bobot Kriteria WSM</h3>
                                     <p class="text-sm text-gray-600 mb-4">Total bobot harus 100.</p>
                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                         <div> <label for="difficulty_weight" class="label-text">Bobot Kesulitan (%)</label> <input type="number" name="difficulty_weight" id="difficulty_weight" min="0" max="100" value="{{ old('difficulty_weight', $project->difficulty_weight) }}" required class="input-field @error('difficulty_weight') input-error @enderror"> @error('difficulty_weight')<p class="input-error-msg">{{ $message }}</p>@enderror </div>
                                         <div> <label for="priority_weight" class="label-text">Bobot Prioritas (%)</label> <input type="number" name="priority_weight" id="priority_weight" min="0" max="100" value="{{ old('priority_weight', $project->priority_weight) }}" required class="input-field @error('priority_weight') input-error @enderror"> @error('priority_weight')<p class="input-error-msg">{{ $message }}</p>@enderror </div>
                                     </div>
                                     @error('weights')<p class="input-error-msg mt-2">{{ $message }}</p>@enderror
                                 </div>
                                  <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t"> <button type="submit" class="btn-primary"> Simpan Bobot </button> </div>
                             </form>
                        </div>

                        {{-- Card Kelola Level (Interaksi via Alpine/AJAX ke Controller) --}}
                         <div class="bg-white shadow sm:rounded-lg border border-gray-200"
                              x-data="levelManagerData({
                                difficultyLevels: {{ Js::from($difficultyLevels) }},
                                priorityLevels: {{ Js::from($priorityLevels) }},
                                projectId: {{ $project->id }},
                                csrfToken: '{{ csrf_token() }}'
                              })">
                            <div class="px-4 py-5 sm:p-6">
                                 <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Kelola Level Kriteria</h3>
                                 {{-- Status Message untuk Level --}}
                                 <template x-if="levelStatusMessage">
                                     <div x-text="levelStatusMessage"
                                        :class="isLevelStatusSuccess ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'"
                                        class="mb-4 border px-4 py-3 rounded relative text-sm" role="alert">
                                     </div>
                                 </template>
                                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                     {{-- Difficulty Levels Section --}}
                                     <div>
                                         <div class="flex justify-between items-center mb-3">
                                             <h4 class="text-md font-semibold text-gray-800">Tingkat Kesulitan</h4>
                                             {{-- Tombol trigger Alpine modal --}}
                                             <button @click="openLevelModal('difficulty')" class="btn-primary btn-sm"> <svg class="btn-icon-left">...</svg> Tambah </button>
                                         </div>
                                         <div class="border border-gray-200 rounded-md overflow-hidden">
                                             <ul x-ref="difficultyList" role="list" class="divide-y divide-gray-200">
                                                 <template x-for="level in difficultyLevels" :key="level.id">
                                                      <li :data-id="level.id" class="list-item-hover cursor-grab">
                                                          <div class="flex items-center flex-grow min-w-0 mr-4">
                                                              <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0 cursor-move">...</svg>
                                                              <span class="inline-block w-4 h-4 rounded-md border border-gray-400 flex-shrink-0" :style="{ backgroundColor: level.color }"></span>
                                                              <div class="min-w-0 ml-3">
                                                                  <span class="font-medium text-gray-800 block truncate text-sm" x-text="level.name"></span>
                                                                  <span class="text-xs text-gray-500" x-text="'Nilai: ' + level.value"></span>
                                                              </div>
                                                          </div>
                                                          <div class="flex space-x-3 flex-shrink-0 items-center">
                                                              <button @click="editLevel('difficulty', level)" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Edit</button>
                                                              {{-- Form delete trigger JS --}}
                                                              <form @submit.prevent="deleteLevel($event, 'difficulty', level.id)"> @csrf <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button> </form>
                                                          </div>
                                                      </li>
                                                 </template>
                                                 <template x-if="difficultyLevels.length === 0"> <li class="list-item-empty">Belum ada level.</li> </template>
                                             </ul>
                                         </div>
                                         <p class="text-xs text-gray-500 mt-2">* Drag & drop untuk mengubah urutan.</p>
                                     </div>
                                     {{-- Priority Levels Section --}}
                                     <div>
                                          <div class="flex justify-between items-center mb-3">
                                             <h4 class="text-md font-semibold text-gray-800">Prioritas</h4>
                                             <button @click="openLevelModal('priority')" class="btn-primary btn-sm"> <svg class="btn-icon-left">...</svg> Tambah </button>
                                          </div>
                                          <div class="border border-gray-200 rounded-md overflow-hidden">
                                              <ul x-ref="priorityList" role="list" class="divide-y divide-gray-200">
                                                  <template x-for="level in priorityLevels" :key="level.id">
                                                       <li :data-id="level.id" class="list-item-hover cursor-grab">
                                                          <div class="flex items-center flex-grow min-w-0 mr-4">
                                                              <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0 cursor-move">...</svg>
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
                                                  <template x-if="priorityLevels.length === 0"> <li class="list-item-empty">Belum ada level.</li> </template>
                                              </ul>
                                          </div>
                                          <p class="text-xs text-gray-500 mt-2">* Drag & drop untuk mengubah urutan.</p>
                                     </div>
                                </div>
                            </div>
                            {{-- Modal Level (Dikelola Alpine) --}}
                            @include('projects.partials.level-modal') {{-- Buat partial terpisah untuk modal --}}
                         </div>

                    </div> {{-- End space-y-8 --}}
                </div> {{-- End Criteria Tab --}}

            </div> {{-- End Tab Content Area --}}
        </div> {{-- End Container Utama Pengaturan --}}
    </div> {{-- End Padding Utama --}}

    {{-- Script Alpine untuk Level Manager dan SortableJS --}}
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <script>
        function levelManagerData(config) {
             return {
                 difficultyLevels: config.difficultyLevels || [],
                 priorityLevels: config.priorityLevels || [],
                 projectId: config.projectId,
                 csrfToken: config.csrfToken,
                 // Modal state
                 isLevelModalOpen: false,
                 isLevelSubmitting: false,
                 levelModalType: 'difficulty',
                 levelToEdit: null,
                 currentLevel: { id: null, name: '', value: 1, color: '#cccccc' },
                 levelFormErrors: {},
                 // Status message
                 levelStatusMessage: '',
                 isLevelStatusSuccess: true,
                 // Sortable instances
                 difficultySortable: null,
                 prioritySortable: null,

                 get levelModalTypeLabel() { return this.levelModalType === 'difficulty' ? 'Kesulitan' : 'Prioritas'; },

                 init() {
                     this.$nextTick(() => {
                         this.initSortable();
                     });
                      // Listener untuk flash message dari Livewire (jika ada event browser)
                     window.addEventListener('show-flash', event => {
                        this.showLevelFlash(event.detail.message, event.detail.success);
                     });
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
                     let url, method, body = { ...this.currentLevel }; // _token tidak perlu di body JSON

                     if (this.levelToEdit) {
                         url = `/projects/${this.projectId}/settings/levels/${this.levelModalType}/${this.levelToEdit.id}`;
                         method = 'PATCH'; // Kirim sebagai PATCH
                     } else {
                         url = `/projects/${this.projectId}/settings/levels/${this.levelModalType}`;
                         method = 'POST';
                         if (!this.levelToEdit) delete body.id; // Hapus ID jika create
                     }

                     fetch(url, {
                         method: method, // Langsung set method
                         headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                         body: JSON.stringify(body)
                     })
                     .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
                     .then(({ status, ok, body }) => {
                         if (ok && body.success) {
                              const listKey = this.levelModalType + 'Levels';
                              if (this.levelToEdit) { const index = this[listKey].findIndex(l => l.id === this.levelToEdit.id); if (index !== -1) this[listKey].splice(index, 1, body.level); }
                              else { this[listKey].push(body.level); }
                              // Re-init sortable mungkin diperlukan jika item baru ditambahkan dan pakai plugin livewire-sortable
                              // this.$nextTick(() => this.initSortable());
                              this.closeLevelModal(); this.showLevelFlash(body.message || 'Level disimpan.', true);
                         } else if (status === 422) { this.levelFormErrors = body.errors; this.showLevelFlash(body.message || 'Validasi gagal.', false); }
                         else { this.showLevelFlash(body.message || 'Gagal menyimpan.', false); }
                     })
                     .catch(err => { console.error("Level Submit Error:", err); this.showLevelFlash('Request error.', false); })
                     .finally(() => { this.isLevelSubmitting = false; });
                 },

                 deleteLevel(event, type, levelId) {
                     event.preventDefault(); if (!confirm('Yakin hapus level ini?')) return;
                     const url = `/projects/${this.projectId}/settings/levels/${type}/${levelId}`;
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
                     .catch(err => { console.error("Level Delete Error:", err); this.showLevelFlash('Request error.', false); })
                      .finally(() => { if(button) button.disabled = false; });
                 },

                 showLevelFlash(message, success = true) {
                      this.levelStatusMessage = message;
                      this.isLevelStatusSuccess = success;
                      setTimeout(() => { this.levelStatusMessage = ''; }, 4000);
                 },

                 initSortable() {
                     const self = this;
                     ['difficulty', 'priority'].forEach(type => {
                         const listEl = this.$refs[type + 'List'];
                         if (listEl) {
                              // Hancurkan instance lama jika ada
                              if (self[type + 'Sortable']) { try { self[type + 'Sortable'].destroy(); } catch(e){} }
                              // Buat instance baru
                              self[type + 'Sortable'] = new Sortable(listEl, {
                                  animation: 150, handle: '.cursor-move', ghostClass: 'sortable-ghost',
                                  onEnd: (evt) => {
                                      // Ambil ID dari data-id attribute
                                      const orderedIds = Array.from(evt.to.children)
                                            .map(item => item.dataset ? parseInt(item.dataset.id) : null)
                                            .filter(id => id !== null);
                                      if (orderedIds.length > 0) {
                                          self.updateLevelOrder(type, orderedIds);
                                      }
                                  }
                              });
                         }
                     });
                 },

                 updateLevelOrder(levelType, orderedIds) {
                     // Update urutan array lokal secara optimis
                     const listKey = levelType + 'Levels';
                     this[listKey] = orderedIds.map(id => this[listKey].find(l => l.id === id));

                     // Kirim request AJAX ke backend
                      fetch(`/projects/${this.projectId}/settings/levels/order`, {
                          method: 'POST', // Method POST tapi pakai _method spoofing
                          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                          body: JSON.stringify({ _method: 'PATCH', level_type: levelType, ordered_ids: orderedIds })
                      })
                      .then(res => res.json())
                      .then(data => {
                          if (data.success) this.showLevelFlash(data.message || 'Urutan disimpan.', true);
                          else {
                              this.showLevelFlash(data.message || 'Gagal simpan urutan.', false);
                              // Rollback urutan lokal jika gagal? (opsional)
                              this.loadDynamicData(); // Load ulang dari server
                          }
                      })
                      .catch(err => {
                           console.error("Order Update Error:", err);
                           this.showLevelFlash('Request error urutan.', false);
                           this.loadDynamicData(); // Load ulang dari server
                      });
                 },

                 // Fungsi untuk update wage standard (jika pakai Alpine + fetch)
                 // updateMemberWageViaJS(memberId, wageStandardId) { ... fetch logic ... }
             }
        }
    </script>
    {{-- Tambahkan CSS Utility jika belum ada secara global --}}
    <style>
        .input-field { @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm; }
        .input-error { @apply border-red-500 ring-red-500; }
        .input-error-msg { @apply mt-1 text-xs text-red-600; }
        .label-text { @apply block text-sm font-medium text-gray-700 mb-1; }
        .radio-input { @apply focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300; }
        .btn-primary { @apply inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50; }
        .btn-secondary { @apply inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500; }
        .btn-primary.btn-sm { @apply px-3 py-1.5 text-xs; }
        .btn-secondary.btn-sm { @apply px-3 py-1.5 text-xs; }
        .btn-icon-left { @apply -ml-0.5 mr-1 h-3.5 w-3.5; }
        .th-cell { @apply px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider; }
        .td-cell { @apply px-6 py-4 whitespace-nowrap text-sm; }
        .input-field-sm { @apply block w-full pl-3 pr-10 py-1 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md; }
        .badge-yellow { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800; }
        .badge-green { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800; }
        .badge-red { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800; }
        .list-item-hover { @apply px-4 py-3 sm:px-6 flex justify-between items-center group hover:bg-gray-50; }
        .list-item-empty { @apply px-4 py-4 sm:px-6 text-center text-sm text-gray-500 italic; }
        .cursor-grab { cursor: grab; }
        .cursor-move { cursor: move; }
        .sortable-ghost { background-color: #e9ecef; opacity: 0.7; border: 1px dashed #adb5bd; }
    </style>
    @endpush
    {{-- Partial Modal Level --}}
    @include('projects.partials.level-modal')

</div>