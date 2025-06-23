<x-app-layout>
    {{-- CSS Khusus untuk Print --}}
    <style>
        @media print {
            body * { visibility: hidden; }
            #printable-area, #printable-area * { visibility: visible; }
            #printable-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0 !important; padding: 15px !important; font-size: 9pt; }
            .no-print { display: none !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ddd !important; padding: 4px 6px !important; text-align: left !important; word-wrap: break-word; }
            thead { display: table-header-group; }
            th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; color-adjust: exact; }
            td span[class*="bg-"] { background-color: transparent !important; color: black !important; border: 1px solid #ccc !important; padding: 1px 3px !important; }
            a { text-decoration: none !important; color: black !important; }
            svg { display: none !important; }
        }
    </style>
    
    {{-- PERBAIKAN: Sanitasi data filter di sini --}}
    @php
        $initialFiltersConfig = [
            'search' => $request->query('search', ''),
            'status' => $request->query('status', 'all'),
            'user_id' => $request->query('user_id', 'all'),
            'date_from' => $request->query('date_from', ''),
            'date_to' => $request->query('date_to', ''),
            'per_page' => (int) $request->query('per_page', 15),
            'sort' => $request->query('sort', 'created_at'),
            'direction' => $request->query('direction', 'desc'),
            'page' => (int) $request->query('page', 1),
        ];
    @endphp

    <div x-data="recapPage({
            baseUrl: '{{ route('projects.tasks.recap', $project) }}',
            initialFilters: {{ Js::from($initialFiltersConfig) }}
         })"
         x-init="init()"
         class="py-6 px-4 sm:px-6 lg:px-8">
        
        {{-- Header & Tombol Aksi --}}
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Assignment Recap</h2>
                <p class="text-sm text-gray-600">Proyek: {{ $project->name }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('projects.kanban', $project) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    Kembali ke Kanban
                </a>
                <button @click="window.print()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Print
                </button>
                <button @click="exportToPdf()" :disabled="isExportingPdf" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-700 hover:bg-gray-800 disabled:opacity-50">
                    <svg x-show="!isExportingPdf" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <svg x-show="isExportingPdf" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isExportingPdf ? 'Mengekspor...' : 'Export PDF'"></span>
                </button>
            </div>
        </div>

        {{-- Filter Form --}}
        <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200 no-print">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700">Cari</label>
                    <input type="text" id="search" x-model="filters.search" placeholder="Nama task atau pekerja..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" x-model="filters.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        <option value="all">Semua Status</option>
                        <option value="To Do">To Do</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Review">Review</option>
                        <option value="Done">Done</option>
                    </select>
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                    <select id="user_id" x-model="filters.user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        <option value="all">Semua Pekerja</option>
                        @foreach($projectUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700">Item/Halaman</label>
                    <select id="per_page" x-model.number="filters.per_page" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4 lg:col-span-2">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Dari Tgl. (Mulai)</label>
                        <input type="date" id="date_from" x-model="filters.date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Sampai Tgl. (Selesai)</label>
                        <input type="date" id="date_to" x-model="filters.date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>
                </div>
            </div>
        </div>

        {{-- Wadah untuk Tabel dan Paginasi --}}
        <div id="printable-area" class="relative">
            {{-- Header untuk print --}}
            <div class="hidden print:block mb-4">
                <h2 class="text-xl font-bold text-center">Rekapitulasi Tugas Proyek</h2>
                <p class="text-sm text-center">Proyek: {{ $project->name }}</p>
                <p class="text-sm text-center">Dicetak pada: <span x-text="new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></span></p>
                <div class="text-xs mt-2 text-gray-600 border-t border-b border-gray-300 py-1 my-2">
                    <p class="font-medium">Filter Aktif:</p>
                    <ul class="list-none pl-0">
                        <li>Pekerja: <span x-text="filters.user_id === 'all' ? 'Semua Pekerja' : (document.getElementById('user_id')?.options[document.getElementById('user_id')?.selectedIndex]?.text || filters.user_id)"></span></li>
                        <li>Status: <span x-text="filters.status === 'all' ? 'Semua Status' : filters.status"></span></li>
                        <li>Periode: <span x-text="filters.date_from || 'Semua'"></span> s/d <span x-text="filters.date_to || 'Semua'"></span></li>
                        <li>Pencarian: <span x-text="filters.search || '-' "></span></li>
                    </ul>
                </div>
            </div>
            
            {{-- Loading Overlay --}}
            <div x-show="loading" x-transition class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 no-print">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
            
            <div id="recap-table-wrapper">
                {{-- Kontainer ini sekarang dibungkus dengan x-html di parent, jadi kita hanya perlu render partialnya --}}
                @include('tasks.partials._recap_table_content', ['tasks' => $tasks, 'project' => $project, 'request' => $request])
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script>
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function recapPage(config) {
                return {
                    baseUrl: config.baseUrl,
                    filters: { ...config.initialFilters }, // Clone the object
                    loading: false,
                    isExportingPdf: false,
                    tableHtml: '',

                    init() {
                        this.tableHtml = document.getElementById('recap-table-wrapper').innerHTML;
                        this.setupFilterWatchers();
                        this.$el.addEventListener('click', (e) => this.handleDelegatedClick(e));
                        this.updateUrl(false);
                        window.onpopstate = (event) => {
                            if (event.state) {
                                Object.assign(this.filters, event.state);
                                this.fetchData(false);
                            }
                        };
                    },

                    setupFilterWatchers() {
                        this.$watch('filters.search', debounce(() => this.applyFilters(), 500));
                        ['status', 'user_id', 'per_page', 'date_from', 'date_to'].forEach(key => {
                            this.$watch(`filters.${key}`, () => this.applyFilters());
                        });
                    },
                    
                    handleDelegatedClick(event) {
                        const sortLink = event.target.closest('#recap-table-wrapper thead a');
                        const pageLink = event.target.closest('#recap-table-wrapper .pagination a');
                        
                        if (sortLink) {
                            event.preventDefault();
                            this.sortBy(sortLink.dataset.sortField);
                        } else if (pageLink) {
                            event.preventDefault();
                            this.goToPage(pageLink.href);
                        }
                    },

                    applyFilters() {
                        this.filters.page = 1;
                        this.fetchData();
                    },
                    
                    sortBy(field) {
                        if (!field) return;
                        if (this.filters.sort === field) {
                            this.filters.direction = this.filters.direction === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.filters.sort = field;
                            this.filters.direction = 'asc';
                        }
                        this.filters.page = 1;
                        this.fetchData();
                    },

                    goToPage(url) {
                        if (!url) return;
                        try {
                            const page = new URL(url).searchParams.get('page') || 1;
                            this.filters.page = parseInt(page);
                            this.fetchData();
                        } catch (e) { console.error("Invalid pagination URL:", url, e); }
                    },

                    fetchData(updateHistory = true) {
                        this.loading = true;
                        if (updateHistory) this.updateUrl();
                        
                        const fetchUrl = this.buildUrlParams(true);
                        
                        fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(res => res.ok ? res.json() : Promise.reject(res))
                        .then(data => {
                            const wrapper = document.getElementById('recap-table-wrapper');
                            if (wrapper) wrapper.innerHTML = data.table_html;
                        })
                        .catch(error => {
                            const wrapper = document.getElementById('recap-table-wrapper');
                            if (wrapper) wrapper.innerHTML = '<div class="p-8 text-center text-red-500">Gagal memuat data.</div>';
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                    },

                    buildUrlParams(fullPath = false) {
                        const params = new URLSearchParams();
                        Object.entries(this.filters).forEach(([key, value]) => {
                             if (value && value !== 'all' && !(key === 'page' && value === 1)) {
                                params.append(key, value);
                            }
                        });
                        const paramString = params.toString();
                        return fullPath ? `${this.baseUrl}${paramString ? '?' + paramString : ''}` : paramString;
                    },

                    updateUrl(replace = false) {
                        const newUrl = this.buildUrlParams(true);
                        const state = { ...this.filters };
                        if (replace) {
                            history.replaceState(state, '', newUrl);
                        } else {
                            history.pushState(state, '', newUrl);
                        }
                    },
                    
                    exportToPdf() {
                        if (this.isExportingPdf) return; this.isExportingPdf = true;
                        const element = document.getElementById('printable-area');
                        if (!element) { this.isExportingPdf = false; return; }
                        const date = new Date().toISOString().slice(0, 10);
                        const filename = `Rekap-Tugas-{{ Str::slug($project->name) }}-${date}.pdf`;
                        const opt = {
                            margin: [10, 5, 10, 5], filename: filename, image: { type: 'jpeg', quality: 0.98 },
                            html2canvas: { scale: 2, useCORS: true, logging: false }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                        };
                        html2pdf().set(opt).from(element).save().then(() => { this.isExportingPdf = false; }).catch(err => { this.isExportingPdf = false; });
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>