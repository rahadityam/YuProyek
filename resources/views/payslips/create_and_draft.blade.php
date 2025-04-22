<x-app-layout>
    {{-- AlpineJS Data Setup --}}
    <div x-data='payslipForm({
            projectId: {{ $project->id }},
            paymentCalculationType: @json($paymentCalculationType),
            workers: @json($workers),
            paymentTerms: @json($paymentTerms), // Data termin dari controller
            unpaidTasksGrouped: @json($unpaidTasksGrouped, JSON_HEX_APOS | JSON_HEX_QUOT),
            csrfToken: "{{ csrf_token() }}",
            storePayslipUrl: "{{ route('projects.payslips.store', $project) }}",
            // --- REVISI: Gunakan $oldInput dari Controller ---
            oldInput: @json($oldInput ?? []), // Default ke array kosong jika tidak ada
            errors: @json($errors->getMessages()),
            defaultTerminName: @json($defaultTerminName)
         })'
         x-init="init()"
         class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             {{-- Judul Halaman --}}
             <div class="mb-6">
                 <h2 class="text-2xl font-semibold text-gray-900">Pembuatan Slip Gaji - {{ $project->name }}</h2>
             </div>

             <!-- Tabs -->
             <div class="border-b border-gray-200 mb-6">
                 <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                     <a href="{{ route('projects.payroll.calculate', $project) }}"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                         Perhitungan Gaji
                     </a>
                     <a href="{{ route('projects.payslips.create', $project) }}"
                        class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                         Pembuatan Slip Gaji
                     </a>
                      <a href="{{ route('projects.payslips.history', $project) }}"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                         Riwayat Slip Gaji
                     </a>
                 </nav>
             </div>

             {{-- Pesan Sukses & Error --}}
             @if(session('success'))
                 <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                     <span class="block sm:inline">{{ session('success') }}</span>
                 </div>
             @endif
             <template x-if="Object.keys(formErrors).length > 0">
                  <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                       <p><strong>Error!</strong> Periksa inputan Anda:</p>
                       <ul class="list-disc list-inside mt-2 text-sm">
                           <template x-for="(messages, field) in formErrors" :key="field">
                               <template x-for="message in messages" :key="message">
                                   {{-- Format error lebih baik --}}
                                   <li x-text="`${getFieldName(field)}: ${message}`"></li>
                               </template>
                           </template>
                       </ul>
                  </div>
              </template>
              @error('general')
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                     <span class="block sm:inline">{{ $message }}</span>
                </div>
              @enderror


            {{-- Form Buat Slip Gaji --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Buat Slip Gaji Baru</h3>
                    <form method="POST" :action="storePayslipUrl" class="space-y-6">
                        @csrf

                        <!-- Pekerja -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                            <select id="user_id" name="user_id" x-model="selectedWorkerId" @change="updateTasksForWorker()" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Pilih Pekerja</option>
                                <template x-for="worker in workers" :key="worker.id">
                                     <option :value="worker.id" x-text="worker.name"></option>
                                </template>
                            </select>
                            <template x-if="formErrors.user_id"><span class="text-red-500 text-xs mt-1" x-text="formErrors.user_id[0]"></span></template>
                        </div>

                        {{-- Tipe Pembayaran (Utama / Bonus) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Slip Gaji</label>
                            <fieldset class="mt-1">
                                <div class="space-y-2 sm:flex sm:items-center sm:space-y-0 sm:space-x-4">
                                    {{-- Opsi berdasarkan paymentCalculationType --}}
                                    <template x-if="paymentCalculationType === 'task'">
                                        <div class="flex items-center">
                                            <input id="payment_type_task" name="payment_type" type="radio" value="task" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                            <label for="payment_type_task" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Task</label>
                                        </div>
                                    </template>
                                    <template x-if="paymentCalculationType === 'termin'">
                                        <div class="flex items-center">
                                            <input id="payment_type_termin" name="payment_type" type="radio" value="termin" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                            <label for="payment_type_termin" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Termin</label>
                                        </div>
                                    </template>
                                    <template x-if="paymentCalculationType === 'full'">
                                        <div class="flex items-center">
                                            <input id="payment_type_full" name="payment_type" type="radio" value="full" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                            <label for="payment_type_full" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Penuh</label>
                                        </div>
                                    </template>
                                    {{-- Opsi Bonus/Lainnya selalu ada --}}
                                    <div class="flex items-center">
                                        <input id="payment_type_other" name="payment_type" type="radio" value="other" x-model="payslipType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="payment_type_other" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Bonus / Lainnya</label>
                                    </div>
                                </div>
                            </fieldset>
                            <template x-if="formErrors.payment_type"><span class="text-red-500 text-xs mt-1" x-text="formErrors.payment_type[0]"></span></template>
                        </div>

                        {{-- Input Spesifik Berdasarkan Tipe --}}

                        {{-- Dropdown Termin (jika tipe proyek 'termin' dan tipe slip 'termin') --}}
                        <div x-show="paymentCalculationType === 'termin' && payslipType === 'termin'" class="mt-4">
                            <label for="payment_term_id" class="block text-sm font-medium text-gray-700">Pilih Termin</label>
                            <select id="payment_term_id" name="payment_term_id" x-model="selectedTermId" @change="filterTasksForSelectedTerm()" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Pilih Termin --</option>
                                {{-- Iterate over paymentTerms passed from controller --}}
                                <template x-for="term in paymentTerms" :key="term.id">
                                     {{-- Display name and formatted dates --}}
                                     {{-- Gunakan data terformat dari Controller --}}
                                    <option :value="term.id" x-text="`${term.name} (${formatTermDate(term.start_date_formatted)} - ${formatTermDate(term.end_date_formatted)})`"></option>
                                </template>
                            </select>
                            <template x-if="paymentTerms.length === 0">
                                <p class="text-xs text-red-500 mt-1 italic">Belum ada termin yang didefinisikan di Pengaturan Proyek.</p>
                            </template>
                             <template x-if="formErrors.payment_term_id"><span class="text-red-500 text-xs mt-1" x-text="formErrors.payment_term_id[0]"></span></template>
                        </div>

                        {{-- Input Pilih Task (muncul jika tipe slip 'task' atau ('termin' dan termin sudah dipilih)) --}}
                        {{-- --- REVISI: Kondisi x-show disesuaikan --- --}}
                        <div x-show="payslipType === 'task' || (payslipType === 'termin' && selectedTermId)">
                            <div x-show="selectedWorkerId">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tugas yang Dibayar
                                    <span x-show="payslipType === 'termin'">(dalam termin terpilih)</span>
                                </label>
                                <div class="mt-1 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2 bg-gray-50">
                                    <template x-if="!isLoadingTasks && availableTasksForWorker.length === 0"> {{-- Tambah cek isLoadingTasks --}}
                                         <p class="text-sm text-gray-500 italic text-center py-4">
                                            <span x-show="payslipType === 'task'">Tidak ada tugas selesai yang belum dibayar untuk pekerja ini.</span>
                                            <span x-show="payslipType === 'termin'">Tidak ada tugas selesai yang belum dibayar untuk pekerja ini dalam termin yang dipilih.</span>
                                         </p>
                                    </template>
                                    <template x-if="isLoadingTasks"> {{-- Indikator Loading Task --}}
                                        <p class="text-sm text-gray-500 italic text-center py-4 animate-pulse">Memuat task...</p>
                                    </template>
                                    {{-- Loop task yang available --}}
                                    <template x-for="task in availableTasksForWorker" :key="task.id">
                                        <div class="flex items-start p-2 border border-gray-200 rounded bg-white shadow-sm hover:bg-indigo-50 transition duration-150">
                                            <input :id="'task_'+task.id" name="task_ids[]" type="checkbox" :value="String(task.id)" x-model="selectedTaskIds"
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-1 cursor-pointer">
                                            <div class="ml-3 flex-1">
                                                <label :for="'task_'+task.id" class="block text-sm font-medium text-gray-900 cursor-pointer" x-text="task.title"></label>
                                                <div class="text-xs text-gray-500 mt-1 space-x-2">
                                                    <span>WSM: <strong x-text="task.wsm_score?.toFixed(2) || 'N/A'"></strong></span>|
                                                    <span>Achv: <strong x-text="(task.achievement_percentage ?? 100) + '%'"></strong></span>|
                                                    {{-- Tampilkan tanggal selesai --}}
                                                    <span>Selesai: <strong x-text="formatDisplayDate(task.finished_date)"></strong></span> |
                                                    <span>Nilai: <strong class="text-indigo-700" x-text="formatCurrency(task.calculated_value || 0)"></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="formErrors.task_ids"><span class="text-red-500 text-xs mt-1 block" x-text="formErrors.task_ids[0]"></span></template>
                                <template x-if="formErrors['task_ids.*']"><span class="text-red-500 text-xs mt-1 block" x-text="formErrors['task_ids.*'][0]"></span></template>
                            </div>
                            <div x-show="!selectedWorkerId" class="text-sm text-gray-500 mt-1 italic">Pilih pekerja terlebih dahulu.</div>
                        </div>

                        <!-- Nama Pembayaran -->
                        <div>
                            <label for="payment_name" class="block text-sm font-medium text-gray-700">Nama Slip Gaji</label>
                            <input type="text" name="payment_name" id="payment_name" required
                                   x-model="paymentName"
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            <template x-if="formErrors.payment_name"><span class="text-red-500 text-xs mt-1" x-text="formErrors.payment_name[0]"></span></template>
                        </div>

                        <!-- Nominal -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Nominal Slip Gaji</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">Rp</span></div>
                                <input type="number" name="amount" id="amount"
                                       x-model="calculatedAmount"
                                       :disabled="payslipType === 'task' || payslipType === 'termin'"
                                       :readonly="payslipType === 'task' || payslipType === 'termin'"
                                       required min="0" step="1"
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md"
                                       :class="{'bg-gray-100 cursor-not-allowed': payslipType === 'task' || payslipType === 'termin'}"
                                       placeholder="0">
                            </div>
                            <p x-show="payslipType === 'task' || payslipType === 'termin'" class="mt-1 text-xs text-gray-500 italic">
                                Nominal dihitung otomatis berdasarkan task yang dipilih: <strong x-text="formatCurrency(calculatedAmount)"></strong>.
                            </p>
                            <template x-if="formErrors.amount"><span class="text-red-500 text-xs mt-1" x-text="formErrors.amount[0]"></span></template>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                            <textarea id="notes" name="notes" rows="3" x-model="notes" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"></textarea>
                            <template x-if="formErrors.notes"><span class="text-red-500 text-xs mt-1" x-text="formErrors.notes[0]"></span></template>
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="flex justify-end">
                             {{-- --- REVISI: Disable logic disesuaikan --- --}}
                            <button type="submit"
                                    :disabled="isSubmitting || !selectedWorkerId || (payslipType === 'task' && selectedTaskIds.length === 0) || (payslipType === 'termin' && (!selectedTermId || selectedTaskIds.length === 0)) || (payslipType === 'full' && (!calculatedAmount || calculatedAmount <= 0)) || (payslipType === 'other' && (!calculatedAmount || calculatedAmount <= 0))"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!isSubmitting">Simpan Draft Slip Gaji</span>
                                <span x-show="isSubmitting">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Draft Slip Gaji -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Draft Slip Gaji</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                     {{-- Header Tabel Draft --}}
                                     @php
                                         $sortLinkDraft = fn($field, $label) => route('projects.payslips.create', $project) . '?' . http_build_query(array_merge(request()->except(['page', 'draft_page']), [ 'sort_draft' => $field, 'direction_draft' => request('sort_draft') === $field && request('direction_draft') === 'asc' ? 'desc' : 'asc', 'draft_page' => request('draft_page', 1) ]));
                                         $sortIndicatorDraft = fn($field) => request('sort_draft') === $field ? (request('direction_draft') === 'asc' ? '↑' : '↓') : '';
                                     @endphp
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"> <a href="{{ $sortLinkDraft('created_at', 'Tanggal Dibuat') }}">Dibuat {!! $sortIndicatorDraft('created_at') !!}</a> </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"> <a href="{{ $sortLinkDraft('user_name', 'Pekerja') }}">Pekerja {!! $sortIndicatorDraft('user_name') !!}</a> </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"> <a href="{{ $sortLinkDraft('payment_type', 'Tipe') }}">Tipe {!! $sortIndicatorDraft('payment_type') !!}</a> </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"> <a href="{{ $sortLinkDraft('payment_name', 'Nama Slip') }}">Nama Slip {!! $sortIndicatorDraft('payment_name') !!}</a> </th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"> <a href="{{ $sortLinkDraft('amount', 'Nominal') }}">Nominal {!! $sortIndicatorDraft('amount') !!}</a> </th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($draftPayslips as $draft)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $draft->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $draft->user->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $draft->payment_type }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500" title="{{ $draft->payment_name }}">{{ \Illuminate\Support\Str::limit($draft->payment_name, 35) }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rp {{ number_format($draft->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-3">
                                            <a href="{{ route('projects.payslips.show', [$project, $draft]) }}" class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail & Setujui"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"> <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /> <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /> </svg> </a>
                                            <form method="POST" action="{{ route('projects.payslips.destroy', [$project, $draft]) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus draft slip gaji \'{{ e($draft->payment_name) }}\'? Task terkait (jika ada) akan dikembalikan.')"> @csrf @method('DELETE') <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus Draft"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"> <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /> </svg> </button> </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr> <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 italic"> Belum ada draft slip gaji. </td> </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination untuk Draft --}}
                     @if ($draftPayslips instanceof \Illuminate\Pagination\LengthAwarePaginator && $draftPayslips->hasPages())
                        <div class="mt-4">
                            {{ $draftPayslips->appends(request()->except('page'))->links('vendor.pagination.tailwind') }}
                        </div>
                     @endif
                </div>
            </div>

        </div> {{-- End max-w-7xl --}}
    </div> {{-- End div x-data --}}

    @push('scripts')
    <script>
        function payslipForm(config) {
            return {
                // Config & Data
                projectId: config.projectId,
                paymentCalculationType: config.paymentCalculationType || 'task',
                workers: config.workers || [],
                paymentTerms: config.paymentTerms || [],
                unpaidTasksGrouped: config.unpaidTasksGrouped || {},
                csrfToken: config.csrfToken,
                storePayslipUrl: config.storePayslipUrl,
                defaultTerminName: config.defaultTerminName || 'Termin 1',
                // Form State
                selectedWorkerId: config.oldInput?.user_id || '',
                payslipType: config.oldInput?.payment_type || config.paymentCalculationType,
                selectedTermId: config.oldInput?.payment_term_id ? parseInt(config.oldInput.payment_term_id) : '',
                paymentName: config.oldInput?.payment_name || '',
                notes: config.oldInput?.notes || '',
                calculatedAmount: parseFloat(config.oldInput?.amount) || 0,
                selectedTaskIds: Array.isArray(config.oldInput?.task_ids) ? config.oldInput.task_ids.map(String) : [],
                availableTasksForWorker: [],
                isLoadingTasks: false, // Tambahkan state loading task
                isSubmitting: false,
                formErrors: config.errors || {},

                // Init
                init() {
                    this.$watch('selectedWorkerId', () => this.updateTasksForWorker());
                    this.$watch('payslipType', (newType) => this.handlePayslipTypeChange(newType));
                    this.$watch('selectedTermId', () => this.filterTasksForSelectedTerm());
                    this.$watch('selectedTaskIds', () => this.calculateTotalAmount());

                    if (this.selectedWorkerId) {
                        this.updateTasksForWorker();
                    }
                    this.handlePayslipTypeChange(this.payslipType, false);

                    if (this.payslipType === 'termin' && this.selectedTermId) {
                        this.$nextTick(() => {
                            this.filterTasksForSelectedTerm();
                            if (this.selectedTaskIds.length > 0) {
                                this.calculateTotalAmount();
                            }
                        });
                    }

                    // Clear errors on input change
                    document.querySelectorAll('input, select, textarea').forEach(el => {
                        el.addEventListener('input', (e) => { this.clearError(e.target.name); });
                        el.addEventListener('change', (e) => { this.clearError(e.target.name); });
                    });
                },

                // Methods
                clearError(fieldName) {
                    if (this.formErrors[fieldName]) {
                        delete this.formErrors[fieldName];
                        // Juga hapus error array task_ids.* jika task_ids diubah
                        if (fieldName === 'task_ids[]' && this.formErrors['task_ids.*']) {
                            delete this.formErrors['task_ids.*'];
                        }
                         if (fieldName === 'payment_term_id' && this.formErrors['payment_term_id']) {
                            delete this.formErrors['payment_term_id'];
                        }
                    }
                },

                getFieldName(fieldKey) {
                     const fieldMap = {
                         'user_id': 'Pekerja',
                         'payment_type': 'Tipe Slip Gaji',
                         'payment_term_id': 'Termin Pembayaran',
                         'payment_name': 'Nama Slip Gaji',
                         'amount': 'Nominal',
                         'notes': 'Catatan',
                         'task_ids': 'Pilihan Task',
                         'task_ids.*': 'Pilihan Task'
                     };
                     return fieldMap[fieldKey] || fieldKey.replace(/_/g, ' ').replace(/\.\*/g, '');
                },

                updateTasksForWorker() {
                    this.selectedTaskIds = [];
                     // Hanya filter jika tipe slip saat ini adalah task atau termin
                    if (this.payslipType === 'task' || this.payslipType === 'termin') {
                        this.filterTasksForSelectedTerm();
                    } else {
                        this.availableTasksForWorker = []; // Kosongkan jika tipe lain
                        this.calculateTotalAmount();
                    }
                },

                filterTasksForSelectedTerm() {
                    this.selectedTaskIds = []; // Reset selection
                    this.isLoadingTasks = true; // Mulai loading
                    this.availableTasksForWorker = []; // Kosongkan dulu

                     // Gunakan setTimeout agar loading indicator sempat terlihat
                    setTimeout(() => {
                        if (!this.selectedWorkerId) {
                            this.isLoadingTasks = false;
                            this.calculateTotalAmount();
                            return;
                        }

                        const workerTasks = this.unpaidTasksGrouped[this.selectedWorkerId] || [];

                        if (this.payslipType === 'termin') {
                            if (!this.selectedTermId) {
                                this.availableTasksForWorker = []; // Kosong jika termin belum dipilih
                            } else {
                                const selectedTerm = this.paymentTerms.find(term => term.id == this.selectedTermId);
                                if (!selectedTerm || !selectedTerm.start_date_formatted || !selectedTerm.end_date_formatted) {
                                    console.error('Invalid term selected or missing dates:', selectedTerm);
                                    this.availableTasksForWorker = [];
                                } else {
                                    try {
                                        const startDate = new Date(selectedTerm.start_date_formatted + 'T00:00:00Z');
                                        const endDate = new Date(selectedTerm.end_date_formatted + 'T23:59:59Z');
                                        if (isNaN(startDate) || isNaN(endDate)) throw new Error("Invalid date parsed from term.");

                                        this.availableTasksForWorker = workerTasks.filter(task => {
                                            if (!task.finished_date) return false;
                                            try {
                                                const finishedDate = new Date(task.finished_date + 'T12:00:00Z'); // UTC Midday
                                                if(isNaN(finishedDate)) return false;
                                                return finishedDate >= startDate && finishedDate <= endDate;
                                            } catch (dateError) { return false; }
                                        });
                                    } catch (e) { console.error("Error during date filtering:", e); this.availableTasksForWorker = []; }
                                }
                            }
                        } else if (this.payslipType === 'task') {
                            this.availableTasksForWorker = workerTasks;
                        } else {
                            this.availableTasksForWorker = [];
                        }

                        this.isLoadingTasks = false; // Selesai loading
                        this.calculateTotalAmount(); // Recalculate amount
                    }, 50); // Delay kecil untuk UX loading
                },

                calculateTotalAmount() {
                    if (this.payslipType === 'task' || this.payslipType === 'termin') {
                        let total = 0;
                        const selectedIdsSet = new Set(this.selectedTaskIds.map(String));
                        const tasksToSum = this.availableTasksForWorker.filter(task =>
                           selectedIdsSet.has(String(task.id))
                        );
                        tasksToSum.forEach(task => {
                            total += parseFloat(task.calculated_value || 0);
                        });
                        this.calculatedAmount = total;
                    }
                     // Jika tipe 'full' atau 'other', biarkan this.calculatedAmount
                     // Jika ada oldInput, nilai itu sudah di-set di awal
                },

                handlePayslipTypeChange(newType, resetAmount = true) {
                    // Set default payment name
                    if (newType === 'termin') { this.paymentName = this.defaultTerminName; }
                    else if (newType === 'task') { const workerName = this.workers.find(w => w.id == this.selectedWorkerId)?.name || 'Pekerja'; this.paymentName = `Pembayaran Task ${workerName} (${new Date().toLocaleDateString('id-ID')})`; }
                    else if (newType === 'full') { this.paymentName = `Pembayaran Penuh (${new Date().toLocaleDateString('id-ID')})`; }
                    else if (newType === 'other') { this.paymentName = ''; }

                    // Reset related fields
                    if (newType !== 'termin') this.selectedTermId = '';
                    if (newType !== 'task' && newType !== 'termin') {
                        this.selectedTaskIds = [];
                        this.availableTasksForWorker = [];
                    }

                    // Reset amount or recalculate
                    if (resetAmount && newType !== 'task' && newType !== 'termin') {
                        this.calculatedAmount = 0;
                        this.$nextTick(() => { document.getElementById("amount")?.focus(); });
                    } else {
                        // Trigger filter/calculation based on the new type
                        this.filterTasksForSelectedTerm(); // This handles task/termin filtering or clearing
                    }
                },

                formatTermDate(dateString) {
                    if (!dateString) return 'Invalid Date';
                    try {
                        // Tanggal dari controller sudah YYYY-MM-DD
                        const date = new Date(dateString + 'T00:00:00Z'); // Anggap UTC
                        if (isNaN(date.getTime())) return 'Invalid Date';
                        // Format dd Mmm
                        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', timeZone: 'UTC' });
                    } catch (e) { return 'Invalid Date'; }
                },

                formatDisplayDate(dateString) {
                     if (!dateString) return 'N/A';
                     try {
                         const date = new Date(dateString + 'T00:00:00Z'); // Anggap UTC
                         if (isNaN(date.getTime())) return 'N/A';
                         // Format dd Mmm YYYY
                         return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' });
                     } catch (e) { return 'N/A'; }
                 },

                formatCurrency(value) {
                    if (value === null || value === undefined || isNaN(value)) return "Rp 0";
                    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                },
            }
        }
    </script>
    @endpush
</x-app-layout>