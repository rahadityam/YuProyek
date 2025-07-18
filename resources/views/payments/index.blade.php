<x-app-layout>
        {{-- Pass unpaid tasks grouped by user ID from controller --}}
        {{-- Pastikan atribut x-data memiliki kutip pembuka dan penutup yang benar --}}
        <div x-data='{
                selectedWorkerId: "{{ old('user_id', '') }}",
                availableTasks: {{ json_encode($unpaidTasksGrouped, JSON_HEX_APOS | JSON_HEX_QUOT) }}, {{-- Tambahkan flag JSON --}}
                workerTasks: [],
                selectedTaskIds: {{ json_encode(old('task_ids', []), JSON_HEX_APOS | JSON_HEX_QUOT) }}, {{-- Tambahkan flag JSON --}}
                paymentType: "{{ old('payment_type', 'task') }}",
                calculatedAmount: 0,
                manualAmount: "{{ old('amount', '') }}",

                // Fungsi untuk update task list saat worker dipilih
                updateTasks() {
                    let tasksForWorker = this.availableTasks[this.selectedWorkerId];
                    // Pastikan tasksForWorker adalah array, jika tidak (misal null/undefined), jadikan array kosong
                    this.workerTasks = Array.isArray(tasksForWorker) ? tasksForWorker : [];
                    this.selectedTaskIds = []; // Reset selection when worker changes
                    this.calculateTotalAmount(); // Hitung ulang (jadi 0)
                    if (this.paymentType === "other") {
                       this.manualAmount = "";
                    }
                },

                // Fungsi untuk menghitung total amount dari task terpilih
                calculateTotalAmount() {
                    if (this.paymentType !== "task") {
                        this.calculatedAmount = 0;
                        return;
                    }
                    let total = 0;
                    // Pastikan workerTasks adalah array
                    if (!Array.isArray(this.workerTasks)) {
                        this.workerTasks = [];
                    }
                    this.selectedTaskIds.forEach(taskId => {
                        // Cari task (pastikan tipe data ID konsisten)
                        const task = this.workerTasks.find(t => String(t.id) === String(taskId));
                        if (task && task.calculated_value) {
                            total += parseFloat(task.calculated_value);
                        }
                    });
                    this.calculatedAmount = total;
                },

                // Fungsi untuk memformat mata uang
                formatCurrency(value) {
                    if (value === null || value === undefined || isNaN(value)) return "Rp 0";
                    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                },

                // Inisialisasi dan Watcher
                initComponent() { {{-- Ganti nama init agar tidak bentrok jika ada init lain --}}
                     this.$watch("selectedTaskIds", () => this.calculateTotalAmount());
                     this.$watch("paymentType", (newValue) => {
                         if(newValue === "task") {
                            this.calculateTotalAmount();
                         } else {
                             this.calculatedAmount = 0;
                             this.$nextTick(() => { document.getElementById("amount")?.focus(); });
                         }
                     });

                     // Jalankan updateTasks jika ada old worker ID saat load
                     if (this.selectedWorkerId) {
                         this.updateTasks();
                         // Hitung ulang jika old data ada & tipenya task (beri delay)
                         if (this.paymentType === "task" && this.selectedTaskIds.length > 0) {
                             this.$nextTick(() => { this.calculateTotalAmount(); });
                         }
                     } else {
                        // Jika tidak ada old worker ID, pastikan task list kosong
                        this.workerTasks = [];
                     }

                     // Hitung awal jika tipe task
                     if (this.paymentType === "task") {
                         this.calculateTotalAmount();
                     }
                }
             }' {{-- <<<=== PASTIKAN KUTIP TUNGGAL PENUTUP INI ADA --}}
             x-init="initComponent()" {{-- Panggil fungsi init yang baru --}}
             class="py-6">

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 {{-- Judul Halaman --}}
                 <div class="mb-6">
                     <h2 class="text-2xl font-semibold text-gray-900">Pembayaran & Riwayat - {{ $project->name }}</h2>
                 </div>

                 <!-- Tabs -->
                 <div class="border-b border-gray-200 mb-6">
                     <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                          <a href="{{ route('projects.pembayaran.calculate', $project) }}"
                             class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                              Perhitungan Penggajian
                          </a>
                          <a href="{{ route('projects.pembayaran', $project) }}"
                             class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                              Pembayaran & Riwayat
                          </a>
                     </nav>
                 </div>

                 {{-- Pesan Sukses & Error --}}
                 @if(session('success'))
                     <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                         <span class="block sm:inline">{{ session('success') }}</span>
                     </div>
                 @endif
                  @if($errors->any())
                     <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                          <p><strong>Error!</strong> Periksa inputan Anda.</p>
                          <ul class="list-disc list-inside mt-2">
                              @foreach ($errors->all() as $error)
                                  <li>{{ $error }}</li>
                              @endforeach
                          </ul>
                     </div>
                 @endif

                {{-- Form Upload Pembayaran --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Upload Bukti Pembayaran</h3>
                        <form method="POST" action="{{ route('projects.storePayment', $project) }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <!-- Pekerja -->
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                                <select id="user_id" name="user_id" x-model="selectedWorkerId" @change="updateTasks()" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Pilih Pekerja</option>
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}" {{ old('user_id') == $worker->id ? 'selected' : '' }}>{{ $worker->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Pilihan Tipe Pembayaran --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pembayaran</label>
                                <fieldset class="mt-1">
                                    <legend class="sr-only">Tipe Pembayaran</legend>
                                    <div class="space-y-2 sm:flex sm:items-center sm:space-y-0 sm:space-x-4">
                                        <div class="flex items-center">
                                            <input id="payment_type_task" name="payment_type" type="radio" value="task" x-model="paymentType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                            <label for="payment_type_task" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">
                                                Per Task Selesai
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="payment_type_other" name="payment_type" type="radio" value="other" x-model="paymentType" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                            <label for="payment_type_other" class="ml-2 block text-sm font-medium text-gray-700 cursor-pointer">
                                                Bonus / DP / Lainnya
                                            </label>
                                        </div>
                                    </div>
                                </fieldset>
                                @error('payment_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Task yang Dibayar (Hanya tampil jika tipe 'task') -->
                             <div x-show="paymentType === 'task'">
                                 <div x-show="selectedWorkerId && workerTasks.length > 0">
                                     <label class="block text-sm font-medium text-gray-700">Tugas yang Dibayar (Pilih satu atau lebih)</label>
                                     <div class="mt-1 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2 bg-gray-50">
                                         <template x-for="task in workerTasks" :key="task.id">
                                             <div class="flex items-start p-2 border border-gray-200 rounded bg-white shadow-sm hover:bg-indigo-50 transition duration-150">
                                                 <input :id="'task_'+task.id" name="task_ids[]" type="checkbox" :value="task.id" x-model="selectedTaskIds"
                                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-1 cursor-pointer">
                                                 <div class="ml-3 flex-1">
                                                      <label :for="'task_'+task.id" class="block text-sm font-medium text-gray-900 cursor-pointer" x-text="task.title"></label>
                                                      {{-- Tampilkan Info Tambahan --}}
                                                      <div class="text-xs text-gray-500 mt-1 space-x-2">
                                                          <span>WSM: <strong x-text="task.wsm_score?.toFixed(2) || 'N/A'"></strong></span>
                                                          <span>|</span>
                                                          <span>Achv: <strong x-text="(task.achievement_percentage ?? 100) + '%'"></strong></span>
                                                          <span>|</span>
                                                          <span>Nilai: <strong class="text-indigo-700" x-text="formatCurrency(task.calculated_value || 0)"></strong></span>
                                                      </div>
                                                 </div>
                                             </div>
                                         </template>
                                     </div>
                                      {{-- Error specific to task_ids array and its elements --}}
                                      @error('task_ids') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                      @error('task_ids.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                 </div>
                                  {{-- Pesan jika tidak ada task atau worker belum dipilih --}}
                                  <div x-show="selectedWorkerId && workerTasks.length === 0" class="text-sm text-gray-500 mt-1 italic">
                                      Tidak ada tugas selesai yang belum dibayar untuk pekerja ini.
                                  </div>
                                   <div x-show="!selectedWorkerId" class="text-sm text-gray-500 mt-1 italic">
                                       Pilih pekerja terlebih dahulu untuk melihat tugas yang tersedia.
                                   </div>
                             </div>

                            <!-- Nama Pembayaran -->
                            <div>
                                <label for="payment_name" class="block text-sm font-medium text-gray-700">
                                    Nama Pembayaran <span x-show="paymentType === 'other'" class="text-gray-500">(Misal: Bonus Q1, DP Awal, dll)</span>
                                </label>
                                <input type="text" name="payment_name" id="payment_name" value="{{ old('payment_name') }}" required
                                       :placeholder="paymentType === 'task' ? 'Otomatis (Pembayaran Task '+ new Date().toLocaleDateString('id-ID') + ')' : 'Masukkan nama pembayaran'"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('payment_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nominal -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Nominal Dibayar</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    {{-- Bind value dan disabled state --}}
                                    <input type="number" name="amount" id="amount"
                                           {{-- Jika tipe task, bind ke calculatedAmount dan disable/readonly --}}
                                           x-bind:value="paymentType === 'task' ? calculatedAmount.toFixed(0) : manualAmount" {{-- toFixed(0) untuk input number --}}
                                           @input="if (paymentType === 'other') manualAmount = $event.target.value" {{-- Update manualAmount hanya jika tipe other --}}
                                           :disabled="paymentType === 'task'"
                                           :readonly="paymentType === 'task'"
                                           required min="0" step="1" {{-- Step 1 untuk Rupiah bulat --}}
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md"
                                           :class="{'bg-gray-100 cursor-not-allowed': paymentType === 'task'}"
                                           placeholder="0">
                                </div>
                                <p x-show="paymentType === 'task'" class="mt-1 text-xs text-gray-500 italic">
                                    Nominal dihitung otomatis berdasarkan total nilai task yang dipilih: <strong x-text="formatCurrency(calculatedAmount)"></strong>.
                                </p>
                                @error('amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Bukti Pembayaran -->
                             <div>
                                 <label for="proof_image" class="block text-sm font-medium text-gray-700">Bukti Pembayaran (JPG, PNG, PDF maks 2MB)</label>
                                 <input type="file" name="proof_image" id="proof_image" required accept=".jpg,.jpeg,.png,.pdf"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                 @error('proof_image') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                 {{-- Simple Preview (optional) --}}
                                 <img id="preview-image" src="#" alt="Preview Gambar" class="mt-2 max-h-40 rounded-md hidden">
                                  <a id="preview-pdf" href="#" target="_blank" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 hidden">Lihat Pratinjau PDF</a>
                             </div>

                            <!-- Catatan -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                                <textarea id="notes" name="notes" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md">{{ old('notes') }}</textarea>
                                @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Tombol Submit --}}
                            <div class="flex justify-end">
                                <button type="submit"
                                        {{-- Kondisi disable: Worker belum dipilih ATAU (tipe task DAN tidak ada task dipilih) --}}
                                        :disabled="!selectedWorkerId || (paymentType === 'task' && selectedTaskIds.length === 0)"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Upload Pembayaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                 <!-- Payment History List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Riwayat Pembayaran</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                         {{-- Helper function for sorting links --}}
                                         @php
                                             $sortLink = fn($field, $label) => route('projects.pembayaran', $project) . '?' . http_build_query(array_merge(request()->except(['page', 'history_page']), [ // Hapus kedua param page
                                                 'sort' => $field,
                                                 'direction' => request('sort') === $field && request('direction') === 'asc' ? 'desc' : 'asc',
                                                 'history_page' => request('history_page', 1) // Pertahankan history_page saat sort
                                             ]));
                                             $sortIndicator = fn($field) => request('sort') === $field ? (request('direction') === 'asc' ? '↑' : '↓') : '';
                                         @endphp

                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                             <a href="{{ $sortLink('created_at', 'Tanggal') }}">Tanggal {!! $sortIndicator('created_at') !!}</a>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                             <a href="{{ $sortLink('user_name', 'Pekerja') }}">Pekerja {!! $sortIndicator('user_name') !!}</a>
                                        </th>
                                         {{-- Kolom Tipe Pembayaran --}}
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                             <a href="{{ $sortLink('payment_type', 'Tipe') }}">Tipe {!! $sortIndicator('payment_type') !!}</a>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                             <a href="{{ $sortLink('payment_name', 'Nama Pembayaran') }}">Nama Pembayaran {!! $sortIndicator('payment_name') !!}</a>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                             <a href="{{ $sortLink('amount', 'Nominal') }}">Nominal {!! $sortIndicator('amount') !!}</a>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                             <a href="{{ $sortLink('status', 'Status') }}">Status {!! $sortIndicator('status') !!}</a>
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($payments as $payment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"> {{-- Tanggal --}}
                                            {{ $payment->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900"> {{-- Pekerja --}}
                                            {{ $payment->user->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500"> {{-- Tipe --}}
                                            @if($payment->payment_type === 'task')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                   Task
                                                </span>
                                            @else
                                                 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 capitalize">
                                                   {{-- Tampilkan nama type jika bukan 'task', misal 'Other', 'Bonus' --}}
                                                   {{ str_replace('_', ' ', $payment->payment_type) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500" title="{{ $payment->payment_name }}"> {{-- Nama Pembayaran --}}
                                            {{ \Illuminate\Support\Str::limit($payment->payment_name, 30) }}
                                            {{-- Tampilkan jumlah task jika tipe task --}}
                                             @if($payment->payment_type === 'task')
                                                <span class="text-xs text-gray-400">({{ $payment->tasks()->count() }} task)</span>
                                             @endif
                                        </td>
                                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right"> {{-- Nominal --}}
                                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center"> {{-- Status --}}
                                             {{-- Definisi $statusClass dipindahkan ke sini --}}
                                             @php
                                                 $statusClasses = [
                                                     'pending' => 'bg-yellow-100 text-yellow-800',
                                                     'completed' => 'bg-green-100 text-green-800',
                                                     'rejected' => 'bg-red-100 text-red-800',
                                                 ];
                                                 $statusClass = $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800';
                                             @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center"> {{-- Bukti --}}
                                            @if ($payment->proof_image)
                                                <a href="{{ Storage::url($payment->proof_image) }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium"> {{-- Aksi --}}
                                            <div class="flex justify-center space-x-2">
                                                {{-- View Detail Link --}}
                                                 <a href="{{ route('projects.payment.detail', [$project, $payment]) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"> <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /> <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /> </svg>
                                                 </a>

                                                {{-- Status Update Buttons --}}
                                                 @if($payment->status !== 'completed')
                                                 <form method="POST" action="{{ route('projects.payments.updateStatus', [$project, $payment]) }}" class="inline">
                                                     @csrf @method('PATCH')
                                                     <input type="hidden" name="status" value="completed">
                                                     <button type="submit" class="text-green-600 hover:text-green-900" title="Tandai Selesai">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"> <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /> </svg>
                                                     </button>
                                                 </form>
                                                 @endif
                                                 @if($payment->status !== 'rejected')
                                                  <form method="POST" action="{{ route('projects.payments.updateStatus', [$project, $payment]) }}" class="inline">
                                                      @csrf @method('PATCH')
                                                      <input type="hidden" name="status" value="rejected">
                                                      <button type="submit" class="text-yellow-600 hover:text-yellow-900" title="Tandai Ditolak">
                                                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"> <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /> </svg>
                                                      </button>
                                                  </form>
                                                 @endif

                                                 {{-- Delete Button --}}
                                                <form method="POST" action="{{ route('projects.payments.destroy', [$project, $payment]) }}" class="inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus pembayaran \'{{ e($payment->payment_name) }}\'? {{ $payment->payment_type === 'task' ? 'Task terkait akan kembali ke status belum dibayar.' : '' }}')"> {{-- Gunakan e() untuk escape nama --}}
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"> <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /> </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-10 text-center text-sm text-gray-500 italic"> {{-- Sesuaikan colspan jadi 9 --}}
                                            Belum ada data riwayat pembayaran.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination untuk Riwayat --}}
                         @if ($payments instanceof \Illuminate\Pagination\LengthAwarePaginator && $payments->hasPages())
                            <div class="mt-4">
                                {{-- Gunakan appends untuk mempertahankan query string sort/filter saat paginasi --}}
                                {{ $payments->appends(request()->except('page'))->links('vendor.pagination.tailwind') }}
                            </div>
                         @endif
                    </div>
                </div>
            </div> {{-- End max-w-7xl --}}
        </div> {{-- End div x-data --}}

        @push('scripts')
        <script>
            // Simple image/pdf preview
            document.getElementById('proof_image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const imgPreview = document.getElementById('preview-image');
                const pdfPreview = document.getElementById('preview-pdf');

                 imgPreview.classList.add('hidden'); // Hide previews initially
                 pdfPreview.classList.add('hidden');
                 imgPreview.src = '#'; // Reset src
                 pdfPreview.href = '#'; // Reset href
                 pdfPreview.textContent = ''; // Reset text

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (file.type.startsWith('image/')) {
                             imgPreview.src = e.target.result;
                             imgPreview.classList.remove('hidden');
                        } else if (file.type === 'application/pdf') {
                             pdfPreview.href = e.target.result; // Use Data URL for preview link
                             pdfPreview.classList.remove('hidden');
                             pdfPreview.textContent = `Lihat Pratinjau: ${file.name}`;
                        }
                    }
                    reader.readAsDataURL(file); // Read file as Data URL
                }
            });
        </script>
        @endpush
    </x-app-layout>