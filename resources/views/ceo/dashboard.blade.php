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
                <!-- PERUBAHAN MINIMAL: Class flex flex-col ditambahkan ke div ini -->
                <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col">

                    <!-- Tag <body> yang salah sudah dihapus, kontennya dimulai langsung di sini -->

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
                                <div class="bg-blue-500 rounded-[6px] p-2">
                                    <i class="bi bi-folder-fill text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Projects Started</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalProjectsStarted }}</p>
                                </div>
                                <div class="bg-green-500 rounded-[6px] p-2">
                                    <i class="bi bi-play-circle-fill text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Projects Ended</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalProjectsEnded }}</p>
                                </div>
                                <div class="bg-red-500 rounded-[6px] p-2">
                                    <i class="bi bi-check2-circle text-white text-2xl"></i>
                                </div>
                            </div>
                        </div>
                        <!-- Row 2: User Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Total Users</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalUsers }}</p>
                                </div>
                                <div class="bg-gray-400 rounded-[6px] p-2">
                                    <i class="bi bi-people-fill text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Active Workers</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $activeWorkersCount }}</p>
                                </div>
                                <div class="bg-green-500 rounded-[6px] p-2">
                                    <i class="bi bi-person-check-fill text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded shadow p-4 flex justify-between items-start">
                                <div class="text-left -translate-y-1">
                                    <h2 class="text-lg font-semibold text-gray-700">Inactive Workers</h2>
                                    <p class="text-black text-2xl font-bold -translate-y-1">{{ $inactiveWorkersCount }}</p>
                                </div>
                                <div class="bg-yellow-500 rounded-[6px] p-2">
                                    <i class="bi bi-person-dash-fill text-white text-2xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Progress Summary -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6 max-w-7xl mx-auto w-full">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">Summary Project</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($projectsProgress as $project)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <h3 class="font-semibold text-lg text-gray-700 mb-1">{{ $project['name'] }}</h3>
                                <p class="text-sm text-gray-500 mb-2">
                                    {{ \Carbon\Carbon::parse($project['start_date'])->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($project['end_date'])->format('d M Y') }}
                                </p>
                                <div class="mb-1 flex justify-between text-xs text-gray-600">
                                    <span>{{ $project['completed_tasks'] }}/{{ $project['total_tasks'] }} selesai</span>
                                    <span>{{ $project['progress_percent'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $project['progress_percent'] }}%"></div>
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500">Tidak ada proyek berjalan.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="flex flex-1 gap-6 max-w-7xl mx-auto w-full">
                        <!-- User Status Doughnut Chart -->
                        <div class="bg-white rounded shadow p-6 w-1/2 flex flex-col border-b-2 border-green-500">
                            <div class="flex justify-between items-center mb-4 border-b pb-2 border-gray-300">
                                <h3 class="text-xl font-semibold text-center flex-shrink-0">User Status</h3>
                            </div>
                            <div class="relative flex-grow">
                                <canvas id="userStatusChart"></canvas>
                            </div>
                        </div>
                        <!-- Projects Started Line Chart -->
                        <div class="bg-white rounded shadow p-6 w-1/2 flex flex-col border-b-2 border-blue-500">
                            <div class="flex justify-between items-center mb-4 border-b pb-2 border-gray-300">
                                <h3 class="text-xl font-semibold text-center flex-shrink-0">Projects</h3>
                                <div class="relative">
                                    <button onclick="toggleDropdown()" class="text-gray-700 hover:text-black focus:outline-none">
                                        <i class="bi bi-chevron-down text-base"></i>
                                    </button>
                                    <ul id="chartDropdown" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-50">
                                        <li><button onclick="changeChart('daily')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Day</button></li>
                                        <li><button onclick="changeChart('monthly')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Month</button></li>
                                        <li><button onclick="changeChart('yearly')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Year</button></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="relative flex-grow">
                                <canvas id="projectsLineChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- SCRIPT YANG SUDAH 100% DIPERBAIKI -->
                    <script>
                        function toggleDropdown() {
                            document.getElementById('chartDropdown').classList.toggle('hidden');
                        }

                        const chartDataSets = {
                            daily: {
                                labels: {!! json_encode($projectLabels['daily']) !!},
                                data: {!! json_encode($projectData['daily']) !!}
                            },
                            monthly: {
                                labels: {!! json_encode($projectLabels['monthly']) !!},
                                data: {!! json_encode($projectData['monthly']) !!}
                            },
                            yearly: {
                                labels: {!! json_encode($projectLabels['yearly']) !!},
                                data: {!! json_encode($projectData['yearly']) !!}
                            }
                        };

                        let lineChart;

                        function changeChart(type) {
                            const ctx = document.getElementById('projectsLineChart');
                            if (!ctx) return;
                            if (lineChart) lineChart.destroy();

                            lineChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: chartDataSets[type].labels,
                                    datasets: [{
                                        label: 'Number of Projects Started',
                                        data: chartDataSets[type].data,
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
                                    scales: { y: { beginAtZero: true, ticks: { callback: function(value) { if (Number.isInteger(value)) { return value; } } } } }
                                }
                            });

                            const dropdown = document.getElementById('chartDropdown');
                            if (dropdown && !dropdown.classList.contains('hidden')) {
                                toggleDropdown();
                            }
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            changeChart('yearly');

                            const userStatusCtx = document.getElementById('userStatusChart');
                            if (!userStatusCtx) return;

                            new Chart(userStatusCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Active', 'Blocked'],
                                    datasets: [{
                                        data: {!! json_encode([$userStatus['active'], $userStatus['blocked']]) !!},
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
                        });
                    </script>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>