<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<x-app-layout>
    <main class="ml-64 p-6">
        <div class="flex">
            {{-- Sidebar --}}
            @include('ceo.sidebar_ceo')
            <div class="container mx-auto p-6">
                <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col">

                    <!-- Header -->
                    <div class="mt-5 mb-5 max-w-7xl mx-auto w-full">
                        <h1 class="text-3xl font-bold mb-1 text-left">Dashboard</h1>
                        <p class="text-gray-600 text-sm">Project and user summary</p>
                    </div>

                    <!-- Summary Cards -->
                    <div class="max-w-7xl mx-auto space-y-4 mb-6 w-full">
                        <!-- Row 1: Project Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Total Projects</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalProjects }}</p>
                                </div>
                                <div class="bg-blue-500 rounded-[6px] p-2"><i class="bi bi-folder-fill text-white text-2xl"></i></div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Proyek Aktif</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalActiveProjects }}</p>
                                </div>
                                <div class="bg-green-500 rounded-[6px] p-2"><i class="bi bi-play-circle-fill text-white text-2xl"></i></div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Proyek Tidak Aktif</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalInactiveProjects }}</p>
                                </div>
                                <div class="bg-yellow-500 rounded-[6px] p-2"><i class="bi bi-pause-circle-fill text-white text-2xl"></i></div>
                            </div>
                        </div>
                        <!-- Row 2: User Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Total Users</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalUsers }}</p>
                                </div>
                                <div class="bg-gray-400 rounded-[6px] p-2"><i class="bi bi-people-fill text-white text-2xl"></i></div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Active Workers</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $activeWorkersCount }}</p>
                                </div>
                                <div class="bg-green-500 rounded-[6px] p-2"><i class="bi bi-person-check-fill text-white text-2xl"></i></div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Inactive Workers</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $inactiveWorkersCount }}</p>
                                </div>
                                <div class="bg-yellow-500 rounded-[6px] p-2"><i class="bi bi-person-dash-fill text-white text-2xl"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- == PERBAIKAN LAYOUT DIMULAI DI SINI == -->
                    <!-- ============================================== -->

                    <!-- Project Progress Summary -->
                    <!-- DIV ini disederhanakan, dihapus bg-white, shadow, dan p-6 -->
                    <div class="mb-6 max-w-7xl mx-auto w-full">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">Summary Project</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($projectsProgress as $project)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <h3 class="font-semibold text-lg text-gray-700 mb-1">{{ $project['name'] }}</h3>
                                <p class="text-sm text-gray-500 mb-2">{{ \Carbon\Carbon::parse($project['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($project['end_date'])->format('d M Y') }}</p>
                                <div class="mb-1 flex justify-between text-xs text-gray-600">
                                    <span>{{ $project['completed_tasks'] }}/{{ $project['total_tasks'] }} selesai</span>
                                    <span>{{ $project['progress_percent'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-blue-500 h-2 rounded-full" style="width: {{ $project['progress_percent'] }}%"></div></div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500">Tidak ada proyek berjalan.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Charts -->
                    <!-- DIV ini disederhanakan, dihapus flex-1 -->
                    <div class="flex gap-6 max-w-7xl mx-auto w-full">
                        <!-- Kartu Chart Kiri -->
                        <div class="bg-white rounded shadow p-6 w-1/2 flex flex-col border-b-2 border-green-500">
                            <div class="flex justify-between items-center mb-4 border-b pb-2 border-gray-300"><h3 class="text-xl font-semibold text-center flex-shrink-0">User Status</h3></div>
                            <div class="relative flex-grow"><canvas id="userStatusChart"></canvas></div>
                        </div>
                        <!-- Kartu Chart Kanan -->
                        <div class="bg-white rounded shadow p-6 w-1/2 flex flex-col border-b-2 border-blue-500">
                            <div class="flex justify-between items-center mb-4 border-b pb-2 border-gray-300">
                                <h3 class="text-xl font-semibold text-center flex-shrink-0">Projects</h3>
                                <div class="relative">
                                    <button onclick="toggleDropdown()" class="text-gray-700 hover:text-black focus:outline-none"><i class="bi bi-chevron-down text-base"></i></button>
                                    <ul id="chartDropdown" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-50">
                                        <li><button onclick="changeChart('daily')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Day</button></li>
                                        <li><button onclick="changeChart('monthly')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Month</button></li>
                                        <li><button onclick="changeChart('yearly')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Year</button></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="relative flex-grow"><canvas id="projectsLineChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Script (tidak ada perubahan di sini) -->
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('chartDropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        window.changeChart = function(type) {
            const canvasId = 'projectsLineChart';
            const data = window.chartDataSets[type];
            const existingChart = Chart.getChart(canvasId);
            if (existingChart) existingChart.destroy();
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Number of Projects Started',
                        data: data.data,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'bottom' } },
                    scales: { y: { beginAtZero: true, ticks: { callback: function(value) { if (Number.isInteger(value)) return value; } } } }
                }
            });
            const dropdown = document.getElementById('chartDropdown');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                toggleDropdown();
            }
        };

        function initDashboardCharts() {
            window.chartDataSets = {
                daily: { labels: {!! json_encode($projectLabels['daily'] ?? []) !!}, data: {!! json_encode($projectData['daily'] ?? []) !!} },
                monthly: { labels: {!! json_encode($projectLabels['monthly'] ?? []) !!}, data: {!! json_encode($projectData['monthly'] ?? []) !!} },
                yearly: { labels: {!! json_encode($projectLabels['yearly'] ?? []) !!}, data: {!! json_encode($projectData['yearly'] ?? []) !!} }
            };
            window.changeChart('yearly');
            const doughnutCanvasId = 'userStatusChart';
            const existingDoughnut = Chart.getChart(doughnutCanvasId);
            if (existingDoughnut) existingDoughnut.destroy();
            const userStatusCtx = document.getElementById(doughnutCanvasId);
            if (userStatusCtx) {
                new Chart(userStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Blocked'],
                        datasets: [{
                            data: {!! json_encode([($userStatus['active'] ?? 0), ($userStatus['blocked'] ?? 0)]) !!},
                            backgroundColor: ['#4ade80', '#f87171'],
                            borderColor: ['#22c55e', '#ef4444'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '50%',
                        plugins: { legend: { position: 'bottom', labels: { font: { size: 14 } } } }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initDashboardCharts);
        document.addEventListener('turbo:load', initDashboardCharts);
    </script>
</x-app-layout>