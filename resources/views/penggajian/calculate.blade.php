{{-- resources/views/penggajian/calculate.blade.php --}}
<x-app-layout>
    {{-- Include AlpineJS if not globally included --}}
    {{--
    <script src="//unpkg.com/alpinejs" defer></script> --}}

    {{-- CSS Khusus untuk Print --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
                /* Sembunyikan semua elemen secara default */
                -webkit-print-color-adjust: exact !important;
                /* Chrome, Safari */
                color-adjust: exact !important;
                /* Firefox, Edge */
            }

            /* Tampilkan hanya area yang ingin dicetak dan anak-anaknya */
            #printable-content,
            #printable-content * {
                visibility: visible;
            }

            /* Posisikan area cetak di sudut kiri atas halaman */
            #printable-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                /* Lebar penuh kertas */
                margin: 0 !important;
                padding: 15px !important;
                /* Beri sedikit padding */
                border: none !important;
                box-shadow: none !important;
                background-color: white !important;
                /* Pastikan background putih */
            }

            /* Sembunyikan elemen UI yang tidak relevan */
            .no-print,
            .no-print * {
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
                font-size: 1.1rem;
                /* Sedikit perbesar judul */
                color: black !important;
            }

            #printable-content table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 9pt !important;
                /* Perkecil font tabel */
                color: black !important;
            }

            #printable-content thead {
                display: table-header-group;
                /* Pastikan thead berulang di setiap halaman */
            }

            #printable-content th {
                background-color: #f2f2f2 !important;
                /* Warna background header */
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            #printable-content th,
            #printable-content td {
                border: 1px solid #ddd !important;
                padding: 4px 6px !important;
                /* Padding lebih kecil */
                text-align: left !important;
                color: black !important;
                background-color: white !important;
                /* Pastikan background sel putih */
                word-wrap: break-word;
                /* Pecah kata jika terlalu panjang */
            }

            #printable-content td span[class*="bg-"] {
                /* Hapus background dari status badge */
                background-color: transparent !important;
                padding: 0 !important;
                border-radius: 0 !important;
                border: 1px solid #ccc !important;
                /* Ganti dengan border tipis */
                color: black !important;
                /* Warna teks badge */
            }

            #printable-content .summary-grid {
                /* Target grid summary */
                grid-template-columns: repeat(2, 1fr) !important;
                /* 2 kolom saat print */
                font-size: 9pt !important;
                margin-top: 1rem !important;
                page-break-inside: avoid;
                /* Hindari summary terpotong antar halaman */
            }

            #printable-content .summary-grid>div {
                padding: 8px !important;
                border: 1px solid #eee !important;
                background-color: white !important;
                page-break-inside: avoid;
                /* Hindari item summary terpotong */
            }

            #printable-content .summary-grid>div[class*="bg-"] {
                /* Styling khusus untuk box berwarna */
                background-color: #f0f0f0 !important;
                /* Ganti dengan abu-abu muda */
                border-color: #ccc !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            #printable-content .summary-grid>div[class*="bg-"] dt,
            #printable-content .summary-grid>div[class*="bg-"] dd,
            #printable-content .summary-grid>div[class*="bg-"] span,
            #printable-content .summary-grid>div[class*="bg-"] p {
                color: black !important;
                /* Pastikan teks di box berwarna terbaca */
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

            #printable-content a {
                /* Styling link saat print */
                color: #0066cc !important;
                /* Warna biru standar */
                text-decoration: none !important;
                /* Hilangkan underline */
            }

            #printable-content svg {
                /* Sembunyikan ikon SVG */
                display: none !important;
            }
        }
    </style>

    <div class="py-6 px-4 sm:px-6 lg:px-8" x-data="payrollCalculator(
                '{{ route('projects.payroll.calculate', $project) }}',
                '{{ old('worker_id', $request->input('worker_id', ($isProjectOwner ? 'all' : auth()->id()))) }}',
                '{{ old('payment_status', $request->input('payment_status', 'all')) }}',
                '{{ old('search', $request->input('search', '')) }}',
                {{ old('per_page', $request->input('per_page', 10)) }},
                '{{ old('sort', $request->input('sort', 'updated_at')) }}',
                '{{ old('direction', $request->input('direction', 'desc')) }}',
                {{ $totalFilteredTaskPayroll ?? 0 }},
                {{ $totalFilteredOtherPayments ?? 0 }},
                {{ $totalOverallTaskPayroll ?? 0 }},
                {{ $totalOverallOtherPayments ?? 0 }},
                {{ $totalFilteredPaidTaskAmount ?? 0 }},
                {{ $totalFilteredPaidOtherAmount ?? 0 }},
                {{ $totalOverallPaidTaskAmount ?? 0 }},
                {{ $totalOverallPaidOtherAmount ?? 0 }}
            )" x-init="init()">

        {{-- Header Halaman & Tombol Aksi --}}
        <div class="mb-6 flex justify-between items-center no-print">
            <h2 class="text-2xl font-semibold text-gray-900">Perhitungan Penggajian - {{ $project->name }}</h2>
            <div class="flex space-x-2">
                <button @click="printReport()" title="Cetak Laporan"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /> </svg>
                    Print
                </button>
                <button @click="exportToPdf()" :disabled="isExportingPdf" title="Ekspor ke PDF"
                    class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-gray-700 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50">
                    <svg x-show="!isExportingPdf" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /> </svg>
                    <svg x-show="isExportingPdf" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"> </circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"> </path> </svg>
                    <span x-text="isExportingPdf ? 'Mengekspor...' : 'Export PDF'"></span>
                </button>
                @can('create', [App\Models\Payment::class, $project]) {{-- Pastikan policy create Payment ada --}}
                <button @click="$dispatch('open-create-payslip-modal')"
                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /> </svg>
                    Buat Slip Gaji
                </button>
                @endcan
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6 no-print">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('projects.payroll.calculate', $project) }}"
                    class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    aria-current="page">
                    Perhitungan Gaji
                </a>
                <a href="{{ route('projects.payslips.history', $project) }}"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Daftar Slip Gaji
                </a>
            </nav>
        </div>

        {{-- Filters, Search, and Controls (Tidak Berubah) ... --}}
        <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200 no-print">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Worker Filter --}}
                <div>
                    <label for="worker_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                    <select name="worker_id" id="worker_id" x-model="filters.worker_id" @change="applyFilters()"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        {{ !$isProjectOwner ? 'disabled' : '' }}> {{-- Disable jika bukan owner --}}
                        @if($isProjectOwner)
                            <option value="all">Semua Pekerja</option>
                        @endif
                        @foreach ($workersForFilter as $worker)
                            <option value="{{ $worker->id }}">
                                {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$isProjectOwner)
                        <p class="text-xs text-gray-500 mt-1 italic">Menampilkan data untuk Anda.</p>
                    @endif
                </div>

                {{-- Task Payment Status Filter --}}
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700">Status Pembayaran Task</label>
                    <select name="payment_status" id="payment_status" x-model="filters.payment_status" @change="applyFilters()"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="all">Semua Status</option>
                        <option value="unpaid">Belum Dibayar</option>
                        <option value="paid">Sudah Dibayar</option>
                    </select>
                </div>

                {{-- Search Input --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Cari Task / Pekerja / Bonus</label>
                    <input type="text" name="search" id="search" x-model="filters.search" @input.debounce.500ms="applyFilters()" placeholder="Masukkan kata kunci..."
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                {{-- Per Page Selector --}}
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700">Item per Halaman (Task)</label>
                    <select name="per_page" id="per_page" x-model="filters.per_page" @change="applyFilters()"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button type="button" @click="resetFilters()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset Filter
                </button>
            </div>
        </div>

        <div id="printable-content">
            
            {{-- Ringkasan Penggajian (Tidak Berubah) ... --}}
            <div id="summary-section" class="mt-8 bg-white shadow-none print:shadow-none print:border print:border-gray-300 overflow-hidden sm:rounded-md p-6 print:p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base">Ringkasan Penggajian</h3>
                <div class="mb-6 pb-4 print:mb-4 print:pb-2 border-b border-gray-200">
                    <h4 class="text-md font-semibold text-gray-700 mb-3 print:text-sm">
                        Ringkasan Gaji
                        @if(!$isProjectOwner && Auth::user())
                            untuk {{ Auth::user()->name }}
                        @elseif($isProjectOwner && $request->input('worker_id') && $request->input('worker_id') !== 'all')
                            untuk {{ $workersForFilter->firstWhere('id', $request->input('worker_id'))->name ?? 'Pekerja Terpilih' }}
                        @elseif($isProjectOwner)
                            (Berdasarkan Filter Aktif)
                        @endif
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm summary-grid">
                        <div class="border border-gray-200 rounded-md p-3">
                            <dt class="text-gray-500 truncate">Total Nilai Task</dt>
                            <dd class="mt-1 text-xl print:text-base font-semibold text-blue-600" x-text="formatCurrency(totals.filteredTask)"></dd>
                        </div>
                        <div class="border border-gray-200 rounded-md p-3">
                            <dt class="text-gray-500 truncate">Total Bonus/Lainnya</dt>
                            <dd class="mt-1 text-xl print:text-base font-semibold text-purple-600" x-text="formatCurrency(totals.filteredOther)"></dd>
                        </div>
                        <div class="border border-green-200 bg-green-50 rounded-md p-3">
                            <dt class="text-green-800 truncate font-medium">Total Sudah Dibayar</dt>
                            <dd class="mt-1 text-xl print:text-base font-bold text-green-700" x-text="formatCurrency(totals.filteredPaidTask + totals.filteredPaidOther)"></dd>
                            <p class="text-xs text-green-600 mt-1">
                                Task: <span x-text="formatCurrency(totals.filteredPaidTask)"></span><br>
                                Bonus/Lain: <span x-text="formatCurrency(totals.filteredPaidOther)"></span>
                            </p>
                        </div>
                        @if($isProjectOwner) {{-- Budget Difference hanya relevan untuk owner --}}
                            <div class="border rounded-md p-3 {{ $budgetDifference >= 0 ? 'border-yellow-300 bg-yellow-50' : 'border-red-300 bg-red-50' }}">
                                <dt class="text-gray-500 truncate">Sisa / Lebih Budget Proyek</dt>
                                <dd class="mt-1 text-lg font-semibold {{ $budgetDifference >= 0 ? 'text-yellow-700' : 'text-red-700' }}">
                                    Rp {{ number_format(abs($budgetDifference), 0, ',', '.') }}
                                    ({{ $budgetDifference >= 0 ? 'Sisa' : 'Melebihi' }})
                                </dd>
                                <p class="text-xs {{ $budgetDifference >= 0 ? 'text-yellow-600' : 'text-red-600' }} mt-1">
                                    Dibandingkan total estimasi hak gaji keseluruhan proyek.
                                </p>
                            </div>
                        @else
                             <div class="border border-transparent rounded-md p-3"> </div>
                        @endif
                    </div>
                    <!-- @if($isProjectOwner)
                        <p class="text-xs text-gray-500 italic mt-2 no-print">
                            @if($request->input('worker_id') && $request->input('worker_id') !== 'all')
                                Menampilkan ringkasan filter untuk pekerja:
                                <strong>{{ $workersForFilter->firstWhere('id', $request->input('worker_id'))->name ?? 'N/A' }}</strong>.
                            @else
                                Menampilkan ringkasan filter untuk semua pekerja.
                            @endif
                        </p>
                    @endif -->
                </div>
            </div>
            {{-- Header Cetak (Tidak Berubah) ... --}}
             <div class="hidden print:block mb-4">
                <h2 class="text-xl font-bold text-center">Laporan Perhitungan Penggajian</h2>
                <p class="text-sm text-center">Proyek: {{ $project->name }}</p>
                <p class="text-sm text-center">Tanggal Cetak/Export: <span x-text="new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></span></p>
                <div class="text-xs mt-2 text-gray-600 border-t border-b border-gray-300 py-1 my-2">
                    <p class="font-medium">Filter Aktif:</p>
                    <ul class="list-none pl-0">
                        <li>Pekerja: <span
                                x-text="filters.worker_id === 'all' || {{ Js::from(!$isProjectOwner) }} ? ({{ Js::from(!$isProjectOwner) }} ? '{{ Auth::user()->name }}' : 'Semua Pekerja') : (document.getElementById('worker_id')?.options[document.getElementById('worker_id')?.selectedIndex]?.text || filters.worker_id)"></span>
                        </li>
                        <li>Status Task: <span
                                x-text="filters.payment_status === 'all' ? 'Semua' : (filters.payment_status === 'paid' ? 'Dibayar' : 'Belum Dibayar')"></span>
                        </li>
                        <li>Pencarian: <span x-text="filters.search || '-'"></span></li>
                        <li>Urutan: <span x-text="filters.sort + ' (' + filters.direction + ')'"></span></li>
                    </ul>
                </div>
            </div>

            <h3 class="text-lg font-medium text-gray-800 mb-3 print:text-base pt-4">Rekap Task</h3>
            <div id="payroll-table-container" x-ref="tableContainer">
                <div x-show="loading" class="text-center py-10 no-print">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-600 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"> </circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"> </path> </svg>
                    <span class="text-gray-600">Memuat data task...</span>
                </div>
                <div x-show="!loading" x-html="tableHtml">
                    @include('penggajian._payroll_table_content', ['tasks' => $tasks, 'project' => $project, 'request' => $request])
                </div>
            </div>
        </div>
        {{-- Pesan link ke pembuatan slip gaji (hanya untuk owner) ditiadakan karena tombol sudah ada --}}
    </div>

    {{-- Include Modal --}}
     @include('payslips.partials._create_modal_content', [
         'project' => $project,
         'modalWorkers' => $modalWorkers, // Pastikan variabel ini di-pass dari controller
         'modalPaymentCalculationType' => $modalPaymentCalculationType,
         'modalPaymentTerms' => $modalPaymentTerms,
         'modalUnpaidTasksGrouped' => $modalUnpaidTasksGrouped,
         'modalDefaultTerminName' => $modalDefaultTerminName
     ])


    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
            integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        {{-- JavaScript untuk Payroll Calculator --}}
        <script>
            function payrollCalculator(baseUrl, initialWorker, initialStatus, initialSearch, initialPerPage, initialSort, initialDirection,
                initialTotalFilteredTask, initialTotalFilteredOther, initialTotalOverallTask, initialTotalOverallOther,
                initialTotalFilteredPaidTask, initialTotalFilteredPaidOther,
                initialTotalOverallPaidTask, initialTotalOverallPaidOther)
            {
                return {
                    // ... (properti dan method yang ada di payrollCalculator tetap sama) ...
                    // ... (seperti filters, loading, tableHtml, totals, init, buildUrl, fetchData, applyFilters, sortBy, goToPage, resetFilters, formatCurrency, printReport, exportToPdf)
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
                    tableHtml: '', // Ini akan diisi oleh AJAX
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
                    currentUrl: '',
                    isExportingPdf: false,
                    isProjectOwner: {{ Js::from($isProjectOwner) }},

                    init() {
                        this.tableHtml = this.$refs.tableContainer.querySelector('[x-html="tableHtml"]').innerHTML;
                         if (!this.isProjectOwner) { this.filters.worker_id = '{{ auth()->id() }}'; }
                        this.currentUrl = this.buildUrl();
                        history.replaceState({ ...this.filters }, '', this.currentUrl);

                        this.$refs.tableContainer.addEventListener('click', (e) => {
                            const paginationLink = e.target.closest('.pagination a, a.relative[href*="page="]');
                            if (paginationLink && paginationLink.href) {
                                e.preventDefault();
                                this.goToPage(paginationLink.href);
                            }
                        });
                        window.addEventListener('popstate', (event) => {
                            if (event.state) {
                                this.filters = { ...this.filters, ...event.state };
                                if (!this.isProjectOwner) { this.filters.worker_id = '{{ auth()->id() }}'; }
                                this.filters.per_page = parseInt(this.filters.per_page) || 10;
                                this.filters.page = parseInt(this.filters.page) || 1;
                                this.fetchData(false);
                            }
                        });
                    },

                    buildUrl() {
                        const activeFilters = {};
                        for (const key in this.filters) {
                            if (!this.isProjectOwner && key === 'worker_id') { activeFilters[key] = '{{ auth()->id() }}'; continue; }
                            if (this.filters[key] !== null && this.filters[key] !== '' && this.filters[key] !== 'all') {
                                if (key === 'page' && this.filters[key] === 1) continue;
                                activeFilters[key] = this.filters[key];
                            }
                        }
                        const params = new URLSearchParams(activeFilters).toString();
                        return `${this.baseUrl}${params ? '?' + params : ''}`;
                    },

                    fetchData(updateHistory = true) {
                        this.loading = true;
                        const fetchUrl = this.buildUrl();
                        fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                            .then(response => {
                                if (!response.ok) return response.json().catch(() => response.text()).then(errData => { /* ... error handling ... */ throw new Error('Fetch error'); });
                                return response.json();
                            })
                            .then(data => {
                                this.tableHtml = data.html;
                                this.totals.filteredTask = parseFloat(data.totalFilteredTaskPayroll) || 0;
                                this.totals.filteredOther = parseFloat(data.totalFilteredOtherPayments) || 0;
                                this.totals.filteredPaidTask = parseFloat(data.totalPaidTaskAmount) || 0;
                                this.totals.filteredPaidOther = parseFloat(data.totalPaidOtherAmount) || 0;
                                // Update overall totals jika dikirim dari backend
                                if (data.hasOwnProperty('totalOverallTaskPayroll')) this.totals.overallTask = parseFloat(data.totalOverallTaskPayroll) || 0;
                                if (data.hasOwnProperty('totalOverallOtherPayments')) this.totals.overallOther = parseFloat(data.totalOverallOtherPayments) || 0;
                                if (data.hasOwnProperty('totalOverallPaidTaskAmount')) this.totals.overallPaidTask = parseFloat(data.totalOverallPaidTaskAmount) || 0;
                                if (data.hasOwnProperty('totalOverallPaidOtherAmount')) this.totals.overallPaidOther = parseFloat(data.totalOverallPaidOtherAmount) || 0;

                                if (updateHistory && fetchUrl !== this.currentUrl) {
                                    history.pushState({ ...this.filters }, '', fetchUrl);
                                    this.currentUrl = fetchUrl;
                                }
                            })
                            .catch(error => { /* ... error handling ... */ this.tableHtml = `<div class="text-red-600 p-4">Error loading data.</div>`; })
                            .finally(() => { this.loading = false; });
                    },
                    applyFilters() { this.filters.page = 1; this.fetchData(); },
                    sortBy(field) {
                        const validDbSortFields = ['title', 'assigned_user_name', 'difficulty_value', 'priority_value', 'achievement_percentage', 'payment_status', 'updated_at'];
                        if (!validDbSortFields.includes(field)) { console.warn(`Sorting by field "${field}" is not implemented.`); return; }
                        let newDirection = 'asc';
                        if (this.filters.sort === field && this.filters.direction === 'asc') { newDirection = 'desc'; }
                        this.filters.sort = field; this.filters.direction = newDirection;
                        this.filters.page = 1; this.fetchData();
                    },
                    goToPage(url) {
                        try { const targetUrl = new URL(url); const page = targetUrl.searchParams.get('page') || 1; this.filters.page = parseInt(page); this.fetchData(); }
                        catch (e) { console.error("Invalid URL for pagination:", url, e); }
                    },
                    resetFilters() {
                        const defaultPerPage = 10; const defaultSort = 'updated_at'; const defaultDirection = 'desc';
                        this.filters.worker_id = this.isProjectOwner ? 'all' : '{{ auth()->id() }}';
                        this.filters.payment_status = 'all'; this.filters.search = '';
                        this.filters.per_page = defaultPerPage; this.filters.sort = defaultSort;
                        this.filters.direction = defaultDirection; this.filters.page = 1; this.fetchData();
                    },
                    formatCurrency(value) {
                        if (value === null || value === undefined || isNaN(value)) return 'Rp 0';
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                    },
                    printReport() { window.print(); },
                    exportToPdf() {
                        if (this.isExportingPdf) return; this.isExportingPdf = true;
                        const element = document.getElementById('printable-content');
                        if (!element) { console.error("#printable-content not found!"); this.isExportingPdf = false; return; }
                        const workerSelect = document.getElementById('worker_id');
                        let workerNamePart = 'SemuaPekerja';
                        if (this.filters.worker_id !== 'all') {
                            if (this.isProjectOwner) workerNamePart = workerSelect?.options[workerSelect?.selectedIndex]?.text.replace(/[^a-zA-Z0-9]/g, '-') || 'Pekerja';
                            else workerNamePart = '{{ Auth::user()->name }}'.replace(/[^a-zA-Z0-9]/g, '-');
                        }
                        const date = new Date().toISOString().slice(0, 10);
                        const filename = `Laporan-Penggajian-${workerNamePart}-${date}.pdf`;
                        const opt = { margin: [10, 10, 15, 10], filename: filename, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2, useCORS: true, logging: false }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } };
                        html2pdf().set(opt).from(element).save().then(() => { this.isExportingPdf = false; }).catch(err => { console.error("PDF export error:", err); this.isExportingPdf = false; });
                    }
                }
            }
        </script>

        {{-- JavaScript untuk Modal Pembuatan Slip Gaji --}}
        <script>
