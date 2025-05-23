<div> {{-- Root Element --}}
    <div class="py-6"> {{-- Hapus padding utama jika sudah ada di layout --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Judul Halaman --}}
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Pembuatan Slip Gaji - {{ $project->name }}</h2>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6 no-print"> {{-- no-print jika perlu --}}
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="{{ route('projects.payroll.calculate', $project) }}" wire:navigate
                       class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Perhitungan Gaji
                    </a>
                    <a href="{{ route('projects.payslips.create', $project) }}" wire:navigate
                       class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                        Pembuatan Slip Gaji
                    </a>
                    <a href="{{ route('projects.payslips.history', $project) }}" wire:navigate
                       class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Riwayat Slip Gaji
                    </a>
                </nav>
            </div>

             {{-- Pesan Sukses & Error --}}
            {{-- Tampilkan flash message dari session (untuk redirect dari Controller) --}}
            @if(session()->has('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
             {{-- Tampilkan error validasi dari Controller (jika redirect back with errors) --}}
            @if($errors->any())
                 <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                      <p><strong>Error!</strong> Periksa inputan Anda:</p>
                      <ul class="list-disc list-inside mt-1 text-sm">
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                 </div>
             @endif
             {{-- Tampilkan error general dari Controller --}}
             @error('general')
               <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $message }}</span>
               </div>
             @enderror


            {{-- Form Buat Slip Gaji (Target ke Controller Action) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Buat Slip Gaji Baru</h3>
                    {{-- Form tetap POST ke route controller --}}
                    <form method="POST" action="{{ route('projects.payslips.store', $project) }}" class="space-y-6">
                        @csrf

                        <!-- Pekerja -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                            {{-- wire:model untuk binding state Livewire --}}
                            <select id="user_id" name="user_id" wire:model="selectedWorkerId" required class="input-field w-full @error('user_id') input-error @enderror">
                                <option value="">Pilih Pekerja</option>
                                @foreach ($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                @endforeach
                            </select>
                             @error('user_id') <span class="input-error-msg">{{ $message }}</span> @enderror
                             {{-- Tampilkan error Livewire jika ada --}}
                             @error('selectedWorkerId') <span class="input-error-msg">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tipe Pembayaran --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Slip Gaji</label>
                            <fieldset class="mt-1">
                                <div class="space-y-2 sm:flex sm:items-center sm:space-y-0 sm:space-x-4">
                                    {{-- Opsi berdasarkan $paymentCalculationType --}}
                                    @if($paymentCalculationType === 'task')
                                        <div class="flex items-center">
                                            <input id="payment_type_task" name="payment_type" type="radio" value="task" wire:model="payslipType" class="radio-input">
                                            <label for="payment_type_task" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Task</label>
                                        </div>
                                    @endif
                                    @if($paymentCalculationType === 'termin')
                                        <div class="flex items-center">
                                            <input id="payment_type_termin" name="payment_type" type="radio" value="termin" wire:model="payslipType" class="radio-input">
                                            <label for="payment_type_termin" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Termin</label>
                                        </div>
                                    @endif
                                    @if($paymentCalculationType === 'full')
                                        <div class="flex items-center">
                                            <input id="payment_type_full" name="payment_type" type="radio" value="full" wire:model="payslipType" class="radio-input">
                                            <label for="payment_type_full" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Pembayaran Penuh</label>
                                        </div>
                                    @endif
                                    <div class="flex items-center">
                                        <input id="payment_type_other" name="payment_type" type="radio" value="other" wire:model="payslipType" class="radio-input">
                                        <label for="payment_type_other" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">Bonus / Lainnya</label>
                                    </div>
                                </div>
                            </fieldset>
                            @error('payment_type') <span class="input-error-msg">{{ $message }}</span> @enderror
                        </div>

                        {{-- Dropdown Termin (jika tipe proyek 'termin' dan tipe slip 'termin') --}}
                        {{-- Tampilkan HANYA jika tipe proyek adalah 'termin' --}}
                        @if($paymentCalculationType === 'termin')
                            <div x-data="{ show: @entangle('payslipType').defer === 'termin' }" x-show="show" x-transition class="mt-4">
                                <label for="payment_term_id" class="block text-sm font-medium text-gray-700">Pilih Termin</label>
                                <select id="payment_term_id" name="payment_term_id" wire:model="selectedTermId" required class="input-field w-full @error('payment_term_id') input-error @enderror">
                                    <option value="">-- Pilih Termin --</option>
                                    {{-- Loop dari $paymentTerms Livewire --}}
                                    @foreach($paymentTerms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }} ({{ $term->start_date_formatted ? \Carbon\Carbon::parse($term->start_date_formatted)->format('d M') : '?' }} - {{ $term->end_date_formatted ? \Carbon\Carbon::parse($term->end_date_formatted)->format('d M') : '?' }})</option>
                                    @endforeach
                                </select>
                                @if($paymentTerms->isEmpty())
                                    <p class="text-xs text-red-500 mt-1 italic">Belum ada termin yang didefinisikan.</p>
                                @endif
                                @error('payment_term_id') <span class="input-error-msg">{{ $message }}</span> @enderror
                             </div>
                        @endif

                        {{-- Input Pilih Task --}}
                        {{-- Tampilkan jika tipe 'task' ATAU (tipe 'termin' DAN term dipilih) --}}
                        <div x-data="{ show: @entangle('payslipType').defer === 'task' || (@entangle('payslipType').defer === 'termin' && @entangle('selectedTermId').defer != '') }" x-show="show" x-transition>
                            <div wire:key="task-list-for-{{ $selectedWorkerId }}-{{ $selectedTermId }}"> {{-- wire:key agar list refresh --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Tugas yang Dibayar <span x-show="$wire.payslipType === 'termin'">(dalam termin terpilih)</span>
                                </label>
                                <div wire:loading wire:target="selectedWorkerId, selectedTermId, payslipType" class="text-sm text-gray-500 italic p-2"> Memuat task... </div>
                                <div wire:loading.remove wire:target="selectedWorkerId, selectedTermId, payslipType" class="mt-1 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2 bg-gray-50">
                                    @if(empty($this->availableTasksForWorker)) {{-- Cek properti Livewire --}}
                                         <p class="text-sm text-gray-500 italic text-center py-4">
                                            @if($payslipType === 'task')
                                                Tidak ada tugas selesai yang belum dibayar untuk pekerja ini.
                                            @elseif($payslipType === 'termin' && !$selectedTermId)
                                                Pilih termin di atas untuk melihat tugas.
                                            @elseif($payslipType === 'termin' && $selectedTermId)
                                                Tidak ada tugas selesai yang belum dibayar untuk pekerja ini dalam termin yang dipilih.
                                            @endif
                                         </p>
                                    @else
                                         @foreach($this->availableTasksForWorker as $task)
                                            <div wire:key="task-option-{{ $task['id'] }}" class="flex items-start p-2 border border-gray-200 rounded bg-white shadow-sm hover:bg-indigo-50 transition duration-150">
                                                {{-- wire:model untuk selectedTaskIds --}}
                                                <input id="task_{{ $task['id'] }}" name="task_ids[]" type="checkbox" value="{{ $task['id'] }}" wire:model="selectedTaskIds" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-1 cursor-pointer">
                                                <div class="ml-3 flex-1">
                                                    <label for="task_{{ $task['id'] }}" class="block text-sm font-medium text-gray-900 cursor-pointer">{{ $task['title'] }}</label>
                                                    <div class="text-xs text-gray-500 mt-1 space-x-2">
                                                        <span>WSM: <strong>{{ number_format($task['wsm_score'] ?? 0, 2) }}</strong></span>|
                                                        <span>Achv: <strong>{{ $task['achievement_percentage'] ?? 100 }}%</strong></span>|
                                                        <span>Selesai: <strong>{{ $task['finished_date'] ? \Carbon\Carbon::parse($task['finished_date'])->format('d M Y') : 'N/A' }}</strong></span> |
                                                        <span>Nilai: <strong class="text-indigo-700">Rp {{ number_format($task['calculated_value'] ?? 0, 0, ',', '.') }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                @error('task_ids') <span class="input-error-msg block">{{ $message }}</span> @enderror
                                @error('task_ids.*') <span class="input-error-msg block">{{ $message }}</span> @enderror
                            </div>
                            <div x-show="!selectedWorkerId && (payslipType === 'task' || payslipType === 'termin')" class="text-sm text-gray-500 mt-1 italic">Pilih pekerja terlebih dahulu.</div>
                        </div>

                        <!-- Nama Pembayaran -->
                        <div>
                            <label for="payment_name" class="block text-sm font-medium text-gray-700">Nama Slip Gaji</label>
                            <input type="text" name="payment_name" id="payment_name" required wire:model.defer="paymentName" class="input-field w-full @error('payment_name') input-error @enderror">
                             @error('payment_name') <span class="input-error-msg">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nominal -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Nominal Slip Gaji</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">Rp</span></div>
                                {{-- type="text" dengan inputmode --}}
                                <input type="text" name="amount" id="amount" inputmode="decimal"
                                       {{-- Gunakan wire:model.lazy untuk update saat blur jika tipe manual --}}
                                       wire:model.lazy="manualAmount"
                                       {{-- Value terisi otomatis jika tipe task/termin --}}
                                       value="{{ ($payslipType === 'task' || $payslipType === 'termin') ? $calculatedAmount : $manualAmount }}"
                                       {{-- Disable/Readonly jika tipe task/termin --}}
                                       @if($payslipType === 'task' || $payslipType === 'termin') readonly disabled @endif
                                       required {{-- Required tetap dari HTML jika perlu --}}
                                       class="input-field w-full pl-10 pr-12 {{ ($payslipType === 'task' || $payslipType === 'termin') ? 'bg-gray-100 cursor-not-allowed' : '' }} @error('amount') input-error @enderror"
                                       placeholder="0">
                            </div>
                            @if($payslipType === 'task' || $payslipType === 'termin')
                            <p class="mt-1 text-xs text-gray-500 italic">
                                Nominal dihitung otomatis: <strong wire:loading wire:target="selectedTaskIds, selectedTermId">menghitung...</strong><strong wire:loading.remove wire:target="selectedTaskIds, selectedTermId">Rp {{ number_format($calculatedAmount, 0, ',', '.') }}</strong>.
                            </p>
                            @endif
                            @error('amount') <span class="input-error-msg">{{ $message }}</span> @enderror
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                            <textarea id="notes" name="notes" rows="3" wire:model.defer="notes" class="input-field w-full @error('notes') input-error @enderror"></textarea>
                            @error('notes') <span class="input-error-msg">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tombol Submit --}}
                        <div class="flex justify-end">
                            <button type="submit"
                                    {{-- Kondisi disabled bisa disederhanakan sedikit --}}
                                    @if(
                                        !$selectedWorkerId ||
                                        ($payslipType === 'task' && empty($selectedTaskIds)) ||
                                        ($payslipType === 'termin' && (empty($selectedTermId) || empty($selectedTaskIds))) ||
                                        (($payslipType === 'full' || $payslipType === 'other') && (!is_numeric($manualAmount) || $manualAmount <= 0))
                                    )
                                        disabled
                                    @endif
                                    class="btn-primary"
                                    wire:loading.attr="disabled" wire:target="saveDraft"> {{-- Target action simpan jika pakai Livewire --}}
                                <span wire:loading.remove wire:target="saveDraft">Simpan Draft Slip Gaji</span>
                                <span wire:loading wire:target="saveDraft">Menyimpan...</span> {{-- Contoh jika simpan via Livewire --}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Draft Slip Gaji -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                 <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Draft Slip Gaji</h3>
                    {{-- Loading indicator untuk tabel draft --}}
                     <div wire:loading wire:target="gotoPage, previousPage, nextPage, sortByDraft" class="text-center py-4"><span class="italic text-gray-500">Loading drafts...</span></div>
                     {{-- Tabel draft --}}
                    <div class="overflow-x-auto" wire:loading.remove wire:target="gotoPage, previousPage, nextPage, sortByDraft">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                     @php $sortIndicatorDraft = fn($field) => $sortFieldDraft === $field ? ($sortDirectionDraft === 'asc' ? '↑' : '↓') : ''; @endphp
                                    <th scope="col" class="th-cell"><button wire:click="sortByDraft('created_at')" class="font-medium hover:text-indigo-700">Dibuat {!! $sortIndicatorDraft('created_at') !!}</button></th>
                                    <th scope="col" class="th-cell"><button wire:click="sortByDraft('user_name')" class="font-medium hover:text-indigo-700">Pekerja {!! $sortIndicatorDraft('user_name') !!}</button></th>
                                    <th scope="col" class="th-cell"><button wire:click="sortByDraft('payment_type')" class="font-medium hover:text-indigo-700">Tipe {!! $sortIndicatorDraft('payment_type') !!}</button></th>
                                    <th scope="col" class="th-cell"><button wire:click="sortByDraft('payment_name')" class="font-medium hover:text-indigo-700">Nama Slip {!! $sortIndicatorDraft('payment_name') !!}</button></th>
                                    <th scope="col" class="th-cell text-right"><button wire:click="sortByDraft('amount')" class="font-medium hover:text-indigo-700">Nominal {!! $sortIndicatorDraft('amount') !!}</button></th>
                                    <th scope="col" class="th-cell text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($draftPayslips as $draft)
                                <tr wire:key="draft-{{ $draft->id }}">
                                    <td class="td-cell text-gray-500">{{ $draft->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="td-cell font-medium text-gray-900">{{ $draft->user->name ?? 'N/A' }}</td>
                                    <td class="td-cell text-gray-500 capitalize">{{ $draft->payment_type }}</td>
                                    <td class="td-cell text-gray-500" title="{{ $draft->payment_name }}">{{ \Illuminate\Support\Str::limit($draft->payment_name, 35) }}</td>
                                    <td class="td-cell text-right text-gray-500">Rp {{ number_format($draft->amount, 0, ',', '.') }}</td>
                                    <td class="td-cell text-center">
                                        <div class="flex justify-center space-x-3">
                                            {{-- Link ke halaman detail (bisa Livewire atau Controller) --}}
                                            <a href="{{ route('projects.payslips.show', [$project, $draft]) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail & Setujui"> <svg class="h-5 w-5">...</svg> </a>
                                            {{-- Tombol hapus tetap pakai form biasa --}}
                                            <form method="POST" action="{{ route('projects.payslips.destroy', [$project, $draft]) }}" class="inline" onsubmit="return confirm('Yakin hapus draft \'{{ e($draft->payment_name) }}\'?')"> @csrf @method('DELETE') <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus Draft"> <svg class="h-5 w-5">...</svg> </button> </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr> <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 italic"> Belum ada draft slip gaji. </td> </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination Livewire --}}
                     @if ($draftPayslips->hasPages())
                        <div class="mt-4">
                            {{ $draftPayslips->links() }}
                        </div>
                     @endif
                </div>
            </div>

        </div> {{-- End max-w-7xl --}}
    </div> {{-- End Padding Utama --}}

    {{-- Script Alpine tidak diperlukan jika semua dikelola Livewire --}}
    {{-- @push('scripts') <script> function payslipForm(config) { ... } </script> @endpush --}}
</div>