<x-app-layout>
    <div x-data="payslipListFilter({
            baseUrl: '{{ route('projects.payslips.history', $project) }}',
            initialFilters: {{ json_encode($request->query()) }},
            isProjectOwner: {{ Js::from($isProjectOwner) }}
         })"
         x-init="init()"
         class="py-6 px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Daftar Slip Gaji - {{ $project->name }}</h2>
            {{-- Tombol Buat Slip Gaji sudah dipindah ke halaman Perhitungan Gaji --}}
        </div>

         <!-- Tabs -->
         <div class="border-b border-gray-200 mb-6">
             <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                 <a href="{{ route('projects.payroll.calculate', $project) }}"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                     Perhitungan Gaji
                 </a>
                  <a href="{{ route('projects.payslips.history', $project) }}"
                     class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                      Daftar Slip Gaji
                  </a>
             </nav>
         </div>

         {{-- Flash Message --}}
         @if(request()->has('success_message') || session('success'))
             <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                 <span class="block sm:inline">{{ request('success_message', session('success')) }}</span>
             </div>
         @endif
          @if($errors->has('general'))
             <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                  <span class="block sm:inline">{{ $errors->first('general') }}</span>
              </div>
          @endif

        {{-- Filter Form --}}
         <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
             <form @submit.prevent="applyFilters">
                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                     <div>
                         <label for="filter_search" class="block text-sm font-medium text-gray-700">Cari</label>
                         <input type="text" name="search" id="filter_search" x-model.debounce.500ms="filters.search" placeholder="Nama slip/pekerja..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                     </div>
                     @if($isProjectOwner)
                     <div>
                        <label for="filter_user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                        <select name="user_id" id="filter_user_id" x-model="filters.user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                            <option value="">Semua Pekerja</option>
                            @foreach ($workersForFilter as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                     </div>
                     @endif
                     <div>
                         <label for="filter_payment_type" class="block text-sm font-medium text-gray-700">Tipe Slip</label>
                         <select name="payment_type" id="filter_payment_type" x-model="filters.payment_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                             <option value="">Semua Tipe</option>
                              @foreach ($paymentTypes as $type)
                                 <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                              @endforeach
                         </select>
                     </div>
                      @if($isProjectOwner)
                     <div>
                         <label for="filter_status" class="block text-sm font-medium text-gray-700">Status Slip</label>
                         <select name="status" id="filter_status" x-model="filters.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                             <option value="">Semua Status</option>
                             @foreach ($statusesForFilter as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                             @endforeach
                         </select>
                     </div>
                     @endif
                     <div class="grid grid-cols-2 gap-2">
                         <div>
                             <label for="filter_date_from" class="block text-sm font-medium text-gray-700">Dari Tgl.</label>
                             <input type="date" name="date_from" id="filter_date_from" x-model="filters.date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                         </div>
                         <div>
                             <label for="filter_date_to" class="block text-sm font-medium text-gray-700">Sampai Tgl.</label>
                             <input type="date" name="date_to" id="filter_date_to" x-model="filters.date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                         </div>
                     </div>
                 </div>
                  <div class="mt-4 flex justify-end space-x-2">
                       <button type="button" @click="resetFilters()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                           Reset
                       </button>
                       {{-- Tombol Terapkan tidak diperlukan lagi untuk filter live --}}
                  </div>
             </form>
         </div>

        {{-- Table Container --}}
        <div x-show="loading" class="text-center py-10">
            <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-600 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="text-gray-600">Memuat data slip gaji...</span>
        </div>

         <div x-show="!loading">
             <div id="payslip-list-table-container" x-ref="tableContainer" class="bg-white shadow overflow-hidden sm:rounded-md">
                  <div x-html="tableHtml">
                      @include('payslips.partials._list_table_content', ['payslips' => $payslips, 'project' => $project, 'request' => $request, 'isProjectOwner' => $isProjectOwner])
                  </div>
             </div>
             <div id="payslip-list-pagination-container" class="mt-4" x-html="paginationHtml">
                 {{-- Render initial pagination --}}
                 @if ($payslips->hasPages())
                    {{ $payslips->links('vendor.pagination.tailwind') }}
                 @endif
             </div>
         </div>

    </div>

    @push('scripts')
    <script>
        function payslipListFilter(config) {
            return {
                baseUrl: config.baseUrl,
                filters: {
                    search: config.initialFilters.search || '',
                    user_id: config.initialFilters.user_id || '',
                    payment_type: config.initialFilters.payment_type || '',
                    status: config.initialFilters.status || '',
                    date_from: config.initialFilters.date_from || '',
                    date_to: config.initialFilters.date_to || '',
                    sort: config.initialFilters.sort || 'updated_at', // Default sort
                    direction: config.initialFilters.direction || 'desc',
                    page: config.initialFilters.page || 1
                },
                isProjectOwner: config.isProjectOwner,
                loading: false,
                tableHtml: '',
                paginationHtml: '',
                currentUrl: '',

                init() {
                    this.tableHtml = this.$refs.tableContainer.querySelector('[x-html="tableHtml"]').innerHTML;
                    const paginationDiv = document.getElementById('payslip-list-pagination-container');
                    if (paginationDiv) this.paginationHtml = paginationDiv.innerHTML;

                    // Pastikan filter user_id untuk non-owner adalah ID mereka
                    if (!this.isProjectOwner && {{ Auth::check() ? 'true' : 'false' }}) {
                        this.filters.user_id = '{{ Auth::id() }}';
                    }

                    this.updateUrlWithoutReload();

                    // Watchers for live filtering
                    Object.keys(this.filters).forEach(key => {
                        if (key !== 'sort' && key !== 'direction' && key !== 'page') {
                            this.$watch(`filters.${key}`, () => {
                                this.filters.page = 1; // Reset to page 1 on filter change
                                this.fetchData();
                            });
                        }
                    });

                    // Handle pagination clicks
                    this.$watch('tableHtml', () => { // Re-attach listeners after table update
                        this.attachPaginationListeners();
                    });
                    this.attachPaginationListeners();


                    // Handle back/forward browser buttons
                    window.addEventListener('popstate', (event) => {
                        if (event.state) {
                            this.filters = { ...this.filters, ...event.state };
                            // Re-ensure non-owner filter
                            if (!this.isProjectOwner && {{ Auth::check() ? 'true' : 'false' }}) {
                                this.filters.user_id = '{{ Auth::id() }}';
                            }
                            this.filters.page = parseInt(this.filters.page) || 1;
                            this.fetchData(false); // Don't push state again
                        }
                    });
                },

                attachPaginationListeners() {
                    this.$nextTick(() => {
                        const paginationLinks = document.querySelectorAll('#payslip-list-pagination-container .pagination a');
                        paginationLinks.forEach(link => {
                            link.addEventListener('click', (e) => {
                                e.preventDefault();
                                this.goToPage(link.href);
                            });
                        });
                    });
                },

                buildUrlParams() {
                    const activeFilters = {};
                    for (const key in this.filters) {
                        if (!this.isProjectOwner && key === 'user_id') {
                             activeFilters[key] = '{{ Auth::id() }}';
                             continue;
                        }
                        if (this.filters[key] !== null && this.filters[key] !== '' && this.filters[key] !== 'all') {
                             if (key === 'page' && this.filters[key] === 1 && !this.hasOtherFilters()) {
                                 // Don't include page=1 if no other filters are active, to keep URL clean
                             } else if (key === 'page' && this.filters[key] === 1 && this.hasOtherFilters()) {
                                 activeFilters[key] = this.filters[key]; // Include page=1 if other filters exist
                             } else if (key !== 'page' || this.filters[key] !== 1) {
                                 activeFilters[key] = this.filters[key];
                             }
                        }
                    }
                    return new URLSearchParams(activeFilters).toString();
                },

                hasOtherFilters() { // Helper to check if any filter other than page is active
                    return Object.keys(this.filters).some(key =>
                        key !== 'page' && this.filters[key] !== null && this.filters[key] !== '' && this.filters[key] !== 'all'
                    );
                },

                updateUrlWithoutReload() {
                    const params = this.buildUrlParams();
                    const newUrl = `${this.baseUrl}${params ? '?' + params : ''}`;
                    if (newUrl !== this.currentUrl) {
                        history.replaceState({ ...this.filters }, '', newUrl);
                        this.currentUrl = newUrl;
                    }
                },

                fetchData(updateHistory = true) {
                    this.loading = true;
                    const params = this.buildUrlParams();
                    const fetchUrl = `${this.baseUrl}${params ? '?' + params : ''}`;

                    fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response error');
                        return response.json();
                    })
                    .then(data => {
                        this.tableHtml = data.table_html;
                        this.paginationHtml = data.pagination_html || '';
                        if (updateHistory && fetchUrl !== this.currentUrl) {
                            history.pushState({ ...this.filters }, '', fetchUrl);
                            this.currentUrl = fetchUrl;
                        }
                         this.$nextTick(() => this.attachPaginationListeners());
                    })
                    .catch(error => {
                        console.error('Error fetching payslip list:', error);
                        this.tableHtml = `<tr><td colspan="8" class="text-center py-4 text-red-500">Gagal memuat data.</td></tr>`;
                        this.paginationHtml = '';
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                },

                applyFilters() { // Called by watchers
                    this.filters.page = 1;
                    this.fetchData();
                },

                sortBy(field) {
                    let newDirection = 'asc';
                    if (this.filters.sort === field && this.filters.direction === 'asc') {
                        newDirection = 'desc';
                    }
                    this.filters.sort = field;
                    this.filters.direction = newDirection;
                    this.filters.page = 1;
                    this.fetchData();
                },

                goToPage(url) {
                    try {
                        const targetUrl = new URL(url);
                        const page = targetUrl.searchParams.get('page') || 1;
                        this.filters.page = parseInt(page);
                        this.fetchData(); // History will be updated by fetchData
                    } catch (e) {
                        console.error("Invalid URL for pagination:", url, e);
                    }
                },

                resetFilters() {
                    this.filters.search = '';
                    this.filters.user_id = this.isProjectOwner ? '' : '{{ Auth::id() }}';
                    this.filters.payment_type = '';
                    this.filters.status = '';
                    this.filters.date_from = '';
                    this.filters.date_to = '';
                    this.filters.sort = 'updated_at';
                    this.filters.direction = 'desc';
                    this.filters.page = 1;
                    this.fetchData();
                }
            }
        }
    </script>
    @endpush
</x-app-layout>