// PERHATIKAN: Pastikan tidak ada error JS lain di console browser sebelum script ini dieksekusi.
console.log('[MODAL SCRIPT] Script payslipFormModal.js mulai dieksekusi.');

function payslipFormModal(config) {
    // PENAMBAHAN LOG: Untuk memastikan fungsi dipanggil dan config diterima
    console.log('[payslipFormModal] INITIALIZING with config:', JSON.parse(JSON.stringify(config)));

    return {
        // Config & Data
        projectId: config.projectId,
        paymentCalculationType: config.paymentCalculationType,
        workers: config.workers,
        paymentTerms: config.paymentTerms,
        unpaidTasksGrouped: config.unpaidTasksGrouped,
        csrfToken: config.csrfToken,
        storePayslipUrl: config.storePayslipUrl,
        payslipListUrl: config.payslipListUrl,
        defaultTerminName: config.defaultTerminName,

        // State
        showCreatePayslipModal: false,
        selectedWorkerId: '',
        payslipType: '', // Akan di-set di init atau openModal
        selectedTermId: '',
        paymentName: '',
        notes: '',
        calculatedAmount: 0,
        selectedTaskIds: [], // HARUS array agar x-model checkbox berfungsi
        availableTasksForWorker: [],
        isLoadingTasks: false,
        isSubmitting: false,
        formErrors: {},
        generalError: '',

        init() {
            // PENAMBAHAN LOG:
            console.log('[payslipFormModal init()] Component initialized. Default paymentCalculationType:', this.paymentCalculationType);
            this.payslipType = this.paymentCalculationType; // Set default awal saat komponen dimuat
            // Watcher akan diaktifkan saat modal dibuka untuk memastikan elemen DOM ada
        },

        openModal() {
            // PENAMBAHAN LOG:
            console.log('[payslipFormModal openModal()] Modal opening...');
            this.resetForm();
            // Selalu reset ke tipe default proyek saat buka, KECUALI JIKA MEMANG MAU TETAP
            // this.payslipType = this.paymentCalculationType; // Ini sudah benar
            this.handlePayslipTypeChange(this.payslipType, false); // Update nama pembayaran dll, jangan reset amount jika tipe task/termin
            this.showCreatePayslipModal = true;

            this.$nextTick(() => {
                console.log('[payslipFormModal openModal() $nextTick] DOM updated. Initializing watchers and focus.');
                this.initModalWatchers(); // Aktifkan watcher SETELAH modal dan elemennya ada
                const firstFocusableElement = this.$refs.createPayslipModalForm.querySelector('select, input:not([type=hidden])');
                if (firstFocusableElement) firstFocusableElement.focus();
            });
        },

        closeModal() {
            console.log('[payslipFormModal closeModal()] Modal closing.');
            this.showCreatePayslipModal = false;
            // Anda mungkin ingin membersihkan watcher di sini jika diperlukan, tapi biasanya tidak masalah
        },

        resetForm() {
            console.log('[payslipFormModal resetForm()] Resetting form fields.');
            this.selectedWorkerId = '';
            this.payslipType = this.paymentCalculationType; // Reset ke tipe default proyek
            this.selectedTermId = '';
            this.paymentName = '';
            this.notes = '';
            this.calculatedAmount = 0;
            this.selectedTaskIds = [];
            this.availableTasksForWorker = [];
            this.isLoadingTasks = false;
            this.isSubmitting = false;
            this.formErrors = {};
            this.generalError = '';
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
            console.log('[payslipFormModal updateTasksForWorker()] Worker changed to:', this.selectedWorkerId);
            this.selectedTaskIds = []; // Reset pilihan task saat pekerja berubah
            if (this.payslipType === 'task' || this.payslipType === 'termin') {
                this.filterTasksForSelectedTerm(); // Ini akan memanggil calculateTotalAmount
            } else {
                this.availableTasksForWorker = [];
                this.calculateTotalAmount(); // Untuk tipe 'full' atau 'other', amount diinput manual, tapi panggil saja
            }
            // Update nama pembayaran jika tipe 'task'
            if (this.payslipType === 'task') {
                this.handlePayslipTypeChange('task', false); // Panggil ulang untuk update nama
            }
        },

        filterTasksForSelectedTerm() {
            console.log(`[payslipFormModal filterTasksForSelectedTerm()] Filtering tasks. Worker: ${this.selectedWorkerId}, Term: ${this.selectedTermId}, PayslipType: ${this.payslipType}`);
            this.selectedTaskIds = []; // Reset pilihan task
            this.isLoadingTasks = true;
            this.availableTasksForWorker = [];

            setTimeout(() => { // setTimeout kecil untuk UX
                if (!this.selectedWorkerId) {
                    console.log('[filterTasksForSelectedTerm] No worker selected.');
                    this.isLoadingTasks = false;
                    this.calculateTotalAmount(); // Hasilnya akan 0
                    return;
                }

                const workerTasks = this.unpaidTasksGrouped[this.selectedWorkerId] || [];
                console.log(`[filterTasksForSelectedTerm] Found ${workerTasks.length} raw tasks for worker ${this.selectedWorkerId}.`);

                if (this.payslipType === 'termin') {
                    if (!this.selectedTermId) {
                        console.log('[filterTasksForSelectedTerm] Payslip type is TERMIN, but no term selected.');
                        this.availableTasksForWorker = [];
                    } else {
                        const selectedTerm = this.paymentTerms.find(term => term.id == this.selectedTermId);
                        if (!selectedTerm || !selectedTerm.start_date_formatted || !selectedTerm.end_date_formatted) {
                            console.error('[filterTasksForSelectedTerm] Invalid term or missing dates:', selectedTerm);
                            this.availableTasksForWorker = [];
                        } else {
                            try {
                                const startDate = new Date(selectedTerm.start_date_formatted + 'T00:00:00Z');
                                const endDate = new Date(selectedTerm.end_date_formatted + 'T23:59:59Z');
                                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                                    throw new Error("Invalid term dates after parsing.");
                                }
                                console.log(`[filterTasksForSelectedTerm] Filtering for term: ${selectedTerm.name}, Start: ${startDate.toISOString()}, End: ${endDate.toISOString()}`);

                                this.availableTasksForWorker = workerTasks.filter(task => {
                                    if (!task.finished_date) return false;
                                    try {
                                        const finishedDate = new Date(task.finished_date + 'T12:00:00Z'); // UTC Midday
                                        if(isNaN(finishedDate.getTime())) return false;
                                        return finishedDate >= startDate && finishedDate <= endDate;
                                    } catch (dateError) {
                                        console.warn("[filterTasksForSelectedTerm] Error parsing task finished_date:", task.finished_date, dateError);
                                        return false;
                                    }
                                });
                            } catch (e) {
                                console.error("[filterTasksForSelectedTerm] Error during date filtering for term:", e);
                                this.availableTasksForWorker = [];
                            }
                        }
                    }
                } else if (this.payslipType === 'task') {
                    console.log('[filterTasksForSelectedTerm] Payslip type is TASK. Using all unpaid tasks for worker.');
                    this.availableTasksForWorker = workerTasks;
                } else { // 'full' or 'other'
                    console.log('[filterTasksForSelectedTerm] Payslip type is FULL or OTHER. No tasks to list.');
                    this.availableTasksForWorker = [];
                }
                console.log(`[filterTasksForSelectedTerm] ${this.availableTasksForWorker.length} tasks available after filtering.`);
                this.isLoadingTasks = false;
                this.calculateTotalAmount(); // Hitung ulang jumlah, selectedTaskIds masih kosong di sini, jadi amount = 0
            }, 50);
        },

        // logSelectedTasks() { // Fungsi ini ada di kode Anda, tapi tidak dipanggil. Mungkin untuk debug manual.
        //     console.log('[payslipFormModal logSelectedTasks()] Current selectedTaskIds:', JSON.parse(JSON.stringify(this.selectedTaskIds)));
        // },

        calculateTotalAmount() {
            // PENAMBAHAN LOG: Ini adalah log kunci
            console.log(`[payslipFormModal calculateTotalAmount()] CALLED. PayslipType: ${this.payslipType}. SelectedTaskIds:`, JSON.parse(JSON.stringify(this.selectedTaskIds)));
            console.log(`[calculateTotalAmount] Available tasks for calculation: ${this.availableTasksForWorker.length} tasks.`);
            // PENAMBAHAN LOG: Tampilkan availableTasksForWorker jika ada task dipilih tapi availableTasksForWorker kosong
            if (this.selectedTaskIds.length > 0 && this.availableTasksForWorker.length === 0) {
                console.warn(`[calculateTotalAmount] WARNING: ${this.selectedTaskIds.length} tasks selected, but no tasks are available in 'availableTasksForWorker'. Amount will be 0.`);
            }

            if (this.payslipType === 'task' || this.payslipType === 'termin') {
                let total = 0;
                // Pastikan selectedTaskIds adalah array string untuk perbandingan yang konsisten
                const selectedIdsSet = new Set(this.selectedTaskIds.map(id => String(id)));

                this.availableTasksForWorker.forEach(task => {
                    // Pastikan task.id dikonversi ke string untuk dicocokkan dengan Set
                    if (selectedIdsSet.has(String(task.id))) {
                        const value = parseFloat(task.calculated_value || 0);
                        // PENAMBAHAN LOG: Untuk setiap task yang dijumlahkan
                        console.log(`[calculateTotalAmount] Task ID ${task.id} (value: ${task.calculated_value}, parsed: ${value}) IS SELECTED and being added to total.`);
                        if (!isNaN(value)) {
                            total += value;
                        } else {
                            console.warn(`[calculateTotalAmount] Task ID ${task.id} has a non-numeric calculated_value: ${task.calculated_value}`);
                        }
                    }
                });
                console.log('[calculateTotalAmount] Total calculated for task/termin:', total);
                this.calculatedAmount = total;
            } else {
                // Untuk tipe 'full' atau 'other', calculatedAmount di-bind ke input manual.
                // Jika perlu, Anda bisa me-resetnya di sini atau membiarkannya.
                // Saat ini, nilai tidak diubah di sini untuk tipe 'full'/'other', yang sepertinya benar.
                console.log(`[calculateTotalAmount] PayslipType is ${this.payslipType}. Amount is manually entered or pre-set. Current calculatedAmount: ${this.calculatedAmount}`);
            }
        },

        handlePayslipTypeChange(newType, resetAmountAndFocus = true) {
            console.log(`[payslipFormModal handlePayslipTypeChange()] Type changed to: ${newType}. ResetAmountAndFocus: ${resetAmountAndFocus}`);
            // Set default payment name
            if (newType === 'termin') {
                this.paymentName = this.defaultTerminName;
            } else if (newType === 'task') {
                const worker = this.workers.find(w => w.id == this.selectedWorkerId);
                const workerName = worker ? worker.name : 'Pekerja';
                this.paymentName = `Pembayaran Task ${workerName} (${new Date().toLocaleDateString('id-ID')})`;
            } else if (newType === 'full') {
                this.paymentName = `Pembayaran Penuh (${new Date().toLocaleDateString('id-ID')})`;
            } else if (newType === 'other') {
                this.paymentName = ''; // Kosongkan untuk diisi manual
            }

            // Reset related fields
            if (newType !== 'termin') {
                this.selectedTermId = ''; // Reset pilihan termin jika bukan tipe termin
            }

            if (newType !== 'task' && newType !== 'termin') {
                this.selectedTaskIds = []; // Jika bukan task/termin, kosongkan task yg dipilih
                this.availableTasksForWorker = []; // dan list task yg tersedia
            }

            // Reset amount atau recalculate
            if (resetAmountAndFocus && (newType === 'full' || newType === 'other')) {
                console.log('[handlePayslipTypeChange] Type is FULL or OTHER. Resetting amount to 0 for manual input.');
                this.calculatedAmount = 0; // Reset untuk input manual
                this.$nextTick(() => {
                    const amountInput = document.getElementById("modal_amount");
                    if (amountInput) amountInput.focus();
                });
            } else if (newType === 'task' || newType === 'termin') {
                console.log('[handlePayslipTypeChange] Type is TASK or TERMIN. Filtering tasks (which will call calculateTotalAmount).');
                // Trigger filter/calculation jika pekerja sudah dipilih
                if (this.selectedWorkerId) {
                    this.filterTasksForSelectedTerm(); // Ini akan memanggil calculateTotalAmount
                } else {
                    this.calculatedAmount = 0; // Jika belum ada pekerja, amount 0
                    this.availableTasksForWorker = [];
                }
            }
            // else: Untuk tipe full/other, jika resetAmountAndFocus false (misal saat load old input), biarkan amount apa adanya.
        },

        formatTermDate(dateString) {
            if (!dateString) return 'Invalid Date';
            try {
                const date = new Date(dateString + 'T00:00:00Z');
                if (isNaN(date.getTime())) return 'Invalid Date';
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', timeZone: 'UTC' });
            } catch (e) { return 'Invalid Date'; }
        },
        formatDisplayDate(dateString) {
             if (!dateString) return 'N/A';
             try {
                 const date = new Date(dateString + 'T00:00:00Z');
                 if (isNaN(date.getTime())) return 'N/A';
                 return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' });
             } catch (e) { return 'N/A'; }
         },
        formatCurrency(value) {
            if (value === null || value === undefined || isNaN(value)) return "Rp 0";
            return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
        },

        submitForm() {
            console.log('[payslipFormModal submitForm()] Submitting form...');
            this.isSubmitting = true;
            this.formErrors = {};
            this.generalError = '';

            const formElement = this.$refs.createPayslipModalForm;
            const formData = new FormData(formElement); // Ambil semua data form asli
            const dataToSend = {};

            // Konversi FormData ke objek, kecuali task_ids (akan diambil dari Alpine state)
            for (let [key, value] of formData.entries()) {
                if (key !== 'task_ids[]') { // Jangan ambil task_ids[] dari FormData
                    dataToSend[key] = value;
                }
            }

            // Ambil task_ids dari Alpine state (selectedTaskIds)
            // Backend Laravel biasanya mengharapkan task_ids sebagai array.
            dataToSend.task_ids = this.selectedTaskIds.map(String); // Pastikan array of strings

            // Jika tipe task atau termin, pastikan amount adalah yang dihitung
            if (this.payslipType === 'task' || this.payslipType === 'termin') {
                dataToSend.amount = this.calculatedAmount;
            } else { // Untuk 'full' atau 'other', amount diambil dari input (x-model="calculatedAmount")
                 dataToSend.amount = this.calculatedAmount; // Ini sudah di-bind ke input `modal_amount`
            }
            // Hapus _token karena kita akan kirim via X-CSRF-TOKEN header
            delete dataToSend._token;

            console.log('[submitForm] Data to send to backend:', JSON.parse(JSON.stringify(dataToSend)));

            fetch(this.storePayslipUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(dataToSend)
            })
            .then(response => response.json().then(data => ({ status: response.status, ok: response.ok, body: data })))
            .then(({ status, ok, body }) => {
                if (ok && body.success) {
                    console.log('[submitForm] Success:', body.message);
                    this.closeModal();
                    if (typeof Turbo !== 'undefined' && body.redirect_url) {
                        Turbo.visit(`${body.redirect_url}?success_message=${encodeURIComponent(body.message)}`);
                    } else if (body.redirect_url) {
                        window.location.href = `${body.redirect_url}?success_message=${encodeURIComponent(body.message)}`;
                    } else {
                        // Fallback jika tidak ada redirect_url, mungkin refresh atau emit event
                        // window.location.reload();
                        // Atau emit event untuk memberitahu komponen lain
                         window.dispatchEvent(new CustomEvent('payslip-created-successfully', { detail: { message: body.message } }));
                    }
                } else {
                    console.error("[submitForm] Server Response Error. Status:", status, "Body:", body);
                    if (status === 422 && body.errors) {
                        this.formErrors = body.errors;
                        this.generalError = body.message || 'Terjadi kesalahan validasi. Periksa inputan Anda.';
                    } else {
                         this.generalError = body.message || 'Terjadi kesalahan saat menyimpan.';
                    }
                }
            })
            .catch(error => {
                console.error('[submitForm] Fetch error:', error);
                this.generalError = 'Kesalahan jaringan atau server. Silakan coba lagi.';
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        initModalWatchers() {
            // PENAMBAHAN LOG:
            console.log('[payslipFormModal initModalWatchers()] Initializing watchers...');

            this.$watch('selectedWorkerId', (newWorkerId, oldWorkerId) => {
                console.log(`[WATCHER selectedWorkerId] Changed from ${oldWorkerId} to ${newWorkerId}`);
                this.updateTasksForWorker();
            });

            this.$watch('payslipType', (newType, oldType) => {
                console.log(`[WATCHER payslipType] Changed from ${oldType} to ${newType}`);
                // Hanya trigger full change handler jika benar-benar berubah oleh interaksi pengguna
                const userInitiatedChange = oldType !== undefined && newType !== oldType;
                this.handlePayslipTypeChange(newType, userInitiatedChange);
            });

            this.$watch('selectedTermId', (newTermId, oldTermId) => {
                console.log(`[WATCHER selectedTermId] Changed from ${oldTermId} to ${newTermId}`);
                this.filterTasksForSelectedTerm();
            });

            // PERHATIKAN: Watcher untuk selectedTaskIds
            this.$watch('selectedTaskIds', (newValue, oldValue) => {
                // PENAMBAHAN LOG: Ini adalah watcher kunci untuk masalah Anda
                console.log('[WATCHER selectedTaskIds] Task selection changed.');
                console.log('[WATCHER selectedTaskIds] Old value:', JSON.parse(JSON.stringify(oldValue)));
                console.log('[WATCHER selectedTaskIds] New value:', JSON.parse(JSON.stringify(newValue)));
                this.calculateTotalAmount(); // Panggil kalkulasi saat task dipilih/dihilangkan
            });
            console.log('[initModalWatchers] Watchers initialized.');
        }
    }
}
// PERHATIKAN: Pastikan tidak ada error JS lain di console browser SETELAH script ini dieksekusi.
console.log('[MODAL SCRIPT] Script payslipFormModal.js selesai dieksekusi.');
</script>
    @endpush
</x-app-layout>