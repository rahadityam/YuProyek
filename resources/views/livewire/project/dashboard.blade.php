<div> {{-- Root Element Wajib --}}

    {{-- Konten dari view dashboard.blade.php LAMA, TANPA <x-app-layout> --}}

    {{-- Tab Navigation (Kontrol dengan property Livewire $activeTab) --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            {{-- Tombol Tab Tugas --}}
            <button wire:click="switchTab('tasks')" type="button"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group
                           {{ $activeTab === 'tasks' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 transition-colors duration-150 {{ $activeTab === 'tasks' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}">...</svg>
                Ringkasan Tugas
            </button>
            {{-- Tombol Tab Keuangan --}}
            <button wire:click="switchTab('finance')" type="button"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group
                           {{ $activeTab === 'finance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 transition-colors duration-150 {{ $activeTab === 'finance' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}">...</svg>
                Ringkasan Keuangan
            </button>
        </nav>
    </div>

    {{-- Loading Indicator Global (Opsional) --}}
    <div wire:loading class="w-full text-center py-4 text-gray-500">
        Loading Dashboard Data...
    </div>

    {{-- Konten Tab (ditampilkan berdasarkan $activeTab) --}}
    <div wire:loading.remove>
        {{-- Konten Tab Tugas --}}
        <div x-data="{ show: @entangle('activeTab').defer == 'tasks' }" x-show="show" x-transition.opacity.duration.300ms>
            {{-- Grafik Tugas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b"><h3 class="font-semibold text-gray-800">Status Tugas</h3></div>
                    <div class="p-6">
                        <div class="h-64">
                            {{-- Gunakan componentId property sebagai data attribute --}}
                            <canvas class="dashboard-chart-canvas" data-chart-type="taskStatus" data-component-id="{{ $componentId }}" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b"><h3 class="font-semibold text-gray-800">Beban Tugas per Anggota</h3></div>
                    <div class="p-6">
                        <div class="h-64">
                            {{-- Gunakan componentId property sebagai data attribute --}}
                            <canvas class="dashboard-chart-canvas" data-chart-type="tasksByAssignee" data-component-id="{{ $componentId }}" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Statistik Tugas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                {{-- ... Card Statistik Tugas (tidak berubah) ... --}}
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-gray-100 text-gray-500"> <svg>...</svg> </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Todo</h4> <p class="text-xl font-semibold">{{ $taskStats['todo'] }}</p> </div> </div>
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-yellow-100 text-yellow-600"> <svg>...</svg> </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">In Progress</h4> <p class="text-xl font-semibold">{{ $taskStats['in_progress'] }}</p> </div> </div>
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600"> <svg>...</svg> </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Review</h4> <p class="text-xl font-semibold">{{ $taskStats['review'] }}</p> </div> </div>
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-green-100 text-green-600"> <svg>...</svg> </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Selesai</h4> <p class="text-xl font-semibold">{{ $taskStats['done'] }}</p> </div> </div>
            </div>
        </div>

        {{-- Konten Tab Keuangan --}}
        <div x-data="{ show: @entangle('activeTab').defer == 'finance' }" x-show="show" x-transition.opacity.duration.300ms>
            {{-- Grafik Keuangan --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b"><h3 class="font-semibold text-gray-800">Ringkasan Pembayaran</h3></div>
                    <div class="p-6"><div class="h-64">
                        {{-- Gunakan componentId property sebagai data attribute --}}
                        <canvas class="dashboard-chart-canvas" data-chart-type="financialOverview" data-component-id="{{ $componentId }}" wire:ignore></canvas>
                    </div></div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b"><h3 class="font-semibold text-gray-800">Budget vs Pengeluaran</h3></div>
                    <div class="p-6"><div class="h-64">
                        {{-- Gunakan componentId property sebagai data attribute --}}
                        <canvas class="dashboard-chart-canvas" data-chart-type="spendingVsBudget" data-component-id="{{ $componentId }}" wire:ignore></canvas>
                    </div></div>
                </div>
            </div>
            {{-- Statistik Keuangan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                 {{-- ... Card Statistik Keuangan (tidak berubah) ... --}}
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-gray-100 text-gray-500"> <svg>...</svg> </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Budget</h4> <p class="text-xl font-semibold">Rp {{ number_format($financialStats['budget'], 0, ',', '.') }}</p> </div> </div>
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600"> <svg>...</svg> </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Total Estimasi Gaji</h4> <p class="text-xl font-semibold">Rp {{ number_format($financialStats['totalHakGaji'], 0, ',', '.') }}</p> <p class="text-[10px] text-gray-400">(Task: {{number_format($financialStats['totalTaskHakGaji'], 0, ',', '.')}} + Lain: {{number_format($financialStats['totalOtherFullHakGaji'], 0, ',', '.')}})</p> </div> </div>
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-green-100 text-green-600"> <svg>...</svg> </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Total Dibayar</h4> <p class="text-xl font-semibold">Rp {{ number_format($financialStats['totalPaid'], 0, ',', '.') }}</p> <p class="text-[10px] text-gray-400">(Task/Term: {{number_format($financialStats['totalPaidTaskTermin'], 0, ',', '.')}} + Lain: {{number_format($financialStats['totalPaidOtherFull'], 0, ',', '.')}})</p> </div> </div>
                 @php $budgetDiff = $financialStats['budgetDifference']; $budgetColor = $budgetDiff >= 0 ? 'yellow' : 'red'; $budgetLabel = $budgetDiff >= 0 ? 'Sisa' : 'Lebih'; @endphp
                 <div class="bg-white rounded-lg shadow p-4 flex items-center"> <div class="rounded-full h-10 w-10 flex items-center justify-center bg-{{ $budgetColor }}-100 text-{{ $budgetColor }}-600"> @if($budgetDiff >= 0) <svg>...</svg> @else <svg>...</svg> @endif </div> <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">{{ $budgetLabel }} Budget</h4> <p class="text-xl font-semibold text-{{ $budgetColor }}-600">Rp {{ number_format(abs($budgetDiff), 0, ',', '.') }}</p> <p class="text-[10px] text-gray-400">(Budget vs Estimasi)</p> </div> </div>
            </div>
        </div>
    </div>

    {{-- Konten yang Selalu Tampil (Aktivitas & Task Berjalan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        {{-- ... Card Aktivitas & Task Berjalan (tidak berubah) ... --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden"> <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center"> <h3 class="font-semibold text-gray-800">Aktivitas Terakhir</h3> <a href="{{ route('projects.activity', $project) }}" wire:navigate class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a> </div> <div class="p-6 max-h-96 overflow-y-auto"> <div class="space-y-4"> @forelse($recentActivities as $activity) <div class="flex items-start"> <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-medium"> {{ substr($activity->user->name ?? '?', 0, 1) }} </div> <div class="ml-3 flex-grow"> <p class="text-sm text-gray-700"> <span class="font-medium">{{ $activity->user->name ?? 'System' }}</span> <span class="text-gray-500">{{ $activity->description }}</span> </p> <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p> </div> </div> @empty <p class="text-gray-500 italic text-sm">Tidak ada aktivitas terbaru.</p> @endforelse </div> </div> </div>
        <div class="bg-white rounded-lg shadow-md overflow-hidden"> <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center"> <h3 class="font-semibold text-gray-800">Tugas Sedang Dikerjakan</h3> <a href="{{ route('projects.kanban', $project) }}" wire:navigate class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a> </div> <div class="p-6 max-h-96 overflow-y-auto"> @if($inProgressTasks->count() > 0) <div class="space-y-4"> @foreach($inProgressTasks as $task) <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150"> <div class="flex justify-between items-start mb-2"> <h4 class="font-medium text-gray-800 text-sm">{{ $task->title }}</h4> <span class="text-xs text-gray-500">{{ $task->assignedUser->name ?? 'Unassigned' }}</span> </div> @php /* Kalkulasi progress bar */ @endphp <div class="mt-2"> <div class="flex justify-between items-center mb-1"> <span class="text-xs">...</span> <span class="text-xs">...</span> </div> <div class="w-full bg-gray-200 rounded-full h-1.5"> <div class="bg-blue-500 h-1.5 rounded-full" style="..."></div> </div> </div> </div> @endforeach </div> @else <div class="py-8 text-center text-gray-500"> <svg>...</svg> <p class="text-sm">Tidak ada tugas.</p> </div> @endif </div> </div>
    </div>

    {{-- Javascript untuk Chart --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Buat objek global untuk menyimpan instance chart
            window.livewireDashboardCharts = window.livewireDashboardCharts || {};

            function initOrUpdateDashboardChart(componentId, chartType, config) {
                const canvasSelector = `canvas.dashboard-chart-canvas[data-chart-type="${chartType}"][data-component-id="${componentId}"]`;
                const canvas = document.querySelector(canvasSelector);
                const instanceName = `${chartType}ChartInstance_${componentId}`;

                if (!canvas) {
                    // console.warn(`Canvas for chart type "${chartType}" with component ID "${componentId}" not found.`);
                    return; // Keluar jika canvas tidak ditemukan
                }

                // Hancurkan chart lama jika ada
                if (window.livewireDashboardCharts[instanceName]) {
                    // console.log(`Destroying existing chart: ${instanceName}`);
                    window.livewireDashboardCharts[instanceName].destroy();
                }

                // Buat chart baru
                // console.log(`Initializing chart: ${instanceName}`);
                window.livewireDashboardCharts[instanceName] = new Chart(canvas.getContext('2d'), config);
            }

            // Simpan data chart awal dari PHP (dilakukan sekali saat render awal)
            const initialTaskData_{{ $componentId }} = @json($taskStats);
            const initialAssigneeData_{{ $componentId }} = @json($tasksByAssigneeStatusChartData);
            const initialFinancialOverviewData_{{ $componentId }} = @json($financialStats['overviewChartData']);
            const initialSpendingData_{{ $componentId }} = @json($financialStats['spendingVsBudgetChartData']);

            // Fungsi untuk setup semua chart Dashboard
            function setupAllDashboardCharts_{{ $componentId }}() {
                // console.log('Setting up all dashboard charts for component {{ $componentId }}');
                 initOrUpdateDashboardChart('{{ $componentId }}', 'taskStatus', { type: 'doughnut', data: { labels: ['Todo', 'In Progress', 'Review', 'Done'], datasets: [{ label: 'Jumlah Tugas', data: [initialTaskData_{{ $componentId }}.todo, initialTaskData_{{ $componentId }}.in_progress, initialTaskData_{{ $componentId }}.review, initialTaskData_{{ $componentId }}.done], backgroundColor: ['#E5E7EB', '#FCD34D', '#93C5FD', '#6EE7B7'], borderColor: ['#9CA3AF', '#F59E0B', '#3B82F6', '#10B981'], borderWidth: 1, hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } } } } });
                 initOrUpdateDashboardChart('{{ $componentId }}', 'tasksByAssignee', { type: 'bar', data: initialAssigneeData_{{ $componentId }}, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true } }, plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } }, tooltip: { mode: 'index', intersect: false, callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.x !== null) { label += context.parsed.x; } return label; } } } } } });
                 initOrUpdateDashboardChart('{{ $componentId }}', 'financialOverview', { type: 'doughnut', data: { labels: [ 'Task/Termin Dibayar', 'Lainnya Dibayar', 'Estimasi Belum Dibayar' ], datasets: [{ label: 'Jumlah (Rp)', data: [ initialFinancialOverviewData_{{ $componentId }}.paidTaskTermin, initialFinancialOverviewData_{{ $componentId }}.paidOtherFull, initialFinancialOverviewData_{{ $componentId }}.remainingUnpaid ], backgroundColor: ['#6EE7B7', '#A7F3D0', '#FCD34D'], borderColor: ['#10B981', '#34D399', '#F59E0B'], borderWidth: 1, hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } }, tooltip: { callbacks: { label: (context) => ` ${context.label}: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed)}` } } } } });
                 initOrUpdateDashboardChart('{{ $componentId }}', 'spendingVsBudget', { type: 'bar', data: { labels: ['Status Keuangan'], datasets: [ { label: 'Budget', data: [initialSpendingData_{{ $componentId }}.budget], backgroundColor: '#BFDBFE', borderColor: '#60A5FA', borderWidth: 1 }, { label: 'Estimasi Gaji', data: [initialSpendingData_{{ $componentId }}.hakGaji], backgroundColor: '#FEF3C7', borderColor: '#FBBF24', borderWidth: 1 }, { label: 'Sudah Dibayar (Approved)', data: [initialSpendingData_{{ $componentId }}.paid], backgroundColor: '#A7F3D0', borderColor: '#34D399', borderWidth: 1 } ] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', notation: 'compact' }).format(value) } } }, plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } }, tooltip: { callbacks: { label: (context) => ` ${context.dataset.label}: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.raw)}` }
            }
            
            // Inisialisasi saat Livewire dimuat
            document.addEventListener('livewire:load', function () {
                // Hanya setup chart jika elemen canvas ada di DOM
                if (document.querySelector(`canvas.dashboard-chart-canvas[data-component-id="{{ $componentId }}"]`)) {
                    setupAllDashboardCharts_{{ $componentId }}();
                }
            });

            // Re-inisialisasi chart SETELAH Livewire selesai mengupdate DOM
            document.addEventListener('livewire:update', () => {
                // Beri sedikit delay untuk memastikan DOM benar-benar siap
                setTimeout(() => {
                    if (document.querySelector(`canvas.dashboard-chart-canvas[data-component-id="{{ $componentId }}"]`)) {
                        // console.log('Livewire updated, re-initializing charts for {{ $componentId }}');
                        setupAllDashboardCharts_{{ $componentId }}();
                    }
                }, 50); // delay 50ms
            });

            // Cleanup chart saat komponen dihancurkan
            document.addEventListener('livewire:unload', function () {
                // console.log('Unloading dashboard charts for component {{ $componentId }}');
                const chartTypes = ['taskStatus', 'tasksByAssignee', 'financialOverview', 'spendingVsBudget'];
                chartTypes.forEach(type => {
                    const instanceName = `${type}ChartInstance_{{ $componentId }}`;
                    if (window.livewireDashboardCharts[instanceName]) {
                        window.livewireDashboardCharts[instanceName].destroy();
                        delete window.livewireDashboardCharts[instanceName];
                    }
                });
            });
        </script>
    @endpush
</div>