<x-app-layout>
    {{-- Include AlpineJS if not globally included --}}
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}

    {{-- CSS Khusus untuk Print --}}
    <style>
        @media print {
            body * {
                visibility: hidden; /* Sembunyikan semua elemen secara default */
                -webkit-print-color-adjust: exact !important; /* Chrome, Safari */
                color-adjust: exact !important; /* Firefox, Edge */
            }
            /* Tampilkan hanya area yang ingin dicetak dan anak-anaknya */
            #printable-content, #printable-content * {
                visibility: visible;
            }
            /* Posisikan area cetak di sudut kiri atas halaman */
            #printable-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%; /* Lebar penuh kertas */
                margin: 0 !important;
                padding: 15px !important; /* Beri sedikit padding */
                border: none !important;
                box-shadow: none !important;
                background-color: white !important; /* Pastikan background putih */
            }

            /* Sembunyikan elemen UI yang tidak relevan */
            .no-print, .no-print * {
                display: none !important;
            }
            /* Override display none untuk pagination di dalam tabel jika ada class no-print di parent */
             #printable-content .pagination {
                 display: none !important;
            }

            /* Styling tambahan untuk tabel dan summary saat print */
            #printable-content h3,
            #printable-content h4 {
                margin-bottom: 0.5rem;
                font-size: 1.1rem; /* Sedikit perbesar judul */
                color: black !important;
            }
            #printable-content table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 9pt !important; /* Perkecil font tabel */
                color: black !important;
            }
            #printable-content thead {
                 display: table-header-group; /* Pastikan thead berulang di setiap halaman */
            }
             #printable-content th {
                background-color: #f2f2f2 !important; /* Warna background header */
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            #printable-content th,
            #printable-content td {
                border: 1px solid #ddd !important;
                padding: 4px 6px !important; /* Padding lebih kecil */
                text-align: left !important;
                color: black !important;
                background-color: white !important; /* Pastikan background sel putih */
                 word-wrap: break-word; /* Pecah kata jika terlalu panjang */
            }
             #printable-content td span[class*="bg-"] { /* Hapus background dari status badge */
                background-color: transparent !important;
                padding: 0 !important;
                border-radius: 0 !important;
                border: 1px solid #ccc !important; /* Ganti dengan border tipis */
                color: black !important; /* Warna teks badge */
             }
            #printable-content .summary-grid { /* Target grid summary */
                 grid-template-columns: repeat(2, 1fr) !important; /* 2 kolom saat print */
                 font-size: 9pt !important;
                 margin-top: 1rem !important;
                 page-break-inside: avoid; /* Hindari summary terpotong antar halaman */
            }
             #printable-content .summary-grid > div {
                 padding: 8px !important;
                 border: 1px solid #eee !important;
                 background-color: white !important;
                  page-break-inside: avoid; /* Hindari item summary terpotong */
            }
            #printable-content .summary-grid > div[class*="bg-"] { /* Styling khusus untuk box berwarna */
                 background-color: #f0f0f0 !important; /* Ganti dengan abu-abu muda */
                 border-color: #ccc !important;
                 -webkit-print-color-adjust: exact !important;
                 color-adjust: exact !important;
             }
             #printable-content .summary-grid > div[class*="bg-"] dt,
             #printable-content .summary-grid > div[class*="bg-"] dd,
             #printable-content .summary-grid > div[class*="bg-"] span,
             #printable-content .summary-grid > div[class*="bg-"] p {
                 color: black !important; /* Pastikan teks di box berwarna terbaca */
             }
             #printable-content .summary-grid dt {
                 font-weight: bold !important;
                 color: #333 !important;
                 margin-bottom: 2px !important;
            }
             #printable-content .summary-grid dd {
                 font-size: 11pt !important;
                 margin-top: 0 !important;
                 color: black !important;
            }
            #printable-content .summary-grid p.text-xs {
                 font-size: 8pt !important;
                 color: #555 !important;
                 margin-top: 4px !important;
            }
            #printable-content a { /* Styling link saat print */
                color: #0066cc !important; /* Warna biru standar */
                text-decoration: none !important; /* Hilangkan underline */
            }
            #printable-content svg { /* Sembunyikan ikon SVG */
                 display: none !important;
            }
        }
    </style>

    <div class="py-6 px-4 sm:px-6 lg:px-8"
         x-data="payrollCalculator(
                '{{ route('projects.payroll.calculate', $project) }}',
                '{{ old('worker_id', $request->input('worker_id', 'all')) }}',
                '{{ old('payment_status', $request->input('payment_status', 'all')) }}',
                '{{ old('search', $request->input('search', '')) }}',
                {{ old('per_page', $request->input('per_page', 10)) }},
                '{{ old('sort', $request->input('sort', 'updated_at')) }}',
                '{{ old('direction', $request->input('direction', 'desc')) }}',
                {{-- Pass ALL initial totals from Controller --}}
                {{ $totalFilteredTaskPayroll ?? 0 }},      // Filtered Hak Gaji Task
                {{ $totalFilteredOtherPayments ?? 0 }},   // Filtered Hak Gaji Other
                {{ $totalOverallTaskPayroll ?? 0 }},      // Overall Hak Gaji Task
                {{ $totalOverallOtherPayments ?? 0 }},    // Overall Hak Gaji Other
                {{ $totalFilteredPaidTaskAmount ?? 0 }},   // Filtered Paid Task
                {{ $totalFilteredPaidOtherAmount ?? 0 }},  // Filtered Paid Other
                {{ $totalOverallPaidTaskAmount ?? 0 }},    // Overall Paid Task
                {{ $totalOverallPaidOtherAmount ?? 0 }}   // Overall Paid Other
            )"
         x-init="init()">

        {{-- Header Halaman & Tombol Aksi --}}
        <div class="mb-6 flex justify-between items-center no-print">
            <h2 class="text-2xl font-semibold text-gray-900">Perhitungan Penggajian - {{ $project->name }}</h2>
            {{-- Tombol Print & Export --}}
            <div class="flex space-x-2">
                <button @click="printReport()" title="Cetak Laporan" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
                 <button @click="exportToPdf()" :disabled="isExportingPdf" title="Ekspor ke PDF" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-500 hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                    {{-- Ikon PDF --}}
                    <svg x-show="!isExportingPdf" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{-- Ikon Loading --}}
                     <svg x-show="isExportingPdf" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                         <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                         <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                     </svg>
                    <span x-text="isExportingPdf ? 'Mengekspor...' : 'Export PDF'"></span>
                </button>
            </div>
        </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6 no-print">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                {{-- 1. Perhitungan Gaji --}}
                <a href="{{ route('projects.payroll.calculate', $project) }}"
                   class="{{ request()->routeIs('projects.payroll.calculate') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                   @if(request()->routeIs('projects.payroll.calculate')) aria-current="page" @endif>
                    Perhitungan Gaji
                </a>
                {{-- 2. Buat & Sahkan Slip --}}
                 {{-- Cek juga route detail payslip karena mungkin berada di bawah "Buat & Sahkan" secara logis --}}
                <a href="{{ route('projects.payslips.create', $project) }}"
                   class="{{ (request()->routeIs('projects.payslips.create') || request()->routeIs('projects.payslips.show')) ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    @if(request()->routeIs('projects.payslips.create') || request()->routeIs('projects.payslips.show')) aria-current="page" @endif>
                     Pembuatan Slip Gaij
                </a>
                {{-- 3. Riwayat Slip Gaji --}}
                 <a href="{{ route('projects.payslips.history', $project) }}"
                    class="{{ request()->routeIs('projects.payslips.history') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    @if(request()->routeIs('projects.payslips.history')) aria-current="page" @endif>
                     Riwayat Slip Gaji
                 </a>
            </nav>
        </div>

        <!-- Weight Info -->
         <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-md text-sm text-indigo-700 flex justify-between items-center no-print">
             <span>
                 Bobot Aktif: Kesulitan = <strong>{{ $project->difficulty_weight }}%</strong>, Prioritas = <strong>{{ $project->priority_weight }}%</strong>.
             </span>
             <div>
                  <a href="{{ route('projects.settings.weights.edit', $project) }}" class="ml-2 font-medium hover:underline">(Ubah Bobot)</a>
                  <a href="{{ route('projects.settings.levels.manage', $project) }}" class="ml-4 font-medium hover:underline">(Kelola Level)</a>
                  <a href="{{ route('projects.wage-standards.index', $project) }}" class="ml-4 font-medium hover:underline">(Kelola Standar Upah)</a>
             </div>
         </div>

        <!-- Filters, Search, and Controls -->
        <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200 no-print">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Worker Filter --}}
                <div>
                    <label for="worker_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                    <select name="worker_id" id="worker_id" x-model="filters.worker_id" @change="applyFilters()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="all">Semua Pekerja</option>
                        @foreach ($workers as $worker)
                            <option value="{{ $worker->id }}" {{ old('worker_id', $request->input('worker_id')) == $worker->id ? 'selected' : '' }}>
                                {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Task Payment Status Filter --}}
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700">Status Pembayaran Task</label>
                    <select name="payment_status" id="payment_status" x-model="filters.payment_status" @change="applyFilters()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="all">Semua Status</option>
                        <option value="unpaid" {{ old('payment_status', $request->input('payment_status')) == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                        <option value="paid" {{ old('payment_status', $request->input('payment_status')) == 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                    </select>
                </div>

                {{-- Search Input --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Cari Task / Pekerja / Bonus</label>
                    <input type="text" name="search" id="search"
                           x-model="filters.search"
                           @input.debounce.500ms="applyFilters()"
                           placeholder="Masukkan kata kunci..."
                           value="{{ old('search', $request->input('search', '')) }}"
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                {{-- Per Page Selector --}}
                <div>
                     <label for="per_page" class="block text-sm font-medium text-gray-700">Item per Halaman (Task)</label>
                     <select name="per_page" id="per_page" x-model="filters.per_page" @change="applyFilters()"
                             class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                         @foreach ($perPageOptions as $option)
                             <option value="{{ $option }}" {{ old('per_page', $request->input('per_page', 10)) == $option ? 'selected' : '' }}>{{ $option }}</option>
                         @endforeach
                     </select>
                 </div>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                 <button type="button" @click="resetFilters()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                     Reset Filter
                 </button>
            </div>
        </div>

        {{-- Wrapper untuk Area Cetak/Export --}}
        <div id="printable-content">

            {{-- Judul Report (Hanya untuk cetak/pdf) --}}
             <div class="hidden print:block mb-4"> {{-- print:block membuatnya hanya tampil saat print --}}
                 <h2 class="text-xl font-bold text-center">Laporan Perhitungan Penggajian</h2>
                 <p class="text-sm text-center">Proyek: {{ $project->name }}</p>
                 <p class="text-sm text-center">Tanggal Cetak/Export: {{ now()->format('d M Y H:i') }}</p>
                 {{-- Info Filter Aktif Saat Cetak (ambil dari Alpine state) --}}
                 <div class="text-xs mt-2 text-gray-600 border-t border-b border-gray-300 py-1 my-2">
                     <p class="font-medium">Filter Aktif:</p>
                     <ul class="list-none pl-0">
                         <li>Pekerja: <span x-text="filters.worker_id === 'all' ? 'Semua' : (document.getElementById('worker_id')?.options[document.getElementById('worker_id')?.selectedIndex]?.text || filters.worker_id)"></span></li>
                         <li>Status Task: <span x-text="filters.payment_status === 'all' ? 'Semua' : (filters.payment_status === 'paid' ? 'Dibayar' : 'Belum Dibayar')"></span></li>
                         <li>Pencarian: <span x-text="filters.search || '-'"></span></li>
                         <li>Urutan: <span x-text="filters.sort + ' (' + filters.direction + ')'"></span></li>
                     </ul>
                 </div>
             </div>

            <!-- Task Table Area -->
            <h3 class="text-lg font-medium text-gray-800 mb-3 print:text-base">Rekap Task</h3>
            {{-- ID payroll-table-container tetap diperlukan untuk Alpine $ref --}}
            <div id="payroll-table-container" x-ref="tableContainer">
                {{-- Loading Indicator (disembunyikan saat print) --}}
                <div x-show="loading" class="text-center py-10 no-print">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-600 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600">Memuat data task...</span>
                </div>
                {{-- Table Content Area (akan berisi tabel dari _payroll_table_content.blade.php) --}}
                <div x-show="!loading" x-html="tableHtml">
                    {{-- Initial server render --}}
                    @include('penggajian._payroll_table_content', ['tasks' => $tasks, 'project' => $project, 'request' => $request])
                </div>
            </div>

            <!-- Summary Section -->
            <div id="summary-section" class="mt-8 bg-white shadow-none print:shadow-none print:border print:border-gray-300 overflow-hidden sm:rounded-md p-6 print:p-4">
                 <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base">Ringkasan Penggajian</h3>

                 {{-- Section 1: Filtered Amounts --}}
                 <div class="mb-6 pb-4 print:mb-4 print:pb-2 border-b border-gray-200">
                     <h4 class="text-md font-semibold text-gray-700 mb-3 print:text-sm">Ringkasan Berdasarkan Filter Aktif</h4>
                     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm summary-grid">
                         {{-- Filtered Task Value --}}
                         <div class="border border-gray-200 rounded-md p-3">
                             <dt class="text-gray-500 truncate">Total Nilai Task</dt>
                             <dd class="mt-1 text-xl print:text-base font-semibold text-blue-600" x-text="formatCurrency(totals.filteredTask)"></dd>
                         </div>
                         {{-- Filtered Other Value --}}
                         <div class="border border-gray-200 rounded-md p-3">
                             <dt class="text-gray-500 truncate">Total Bonus/Lainnya</dt>
                             <dd class="mt-1 text-xl print:text-base font-semibold text-purple-600" x-text="formatCurrency(totals.filteredOther)"></dd>
                         </div>
                         {{-- Filtered Total Hak Gaji --}}
                         <div class="border border-indigo-200 bg-indigo-50 rounded-md p-3">
                             <dt class="text-indigo-800 truncate font-medium">Total Hak Gaji</dt>
                             <dd class="mt-1 text-xl print:text-base font-bold text-indigo-700" x-text="formatCurrency(totals.filteredTask + totals.filteredOther)"></dd>
                         </div>
                         {{-- Filtered Total Paid --}}
                         <div class="border border-green-200 bg-green-50 rounded-md p-3">
                             <dt class="text-green-800 truncate font-medium">Total Sudah Dibayar</dt>
                             <dd class="mt-1 text-xl print:text-base font-bold text-green-700" x-text="formatCurrency(totals.filteredPaidTask + totals.filteredPaidOther)"></dd>
                             <p class="text-xs text-green-600 mt-1">
                                 Task: <span x-text="formatCurrency(totals.filteredPaidTask)"></span><br>
                                 Bonus/Lain: <span x-text="formatCurrency(totals.filteredPaidOther)"></span>
                             </p>
                         </div>
                     </div>
                     {{-- Worker Filter Info (disembunyikan saat print) --}}
                     <p class="text-xs text-gray-500 italic mt-2 no-print">
                        @if($request->input('worker_id') && $request->input('worker_id') !== 'all')
                            @php $selectedWorker = $workers->firstWhere('id', $request->input('worker_id')); @endphp
                            Menampilkan ringkasan filter untuk pekerja: <strong>{{ $selectedWorker->name ?? 'N/A' }}</strong>.
                        @else
                            Menampilkan ringkasan filter untuk semua pekerja.
                        @endif
                     </p>
                 </div>

                 {{-- Section 2: Overall Amounts & Budget (Sembunyikan saat print sederhana) --}}
                 <div class="no-print">
                     <h4 class="text-md font-semibold text-gray-700 mb-3">Ringkasan Keseluruhan Proyek</h4>
                     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm summary-grid">
                         {{-- Overall Task Value --}}
                         <div class="border border-gray-200 rounded-md p-3">
                             <dt class="text-gray-500 truncate">Total Nilai Task</dt>
                             <dd class="mt-1 text-lg font-semibold text-gray-700" x-text="formatCurrency(totals.overallTask)"></dd>
                         </div>
                         {{-- Overall Other Value --}}
                         <div class="border border-gray-200 rounded-md p-3">
                             <dt class="text-gray-500 truncate">Total Bonus/Lainnya</dt>
                             <dd class="mt-1 text-lg font-semibold text-gray-700" x-text="formatCurrency(totals.overallOther)"></dd>
                         </div>
                         {{-- Overall Total Hak Gaji --}}
                         <div class="border border-gray-300 bg-gray-50 rounded-md p-3">
                             <dt class="text-gray-600 truncate font-medium">Total Hak Gaji</dt>
                             <dd class="mt-1 text-lg font-semibold text-gray-800" x-text="formatCurrency(totals.overallTask + totals.overallOther)"></dd>
                         </div>
                         {{-- Overall Total Paid --}}
                         <div class="border border-green-300 bg-green-50 rounded-md p-3">
                             <dt class="text-green-800 truncate font-medium">Total Sudah Dibayar</dt>
                             <dd class="mt-1 text-lg font-semibold text-green-700" x-text="formatCurrency(totals.overallPaidTask + totals.overallPaidOther)"></dd>
                             <p class="text-xs text-green-600 mt-1">
                                 Task: <span x-text="formatCurrency(totals.overallPaidTask)"></span><br>
                                 Bonus/Lain: <span x-text="formatCurrency(totals.overallPaidOther)"></span>
                             </p>
                         </div>
                         {{-- Budget --}}
                         <div class="border border-gray-200 rounded-md p-3">
                            <dt class="text-gray-500 truncate">Estimasi Budget</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">Rp {{ number_format($project->budget, 0, ',', '.') }}</dd>
                        </div>
                         {{-- Budget Difference --}}
                          @php
                               $totalOverallCombined = ($totalOverallTaskPayroll ?? 0) + ($totalOverallOtherPayments ?? 0);
                               $budgetDifferenceCombined = ($project->budget ?? 0) - $totalOverallCombined;
                          @endphp
                         <div class="border rounded-md p-3 {{ $budgetDifferenceCombined >= 0 ? 'border-yellow-300 bg-yellow-50' : 'border-red-300 bg-red-50' }}">
                             <dt class="text-gray-500 truncate">Sisa / Lebih Budget</dt>
                             <dd class="mt-1 text-lg font-semibold {{ $budgetDifferenceCombined >= 0 ? 'text-yellow-700' : 'text-red-700' }}">
                                 Rp {{ number_format(abs($budgetDifferenceCombined), 0, ',', '.') }}
                                 ({{ $budgetDifferenceCombined >= 0 ? 'Sisa' : 'Melebihi' }})
                             </dd>
                             <p class="text-xs {{ $budgetDifferenceCombined >= 0 ? 'text-yellow-600' : 'text-red-600' }} mt-1">Dibandingkan total hak gaji.</p>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
        {{-- Akhir Wrapper Area Cetak/Export --}}

        {{-- Link to Payment Page (disembunyikan saat print) --}}
        <div class="mt-6 text-center text-sm text-gray-600 no-print">
             Pergi ke halaman <a href="{{ route('projects.payslips.create', $project) }}" class="text-indigo-600 hover:underline font-medium">Pembuatan Slip Gaji</a> untuk membuat slip gaji atau membayar bonus/lainnya.
         </div>

    </div>

    {{-- Alpine.js Script & html2pdf Script --}}
    @push('scripts')
    {{-- CDN untuk html2pdf.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Alpine component function
        function payrollCalculator(baseUrl, initialWorker, initialStatus, initialSearch, initialPerPage, initialSort, initialDirection,
                                   initialTotalFilteredTask, initialTotalFilteredOther, initialTotalOverallTask, initialTotalOverallOther,
                                   initialTotalFilteredPaidTask, initialTotalFilteredPaidOther, // Filtered paid params
                                   initialTotalOverallPaidTask, initialTotalOverallPaidOther) // Overall paid params
        {
            return {
                // State properties
                baseUrl: baseUrl,
                filters: {
                    worker_id: initialWorker,
                    payment_status: initialStatus,
                    search: initialSearch,
                    per_page: parseInt(initialPerPage) || 10,
                    sort: initialSort,
                    direction: initialDirection,
                    page: 1,
                },
                loading: false,
                tableHtml: '', // Holds the HTML for the task table
                totals: {
                    filteredTask: parseFloat(initialTotalFilteredTask) || 0,
                    filteredOther: parseFloat(initialTotalFilteredOther) || 0,
                    overallTask: parseFloat(initialTotalOverallTask) || 0,
                    overallOther: parseFloat(initialTotalOverallOther) || 0,
                    filteredPaidTask: parseFloat(initialTotalFilteredPaidTask) || 0,
                    filteredPaidOther: parseFloat(initialTotalFilteredPaidOther) || 0,
                    overallPaidTask: parseFloat(initialTotalOverallPaidTask) || 0,
                    overallPaidOther: parseFloat(initialTotalOverallPaidOther) || 0
                },
                currentUrl: '', // Tracks the current URL for history management
                isExportingPdf: false, // State for PDF export loading indicator

                // Initialization logic
                init() {
                    this.tableHtml = this.$refs.tableContainer.querySelector('[x-html="tableHtml"]').innerHTML;
                    this.currentUrl = this.buildUrl();
                    history.replaceState({ ...this.filters }, '', this.currentUrl);
                    // Event listener for pagination
                    this.$refs.tableContainer.addEventListener('click', (e) => {
                        const paginationLink = e.target.closest('.pagination a, a.relative[href*="page="]');
                        if (paginationLink && paginationLink.href) {
                            e.preventDefault();
                            this.goToPage(paginationLink.href);
                        }
                    });
                    // Event listener for browser navigation
                    window.addEventListener('popstate', (event) => {
                         if (event.state) {
                             this.filters = { ...this.filters, ...event.state };
                             this.filters.per_page = parseInt(this.filters.per_page) || 10;
                             this.filters.page = parseInt(this.filters.page) || 1;
                             this.fetchData(false);
                         }
                    });
                },

                // Builds the URL with current filters
                buildUrl() {
                    const activeFilters = {};
                    for (const key in this.filters) {
                        if (this.filters[key] !== null && this.filters[key] !== '' && this.filters[key] !== 'all') {
                            if (key === 'page' && this.filters[key] === 1) continue;
                            activeFilters[key] = this.filters[key];
                        }
                    }
                    const params = new URLSearchParams(activeFilters).toString();
                    return `${this.baseUrl}${params ? '?' + params : ''}`;
                },

                // Fetches data from the server via AJAX
                fetchData(updateHistory = true) {
                    this.loading = true;
                    const fetchUrl = this.buildUrl();
                    fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().catch(() => response.text()).then(errData => {
                                let errMsg = `Error ${response.status}: ${response.statusText}`;
                                if (typeof errData === 'string' && errData.length < 500) errMsg += ` - ${errData}`;
                                else if (errData && errData.message) errMsg += ` - ${errData.message}`;
                                console.error("Fetch error details:", errData);
                                throw new Error(errMsg);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.tableHtml = data.html;
                        this.totals.filteredTask = parseFloat(data.totalFilteredTaskPayroll) || 0;
                        this.totals.filteredOther = parseFloat(data.totalFilteredOtherPayments) || 0;
                        this.totals.filteredPaidTask = parseFloat(data.totalPaidTaskAmount) || 0;
                        this.totals.filteredPaidOther = parseFloat(data.totalPaidOtherAmount) || 0;
                        // Overall totals not updated by fetch
                        if (updateHistory && fetchUrl !== this.currentUrl) {
                             history.pushState({ ...this.filters }, '', fetchUrl);
                             this.currentUrl = fetchUrl;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching payroll data:', error);
                        this.tableHtml = `<div class="text-red-600 bg-red-100 p-4 rounded text-center">Gagal memuat data: ${error.message}. Silakan coba lagi atau cek log server.</div>`;
                        this.totals.filteredTask = 0;
                        this.totals.filteredOther = 0;
                        this.totals.filteredPaidTask = 0;
                        this.totals.filteredPaidOther = 0;
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                },

                // Applies filters and fetches data
                applyFilters() {
                    this.filters.page = 1;
                    this.fetchData();
                },

                // Handles sorting request
                sortBy(field) {
                    const validDbSortFields = [
                        'title', 'assigned_user_name', 'difficulty_value', 'priority_value',
                        'achievement_percentage', 'payment_status', 'updated_at'
                     ];
                    if (!validDbSortFields.includes(field)) {
                        console.warn(`Sorting by field "${field}" is not implemented in the backend query.`);
                        return;
                    }
                    let newDirection = 'asc';
                    if (this.filters.sort === field && this.filters.direction === 'asc') {
                        newDirection = 'desc';
                    }
                    this.filters.sort = field;
                    this.filters.direction = newDirection;
                    this.filters.page = 1;
                    this.fetchData();
                },

                // Handles pagination link clicks
                goToPage(url) {
                    try {
                        const targetUrl = new URL(url);
                        const page = targetUrl.searchParams.get('page') || 1;
                        this.filters.page = parseInt(page);
                        this.fetchData();
                    } catch (e) {
                        console.error("Invalid URL for pagination:", url, e);
                    }
                },

                // Resets all filters to default values
                resetFilters() {
                    const defaultPerPage = 10;
                    const defaultSort = 'updated_at';
                    const defaultDirection = 'desc';
                    this.filters.worker_id = 'all';
                    this.filters.payment_status = 'all';
                    this.filters.search = '';
                    this.filters.per_page = defaultPerPage;
                    this.filters.sort = defaultSort;
                    this.filters.direction = defaultDirection;
                    this.filters.page = 1;
                    this.fetchData();
                },

                // Formats a number as Indonesian Rupiah currency
                formatCurrency(value) {
                    if (value === null || value === undefined || isNaN(value)) return 'Rp 0';
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value);
                },

                // --- Print & Export Functions ---
                printReport() {
                    window.print(); // Trigger browser print dialog
                },

                exportToPdf() {
                    if (this.isExportingPdf) return; // Prevent multiple clicks
                    this.isExportingPdf = true;
                    const element = document.getElementById('printable-content');
                    if (!element) {
                        console.error("Element #printable-content not found!");
                        this.isExportingPdf = false;
                        return;
                    }

                    // Generate filename
                    const workerSelect = document.getElementById('worker_id');
                    const workerName = this.filters.worker_id === 'all' ? 'SemuaPekerja' : (workerSelect?.options[workerSelect?.selectedIndex]?.text.replace(/[^a-zA-Z0-9]/g, '-') || 'Pekerja');
                    const date = new Date().toISOString().slice(0, 10);
                    const filename = `Laporan-Penggajian-${workerName}-${date}.pdf`;

                    // html2pdf options
                    const opt = {
                      margin:       [10, 10, 15, 10], // top, left, bottom, right (mm)
                      filename:     filename,
                      image:        { type: 'jpeg', quality: 0.98 }, // Image settings
                      html2canvas:  { scale: 2, useCORS: true, logging: false }, // Canvas settings
                      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' } // PDF settings
                    };

                    // Generate PDF
                    html2pdf().set(opt).from(element).save().then(() => {
                         console.log("PDF Exported Successfully");
                         this.isExportingPdf = false;
                    }).catch(err => {
                         console.error("Error exporting PDF:", err);
                         alert("Gagal mengekspor PDF. Silakan cek konsol browser untuk detail.");
                         this.isExportingPdf = false;
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>