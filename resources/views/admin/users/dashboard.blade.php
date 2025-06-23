<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<x-app-layout>
    <main class="ml-64 p-6">
    <div class="flex">
        {{-- Sidebar --}}
        @include('admin.users.sidebaradmin')
    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
<body class="bg-gray-100 min-h-screen p-6 flex flex-col" style="height: 100vh; overflow: hidden;">

    <!-- Header -->
   <div class="mt-5 mb-5 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-1 text-left">Admin Dashboard</h1>
    <p class="text-gray-600 text-sm">Project and user summary</p>
</div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 max-w-7xl mx-auto flex-shrink-0" style="height: 90px; overflow: hidden;">
        <div class="bg-white rounded shadow p-4 flex justify-between items-start">
            <div class="text-left -translate-y-1">
                <h2 class="text-lg font-semibold text-gray-700">Total Users</h2>
                <p class="text-black text-2xl font-bold -translate-y-1">{{ $totalUsers }}</p>
            </div>
            <div class="bg-gray-300 rounded-[6px] p-2">
                <i class="bi bi-people-fill text-white text-2xl"></i>
            </div>
        </div>
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

    <!-- Charts -->
    <div class="flex flex-1 gap-6 max-w-7xl mx-auto" style="height: 60vh;">

        <!-- User Status Doughnut Chart -->
        <div class="bg-white rounded shadow p-6 w-1/2 flex flex-col justify-center">
            <h3 class="flex justify-between items-center mb-4 border-b pb-2 border-gray-300 font-bold">
            User Status 
          </h3>

            <div class="flex justify-center items-center flex-grow">
                <canvas id="userStatusChart" width="300" height="300"></canvas>
            </div>
        </div>

        <!-- Projects Started Line Chart with Dropdown -->
        <div class="bg-white rounded shadow p-6 w-1/2 flex flex-col border-b-2 border-blue-500">
            <div class="flex justify-between items-center mb-4 border-b pb-2 border-gray-300">
                <h3 class="text-xl font-semibold text-center flex-shrink-0">Projects</h3>
                <div class="relative">
                   <button onclick="toggleDropdown()" 
                        class="text-gray-700 hover:text-black focus:outline-none text-2xl font-bold select-none leading-none" 
                        style="line-height:1;">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul id="chartDropdown" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg hidden z-50">
                        <li><button onclick="changeChart('daily')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Day</button></li>
                        <li><button onclick="changeChart('monthly')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Month</button></li>
                        <li><button onclick="changeChart('yearly')" class="block px-4 py-2 text-sm hover:bg-gray-100 w-full text-left">Year</button></li>
                    </ul>
                </div>
            </div>
            <div class="flex-grow flex justify-center items-center overflow-hidden">
                <canvas id="projectsLineChart" width="600" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart.js & Script -->
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
        const ctx = document.getElementById('projectsLineChart').getContext('2d');
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
                    fill: true,
                    pointBackgroundColor: '#3B82F6',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: { display: true, position: 'bottom' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });

        toggleDropdown();
    }

    window.onload = function () {
        // Default chart: YEARLY
        changeChart('yearly');

        // User Status Doughnut Chart
        new Chart(document.getElementById('userStatusChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Blocked'],
                datasets: [{
                    data: [{{ $userStatus['active'] }}, {{ $userStatus['blocked'] }}],
                    backgroundColor: ['#AEF9A5', '#F50100'],
                    borderColor: ['#71F262', '#C51918'],
                    borderWidth: 2,
                    hoverOffset: 30
                }]
            },
            options: {
                responsive: false,
                cutout: '50%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 14 } }
                    }
                }
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (!lineChart) {
            changeChart('yearly');
        }
    });
</script>

</body>
</main>
</x-app-layout>
</html>
