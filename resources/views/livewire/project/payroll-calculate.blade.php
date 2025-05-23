<div> {{-- Root Element --}}
    {{-- CSS Khusus Print --}}
    @push('styles')
    <style>
        @media print {
            body * { visibility: hidden; -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
            #printable-content, #printable-content * { visibility: visible; }
            #printable-content { position: absolute; left: 0; top: 0; width: 100%; margin: 0 !important; padding: 15px !important; border: none !important; box-shadow: none !important; background-color: white !important; }
            .no-print, .no-print * { display: none !important; }
            #printable-content .pagination { display: none !important; }
            #printable-content h3, #printable-content h4 { margin-bottom: 0.5rem; font-size: 1.1rem; color: black !important; }
            #printable-content table { width: 100% !important; border-collapse: collapse !important; font-size: 8pt !important; /* Lebih kecil lagi untuk landscape */ color: black !important; }
            #printable-content thead { display: table-header-group; }
            #printable-content th { background-color: #f2f2f2 !important; color: black !important; -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
            #printable-content th, #printable-content td { border: 1px solid #ddd !important; padding: 3px 5px !important; /* Lebih kecil */ text-align: left !important; color: black !important; background-color: white !important; word-wrap: break-word; }
            #printable-content td span[class*="bg-"] { background-color: transparent !important; padding: 0 !important; border-radius: 0 !important; border: 1px solid #ccc !important; color: black !important; }
            #printable-content .summary-grid { grid-template-columns: repeat(2, 1fr) !important; font-size: 9pt !important; margin-top: 1rem !important; page-break-inside: avoid; }
            #printable-content .summary-grid > div { padding: 8px !important; border: 1px solid #eee !important; background-color: white !important; page-break-inside: avoid; }
            #printable-content .summary-grid > div[class*="bg-"] { background-color: #f0f0f0 !important; border-color: #ccc !important; -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
            #printable-content .summary-grid > div[class*="bg-"] dt, #printable-content .summary-grid > div[class*="bg-"] dd, #printable-content .summary-grid > div[class*="bg-"] span, #printable-content .summary-grid > div[class*="bg-"] p { color: black !important; }
            #printable-content .summary-grid dt { font-weight: bold !important; color: #333 !important; margin-bottom: 2px !important; }
            #printable-content .summary-grid dd { font-size: 10pt !important; margin-top: 0 !important; color: black !important; }
            #printable-content .summary-grid p.text-xs { font-size: 8pt !important; color: #555 !important; margin-top: 4px !important; }
            #printable-content a { color: #0066cc !important; text-decoration: none !important; }
            #printable-content svg:not(.print-icon) { display: none !important; } /* Sembunyikan ikon kecuali yg perlu */
        }
         /* CSS Utility tambahan jika belum ada global */
        .input-field { @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm; }
        .label-text { @apply block text-sm font-medium text-gray-700; }
        .btn-primary { @apply inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50; }
        .btn-secondary { @apply inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
        .th-cell { @apply px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider; }
        .td-cell { @apply px-4 py-4 whitespace-nowrap text-sm; }
        .td-cell-wrap { @apply px-4 py-4 text-sm; } /* Class baru untuk text wrap */
        .badge-green { @apply px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800; }
        .badge-yellow { @apply px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800; }
        .icon-link { @apply ml-1 text-xs text-blue-600 hover:text-blue-800 hover:underline; }
        .icon-sm { @apply h-3 w-3 inline-block; }
    </style>
    @endpush

    {{-- Kontainer Utama Komponen --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">

        {{-- Header Halaman & Tombol Aksi --}}
        <div class="mb-6 flex justify-between items-center no-print">
            <h2 class="text-2xl font-semibold text-gray-900">Perhitungan Penggajian - {{ $project->name }}</h2>
            <div class="flex space-x-2">
                <button @click="printReport()" title="Cetak Laporan" class="btn-secondary">
                    <svg class="print-icon h-4 w-4 mr-2">...</svg> Print
                </button>
                <button @click="exportToPdf()" :disabled="isExportingPdf" title="Ekspor ke PDF" class="btn-primary">
                     <svg x-show="!isExportingPdf" class="print-icon h-4 w-4 mr-2">...</svg>
                     <svg x-show="isExportingPdf" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white">...</svg>
                    <span x-text="isExportingPdf ? 'Mengekspor...' : 'Export PDF'"></span>
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-6 no-print">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('projects.payroll.calculate', $project) }}" wire:navigate
                   class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                    Perhitungan Gaji
                </a>
                <a href="{{ route('projects.payslips.create', $project) }}" wire:navigate
                   class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                     Pembuatan Slip Gaji
                </a>
                 <a href="{{ route('projects.payslips.history', $project) }}" wire:navigate
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                     Riwayat Slip Gaji
                 </a>
            </nav>
        </div>

        <!-- Weight Info -->
        <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-md text-sm text-indigo-700 flex justify-between items-center no-print">
            <span> Bobot Aktif: Kesulitan = <strong>{{ $project->difficulty_weight }}%</strong>, Prioritas = <strong>{{ $project->priority_weight }}%</strong>. </span>
            <div>
                 {{-- Link ke halaman pengaturan (jika sudah Livewire, gunakan wire:navigate) --}}
                 <a href="{{ route('projects.pengaturan', $project) }}" wire:navigate class="ml-2 font-medium hover:underline">(Ubah Pengaturan)</a>
            </div>
        </div>

        <!-- Filters, Search, and Controls (Gunakan wire:model, wire:click) -->
        <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200 no-print">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Worker Filter --}}
                <div>
                    <label for="worker_filter" class="label-text">Pekerja</label>
                    <select wire:model="filterWorkerId" id="worker_filter" class="input-field w-full">
                        <option value="all">Semua Pekerja</option>
                        @foreach ($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Payment Status Filter --}}
                <div>
                    <label for="payment_status_filter" class="label-text">Status Pembayaran Task</label>
                    <select wire:model="filterPaymentStatus" id="payment_status_filter" class="input-field w-full">
                        <option value="all">Semua Status</option>
                        <option value="unpaid">Belum Dibayar</option>
                        <option value="paid">Sudah Dibayar</option>
                    </select>
                </div>
                {{-- Search Input --}}
                <div>
                    <label for="search_filter" class="label-text">Cari Task / Pekerja / Bonus</label>
                    <input wire:model.debounce.500ms="filterSearch" type="text" id="search_filter" placeholder="Masukkan kata kunci..." class="input-field w-full">
                </div>
                {{-- Per Page Selector --}}
                <div>
                     <label for="per_page_filter" class="label-text">Item per Halaman (Task)</label>
                     <select wire:model="perPage" id="per_page_filter" class="input-field w-full">
                         @foreach ($perPageOptions as $option)
                             <option value="{{ $option }}">{{ $option }}</option>
                         @endforeach
                     </select>
                 </div>
            </div>
            <div class="mt-4 flex justify-end space-x-2 items-center">
                  {{-- Loading indicator untuk filter --}}
                 <div wire:loading wire:target="filterWorkerId, filterPaymentStatus, filterSearch, perPage, resetFilters"
                      class="text-sm text-gray-500 italic">
                     Applying filters...
                 </div>
                 <button wire:click="resetFilters" type="button" class="btn-secondary">
                     Reset Filter
                 </button>
            </div>
        </div>

        {{-- Wrapper untuk Area Cetak/Export --}}
        <div id="printable-content">

            {{-- Judul Report (Hanya untuk cetak/pdf) --}}
             <div class="hidden print:block mb-4">
                 <h2 class="text-xl font-bold text-center">Laporan Perhitungan Penggajian</h2>
                 <p class="text-sm text-center">Proyek: {{ $project->name }}</p>
                 <p class="text-sm text-center">Tanggal Cetak/Export: {{ now()->format('d M Y H:i') }}</p>
                 <div class="text-xs mt-2 text-gray-600 border-t border-b border-gray-300 py-1 my-2">
                     <p class="font-medium">Filter Aktif:</p>
                     <ul class="list-none pl-0">
                         <li>Pekerja: {{ $filterWorkerId === 'all' ? 'Semua' : ($workers->firstWhere('id', $filterWorkerId)->name ?? 'N/A') }}</li>
                         <li>Status Task: {{ $filterPaymentStatus === 'all' ? 'Semua' : ($filterPaymentStatus === 'paid' ? 'Dibayar' : 'Belum Dibayar') }}</li>
                         <li>Pencarian: {{ $filterSearch ?: '-' }}</li>
                         <li>Urutan: {{ $sortField }} ({{ $sortDirection }})</li>
                     </ul>
                 </div>
             </div>

            <!-- Task Table Area -->
            <h3 class="text-lg font-medium text-gray-800 mb-3 print:text-base">Rekap Task</h3>
            <div id="payroll-table-container">
                {{-- Loading Indicator Tabel --}}
                <div wire:loading wire:target="render" class="text-center py-10 no-print">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-600 inline-block">...</svg>
                    <span class="text-gray-600">Memuat data task...</span>
                </div>
                 {{-- Tabel Task --}}
                <div wire:loading.remove wire:target="render">
                     <div class="bg-white shadow overflow-hidden sm:rounded-md">
                         <div class="overflow-x-auto">
                             <table class="min-w-full divide-y divide-gray-200">
                                 <thead class="bg-gray-50">
                                     <tr>
                                         {{-- Header dengan wire:click untuk sorting --}}
                                         <th scope="col" class="th-cell"><button wire:click="sortBy('title')" class="font-medium hover:text-indigo-700">Tugas @if($sortField === 'title')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button></th>
                                         <th scope="col" class="th-cell"><button wire:click="sortBy('assigned_user_name')" class="font-medium hover:text-indigo-700">Pekerja @if($sortField === 'assigned_user_name')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button></th>
                                         <th scope="col" class="th-cell"><button wire:click="sortBy('difficulty_value')" class="font-medium hover:text-indigo-700">Kesulitan @if($sortField === 'difficulty_value')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button></th>
                                         <th scope="col" class="th-cell"><button wire:click="sortBy('priority_value')" class="font-medium hover:text-indigo-700">Prioritas @if($sortField === 'priority_value')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button></th>
                                         <th scope="col" class="th-cell text-center"><button wire:click="sortBy('achievement_percentage')" class="font-medium hover:text-indigo-700">Achiev (%) @if($sortField === 'achievement_percentage')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button></th>
                                         <th scope="col" class="th-cell text-center" title="Weighted Sum Model Score">Skor WSM</th>
                                         <th scope="col" class="th-cell text-right" title="Nilai dasar dari standar upah pekerja">Nilai Dasar (Rp)</th>
                                         <th scope="col" class="th-cell text-right" title="Nilai Akhir = Skor WSM * Nilai Dasar * Persentase Achievement">Nilai Akhir (Rp)</th>
                                         <th scope="col" class="th-cell text-center"><button wire:click="sortBy('payment_status')" class="font-medium hover:text-indigo-700">Status Bayar @if($sortField === 'payment_status')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button></th>
                                         <th scope="col" class="th-cell"><button wire:click="sortBy('updated_at')" class="font-medium hover:text-indigo-700">Tgl Selesai @if($sortField === 'updated_at')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button></th>
                                     </tr>
                                 </thead>
                                 <tbody class="bg-white divide-y divide-gray-200">
                                     @forelse($tasks as $task)
                                         <tr class="hover:bg-gray-50">
                                             <td class="td-cell font-medium text-gray-900">{{ $task->title }}</td>
                                             <td class="td-cell text-gray-500">{{ $task->assigned_user_name ?? $task->assignedUser?->name ?? 'N/A' }}</td>
                                             <td class="td-cell text-gray-500">{{ $task->difficultyLevel?->name ?? 'N/A' }} ({{ $task->difficulty_value ?? '-' }})</td>
                                             <td class="td-cell text-gray-500">{{ $task->priorityLevel?->name ?? 'N/A' }} ({{ $task->priority_value ?? '-' }})</td>
                                             <td class="td-cell text-center text-gray-500">{{ $task->achievement_percentage ?? 100 }}%</td>
                                             <td class="td-cell text-center text-gray-600 font-semibold">{{ number_format($task->wsm_score, 2, ',', '.') }}</td>
                                             <td class="td-cell text-right text-gray-500">Rp {{ number_format($task->base_value, 0, ',', '.') }}</td>
                                             <td class="td-cell text-right text-gray-800 font-bold">Rp {{ number_format($task->calculated_value, 0, ',', '.') }}</td>
                                             <td class="td-cell text-center">
                                                  @if($task->payment_id)
                                                      <span class="badge-green"> Dibayar </span>
                                                      @if($task->payment) {{-- Cek relasi sudah di-load --}}
                                                           <a href="{{ route('projects.payslips.show', [$project, $task->payment]) }}" wire:navigate class="icon-link" title="Lihat Slip: {{ $task->payment->payment_name }}">
                                                               <svg class="icon-sm">...</svg> {{-- Icon mata --}}
                                                           </a>
                                                      @else
                                                            <span class="text-xs text-gray-400 ml-1">(#{{ $task->payment_id }})</span>
                                                      @endif
                                                  @else
                                                      <span class="badge-yellow"> Belum Dibayar </span>
                                                  @endif
                                             </td>
                                             <td class="td-cell text-gray-500"> {{ $task->updated_at ? $task->updated_at->format('d/m/Y H:i') : '-'}} </td>
                                         </tr>
                                     @empty
                                         <tr>
                                             <td colspan="10" class="px-6 py-10 text-center text-sm text-gray-500 italic"> Tidak ada data task selesai yang sesuai dengan filter. </td>
                                         </tr>
                                     @endforelse
                                 </tbody>
                             </table>
                         </div>
                         {{-- Pagination Livewire --}}
                         @if ($tasks->hasPages())
                             <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                                 {{ $tasks->links() }}
                             </div>
                         @endif
                     </div>
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
                         <div class="border border-gray-200 rounded-md p-3"> <dt class="text-gray-500 truncate">Total Nilai Task</dt> <dd class="mt-1 text-xl print:text-base font-semibold text-blue-600">{{ number_format($totalFilteredTaskPayroll, 0, ',', '.') }}</dd> </div>
                         {{-- Filtered Other Value --}}
                         <div class="border border-gray-200 rounded-md p-3"> <dt class="text-gray-500 truncate">Total Bonus/Lainnya</dt> <dd class="mt-1 text-xl print:text-base font-semibold text-purple-600">{{ number_format($totalFilteredOtherPayments, 0, ',', '.') }}</dd> </div>
                         {{-- Filtered Total Hak Gaji --}}
                         <div class="border border-indigo-200 bg-indigo-50 rounded-md p-3"> <dt class="text-indigo-800 truncate font-medium">Total Hak Gaji</dt> <dd class="mt-1 text-xl print:text-base font-bold text-indigo-700">{{ number_format($totalFilteredTaskPayroll + $totalFilteredOtherPayments, 0, ',', '.') }}</dd> </div>
                         {{-- Filtered Total Paid --}}
                         <div class="border border-green-200 bg-green-50 rounded-md p-3">
                             <dt class="text-green-800 truncate font-medium">Total Sudah Dibayar</dt>
                             <dd class="mt-1 text-xl print:text-base font-bold text-green-700">{{ number_format($totalFilteredPaidTaskAmount + $totalFilteredPaidOtherAmount, 0, ',', '.') }}</dd>
                             <p class="text-xs text-green-600 mt-1"> Task/Term: {{ number_format($totalFilteredPaidTaskAmount, 0, ',', '.') }}<br> Bonus/Lain: {{ number_format($totalFilteredPaidOtherAmount, 0, ',', '.') }} </p>
                         </div>
                     </div>
                     <p class="text-xs text-gray-500 italic mt-2 no-print"> @if($filterWorkerId && $filterWorkerId !== 'all') Menampilkan ringkasan filter untuk pekerja: <strong>{{ $workers->firstWhere('id', $filterWorkerId)->name ?? 'N/A' }}</strong>. @else Menampilkan ringkasan filter untuk semua pekerja. @endif </p>
                 </div>

                 {{-- Section 2: Overall Amounts & Budget (Sembunyikan saat print sederhana) --}}
                 <div class="no-print">
                     <h4 class="text-md font-semibold text-gray-700 mb-3">Ringkasan Keseluruhan Proyek</h4>
                     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm summary-grid">
                         {{-- Overall Task Value --}}
                         <div class="border border-gray-200 rounded-md p-3"> <dt class="text-gray-500 truncate">Total Nilai Task</dt> <dd class="mt-1 text-lg font-semibold text-gray-700">{{ number_format($totalOverallTaskPayroll, 0, ',', '.') }}</dd> </div>
                         {{-- Overall Other Value --}}
                         <div class="border border-gray-200 rounded-md p-3"> <dt class="text-gray-500 truncate">Total Bonus/Lainnya</dt> <dd class="mt-1 text-lg font-semibold text-gray-700">{{ number_format($totalOverallOtherPayments, 0, ',', '.') }}</dd> </div>
                         {{-- Overall Total Hak Gaji --}}
                         <div class="border border-gray-300 bg-gray-50 rounded-md p-3"> <dt class="text-gray-600 truncate font-medium">Total Hak Gaji</dt> <dd class="mt-1 text-lg font-semibold text-gray-800">{{ number_format($totalOverallPayroll, 0, ',', '.') }}</dd> </div>
                         {{-- Overall Total Paid --}}
                         <div class="border border-green-300 bg-green-50 rounded-md p-3">
                             <dt class="text-green-800 truncate font-medium">Total Sudah Dibayar</dt>
                             <dd class="mt-1 text-lg font-semibold text-green-700">{{ number_format($totalOverallPaidTaskAmount + $totalOverallPaidOtherAmount, 0, ',', '.') }}</dd>
                             <p class="text-xs text-green-600 mt-1"> Task/Term: {{ number_format($totalOverallPaidTaskAmount, 0, ',', '.') }}<br> Bonus/Lain: {{ number_format($totalOverallPaidOtherAmount, 0, ',', '.') }} </p>
                         </div>
                         {{-- Budget --}}
                         <div class="border border-gray-200 rounded-md p-3"> <dt class="text-gray-500 truncate">Estimasi Budget</dt> <dd class="mt-1 text-lg font-semibold text-gray-900">Rp {{ number_format($project->budget, 0, ',', '.') }}</dd> </div>
                         {{-- Budget Difference --}}
                         <div class="border rounded-md p-3 {{ $budgetDifference >= 0 ? 'border-yellow-300 bg-yellow-50' : 'border-red-300 bg-red-50' }}">
                             <dt class="text-gray-500 truncate">Sisa / Lebih Budget</dt>
                             <dd class="mt-1 text-lg font-semibold {{ $budgetDifference >= 0 ? 'text-yellow-700' : 'text-red-700' }}"> Rp {{ number_format(abs($budgetDifference), 0, ',', '.') }} ({{ $budgetDifference >= 0 ? 'Sisa' : 'Melebihi' }}) </dd>
                             <p class="text-xs {{ $budgetDifference >= 0 ? 'text-yellow-600' : 'text-red-600' }} mt-1">Dibandingkan total hak gaji.</p>
                         </div>
                     </div>
                 </div>
            </div>
        </div> {{-- Akhir Wrapper Area Cetak/Export --}}

        {{-- Link ke Pembuatan Slip Gaji --}}
        <div class="mt-6 text-center text-sm text-gray-600 no-print">
             Pergi ke halaman <a href="{{ route('projects.payslips.create', $project) }}" wire:navigate class="text-indigo-600 hover:underline font-medium">Pembuatan Slip Gaji</a>.
         </div>

    </div> {{-- End Padding Utama --}}

    {{-- Alpine.js untuk Print & Export --}}
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('payrollCalculatorPrintExport', () => ({
                isExportingPdf: false,
                printReport() { window.print(); },
                exportToPdf() {
                    if (this.isExportingPdf) return;
                    this.isExportingPdf = true;
                    const element = document.getElementById('printable-content');
                    if (!element) { this.isExportingPdf = false; return; }
                    const workerSelect = document.getElementById('worker_filter'); // Sesuaikan ID
                    const workerName = '{{ $filterWorkerId === 'all' ? 'SemuaPekerja' : ($workers->firstWhere('id', $filterWorkerId)->name ?? 'Pekerja') }}'.replace(/[^a-zA-Z0-9]/g, '-');
                    const date = new Date().toISOString().slice(0, 10);
                    const filename = `Laporan-Penggajian-${workerName}-${date}.pdf`;
                    const opt = { margin: [10, 5, 10, 5], filename: filename, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2, useCORS: true, logging: false }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } }; // Landscape

                    html2pdf().set(opt).from(element).save().then(() => { this.isExportingPdf = false; }).catch(err => { console.error("PDF Export Error:", err); alert("Gagal export PDF."); this.isExportingPdf = false; });
                }
            }));
        });
    </script>
    @endpush

</div>