<x-app-layout>
    <div x-data="activityLogPage({
            baseUrl: '{{ route('projects.activity', $project) }}',
            initialFilters: {{ Js::from($request->query()) }}
         })"
         x-init="init()"
         class="py-8 px-4 sm:px-6 lg:px-8">

        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Activity Log - {{ $project->name }}</h1>
            
        <!-- Filter Form -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Description</label>
                    <input type="text" id="search" x-model.debounce.500ms="filters.search" placeholder="Cari deskripsi..." class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select id="user_id" x-model="filters.user_id" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        <option value="all">All Users</option>
                        @foreach($projectUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select id="action" x-model="filters.action" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        <option value="all">All Actions</option>
                        @foreach($availableActions as $action)
                            <option value="{{ $action }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2 lg:col-span-2">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" id="date_from" x-model="filters.date_from" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" id="date_to" x-model="filters.date_to" class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    </div>
                </div>
            </div>
        </div>
            
        <div x-show="loading" class="text-center py-16">
            <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-indigo-600 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="text-gray-600">Memuat log aktivitas...</span>
        </div>

        <div x-show="!loading" class="bg-white shadow-md rounded-lg overflow-hidden">
            <div id="activity-log-table-container">
                @include('projects.partials._activity_log_table', ['logs' => $logs])
            </div>
            <div id="activity-log-pagination-container" class="px-6 py-4 border-t border-gray-200">
                @if ($logs->hasPages())
                    {{ $logs->links('vendor.pagination.tailwind') }}
                @endif
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

        function activityLogPage(config) {
            return {
                baseUrl: config.baseUrl,
                filters: {
                    search: config.initialFilters.search || '',
                    user_id: config.initialFilters.user_id || 'all',
                    action: config.initialFilters.action || 'all',
                    date_from: config.initialFilters.date_from || '',
                    date_to: config.initialFilters.date_to || '',
                    sort_by: config.initialFilters.sort_by || 'created_at',
                    sort_dir: config.initialFilters.sort_dir || 'desc',
                    page: parseInt(config.initialFilters.page) || 1,
                },
                loading: true,
                fetchTimeout: null,

                init() {
                    const debouncedFetchForFilters = debounce(() => {
                        this.filters.page = 1; // Selalu reset ke page 1 saat filter berubah
                        this.fetchData();
                    }, 350);

                    // Watcher ini sekarang HANYA untuk elemen filter UI
                    ['search', 'user_id', 'action', 'date_from', 'date_to'].forEach(key => {
                        this.$watch(`filters.${key}`, () => {
                            debouncedFetchForFilters();
                        });
                    });
                    
                    this.updateUrl(false);
                    this.attachEventListeners();

                    window.onpopstate = (event) => {
                        if (event.state) {
                            // Non-aktifkan watch sementara agar tidak ada pemicu ganda
                            let oldWatcher = this.$watchers.filters;
                            this.$watchers.filters = [];
                            
                            Object.assign(this.filters, event.state);
                            
                            // Aktifkan kembali watcher setelah state diperbarui
                            this.$nextTick(() => { this.$watchers.filters = oldWatcher; });
                            
                            this.fetchData(false);
                        }
                    };
                    
                    this.fetchData(false); // Muat data awal
                },

                attachEventListeners() {
                    // Event delegation untuk semua link di dalam kontainer yang kita refresh
                    this.$el.addEventListener('click', (e) => {
                        const sortLink = e.target.closest('#activity-log-table-container thead a');
                        const pageLink = e.target.closest('#activity-log-pagination-container a');
                        
                        if (sortLink) {
                            e.preventDefault();
                            this.sortBy(sortLink.dataset.sortField || sortLink.getAttribute('href').split('sort_by=')[1].split('&')[0]);
                        } else if (pageLink) {
                            e.preventDefault();
                            this.goToPage(new URL(pageLink.href).searchParams.get('page'));
                        }
                    });
                },
                
                fetchData(updateHistory = true) {
                    this.loading = true;
                    if (updateHistory) { this.updateUrl(); }
                    
                    const fetchUrl = this.buildUrlParams(true);

                    fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => res.ok ? res.json() : Promise.reject(res))
                    .then(data => {
                        document.getElementById('activity-log-table-container').innerHTML = data.table_html;
                        document.getElementById('activity-log-pagination-container').innerHTML = data.pagination_html;
                    })
                    .catch(error => console.error('Error fetching data:', error))
                    .finally(() => { this.loading = false; });
                },
                
                sortBy(field) {
                    if (!field) return;
                    if (this.filters.sort_by === field) {
                        this.filters.sort_dir = this.filters.sort_dir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.filters.sort_by = field;
                        this.filters.sort_dir = 'asc';
                    }
                    this.filters.page = 1;
                    this.fetchData();
                },

                goToPage(page) {
                    if (page) {
                        this.filters.page = parseInt(page);
                        this.fetchData();
                    }
                },
                
                buildUrlParams(fullPath = false) {
                    const params = new URLSearchParams();
                    // Gunakan Object.entries untuk iterasi yang lebih aman
                    Object.entries(this.filters).forEach(([key, value]) => {
                        if (value && value !== 'all' && !(key === 'page' && value === 1)) {
                             params.append(key, value);
                        }
                    });
                    const paramString = params.toString();
                    return fullPath ? `${this.baseUrl}${paramString ? '?' + paramString : ''}` : paramString;
                },
                
                updateUrl() {
                    const newUrl = this.buildUrlParams(true);
                    if (window.location.href !== newUrl) {
                        history.pushState({ ...this.filters }, '', newUrl);
                    }
                }
            }
        }
    </script>
@endpush
</x-app-layout>