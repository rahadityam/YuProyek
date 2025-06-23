<x-app-layout>
    {{-- PERBAIKAN: Sanitasi data filter di sini untuk menghindari error "could not be cloned" --}}
    @php
        $initialFiltersConfig = [
            'search' => $request->query('search', ''),
            'user_id' => $request->query('user_id', ''),
            'payment_type' => $request->query('payment_type', ''),
            'status' => $request->query('status', ''),
            'date_from' => $request->query('date_from', ''),
            'date_to' => $request->query('date_to', ''),
            'sort' => $request->query('sort', 'updated_at'),
            'direction' => $request->query('direction', 'desc'),
            'page' => (int) $request->query('page', 1),
        ];
    @endphp

    <div id="payslip-list-component"
         x-data="payslipListFilter({
            baseUrl: '{{ route('projects.payslips.history', $project) }}',
            initialFilters: {{ Js::from($initialFiltersConfig) }},
            isProjectOwner: {{ Js::from($isProjectOwner) }}
         })"
         x-init="init()"
         class="py-6 px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Daftar Slip Gaji - {{ $project->name }}</h2>
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
         @if(request()->get('success_message') || session('success'))
             <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                 <span class="block sm:inline">{{ request()->get('success_message', session('success')) }}</span>
             </div>
         @endif
          @if($errors->has('general'))
             <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                  <span class="block sm:inline">{{ $errors->first('general') }}</span>
              </div>
          @endif

        {{-- PERBAIKAN: Hapus @submit.prevent dan trigger @input/@change --}}
        <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="filter_search" class="block text-sm font-medium text-gray-700">Cari</label>
                    <input type="text" name="search" id="filter_search" 
                           x-model.debounce.500ms="filters.search" 
                           placeholder="Nama slip/pekerja..." 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
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
                        <div class="grid grid-cols-2 gap-2 {{ $isProjectOwner ? '' : 'lg:col-span-2' }}">
                    <div>
                        <label for="filter_date_from" class="block text-sm font-medium text-gray-700">Dari Tgl.</label>
                        <input type="date" name="date_from" id="filter_date_from" x-model="filters.date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>
                    <div>
                        <label for="filter_date_to" class="block text-sm font-medium text-gray-700">Sampai Tgl.</label>
                        <input type="date" name="date_to" id="filter_date_to" x-model="filters.date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>
                </div>
                <div class="{{ $isProjectOwner ? 'lg:col-span-5' : 'lg:col-span-1' }} flex justify-end">
                   <button type="button" @click="resetFilters()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                       Reset
                   </button>
                </div>
            </div>
        </div>

        <div class="relative">
            {{-- Loading Overlay --}}
            <div x-show="loading" x-transition class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>

            {{-- PERBAIKAN: Satu wrapper untuk tabel dan pagination --}}
            <div id="table-wrapper">
                {{-- Render awal dari server --}}
                @include('payslips.partials._list_table_content', ['payslips' => $payslips, 'project' => $project, 'request' => $request, 'isProjectOwner' => $isProjectOwner])
                <div class="px-6 py-4 border-t border-gray-200 bg-white">
                     @if ($payslips->hasPages())
                        {{ $payslips->links('vendor.pagination.tailwind') }}
                     @endif
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        function payslipListFilter(config) {
            return {
                baseUrl: config.baseUrl,
                filters: { ...config.initialFilters }, // Clone
                isProjectOwner: config.isProjectOwner,
                loading: false,
                
                init() {
                    // Cek jika user bukan owner, set filter user_id secara default
                    if (!this.isProjectOwner) {
                        this.filters.user_id = '{{ Auth::id() }}';
                    }
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

                // PERBAIKAN: Implementasi watcher terpusat
                setupFilterWatchers() {
                    this.$watch('filters.search', debounce(() => this.applyFilters(), 500));

                    ['user_id', 'payment_type', 'status', 'date_from', 'date_to'].forEach(key => {
                        this.$watch(`filters.${key}`, () => this.applyFilters());
                    });
                },

                applyFilters() {
                    this.filters.page = 1;
                    this.fetchData();
                },
                
                handleDelegatedClick(event) {
                    const sortLink = event.target.closest('#table-wrapper thead a');
                    const pageLink = event.target.closest('#table-wrapper .pagination a');
                    
                    if (sortLink) {
                        event.preventDefault();
                        this.sortBy(sortLink.dataset.sortField);
                    } else if (pageLink) {
                        event.preventDefault();
                        this.goToPage(pageLink.href);
                    }
                },
                
                sortBy(field) {
                    if (!field) return;
                    this.filters.direction = (this.filters.sort === field && this.filters.direction === 'asc') ? 'desc' : 'asc';
                    this.filters.sort = field;
                    this.filters.page = 1;
                    this.fetchData();
                },

                getSortIndicator(field) {
                    if (this.filters.sort !== field) return '↕'; // Indikator default
                    return this.filters.direction === 'asc' ? '↑' : '↓';
                },

                goToPage(url) {
                    if(!url) return;
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
                        const wrapper = document.getElementById('table-wrapper');
                        if (wrapper) wrapper.innerHTML = data.table_html + data.pagination_html;
                    })
                    .catch(error => {
                        const wrapper = document.getElementById('table-wrapper');
                        if (wrapper) wrapper.innerHTML = `<div class="bg-white p-6 rounded-md shadow"><p class="text-center text-red-500 font-semibold">Gagal memuat data. Silakan coba lagi.</p></div>`;
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
                    if (replace) {
                        history.replaceState({ ...this.filters }, '', newUrl);
                    } else {
                        history.pushState({ ...this.filters }, '', newUrl);
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