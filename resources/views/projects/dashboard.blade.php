<x-app-layout>
    <div class="flex h-full">
        <!-- Bagian Utama - Chart dan Task yang Sedang Berjalan -->
        <!-- Bagian Utama - Chart dan Task yang Sedang Berjalan -->
        <div class="flex-1 overflow-y-auto p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Dashboard: {{ $project->name }}</h2>
                <p class="text-gray-600">Ringkasan status dan progres proyek</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Task Status Chart -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Status Tugas</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="taskStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Weekly Progress Chart -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Progres Mingguan</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="weeklyProgressChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full h-12 w-12 flex items-center justify-center bg-gray-100 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-500">Todo</h4>
                        <p class="text-2xl font-semibold">{{ $taskStats['todo'] }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full h-12 w-12 flex items-center justify-center bg-yellow-100 text-yellow-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-500">In Progress</h4>
                        <p class="text-2xl font-semibold">{{ $taskStats['in_progress'] }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full h-12 w-12 flex items-center justify-center bg-blue-100 text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-500">Review</h4>
                        <p class="text-2xl font-semibold">{{ $taskStats['review'] }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full h-12 w-12 flex items-center justify-center bg-green-100 text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-500">Selesai</h4>
                        <p class="text-2xl font-semibold">{{ $taskStats['done'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Aktivitas Terakhir</h3>
                    <a href="{{ route('projects.activity', $project) }}"
                        class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($recentActivities as $activity)
                            <div class="flex items-start">
                                <div
                                    class="h-8 w-8 bg-{{ $activity->user_id == $project->owner_id ? 'blue' : 'gray' }}-100 rounded-full flex items-center justify-center text-{{ $activity->user_id == $project->owner_id ? 'blue' : 'gray' }}-700 mt-1">
                                    {{ substr($activity->user->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm">
                                        <span class="font-medium">{{ $activity->user->name }}</span>
                                        <span class="font-medium text-gray-400">{{ $activity->description }}</span>
                                        <span class="text-gray-600">{{ $activity->action }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 italic">Tidak ada aktivitas terbaru.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- In Progress Tasks -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Tugas Sedang Dikerjakan</h3>
                    <a href="{{ route('projects.kanban', $project) }}"
                        class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a>
                </div>
                <div class="p-6">
                    @if($inProgressTasks->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($inProgressTasks as $task)
                                                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                                                <div class="flex justify-between items-start">
                                                                    <div>
                                                                        <h4 class="font-medium">{{ $task->title }}</h4>
                                                                        <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ $task->description }}</p>
                                                                    </div>
                                                                    <span
                                                                        class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full whitespace-nowrap">In
                                                                        Progress</span>
                                                                </div>

                                                                <div class="mt-4 flex flex-wrap gap-y-2">
                                                                    <div class="w-1/2 flex items-center">
                                                                        <div
                                                                            class="h-6 w-6 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 text-xs">
                                                                            {{ substr($task->assignedUser->name ?? 'U', 0, 1) }}
                                                                        </div>
                                                                        <span
                                                                            class="ml-2 text-sm">{{ $task->assignedUser->name ?? 'Belum ditugaskan' }}</span>
                                                                    </div>

                                                                    <div class="w-1/2 flex items-center justify-end">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                                                                        </svg>
                                                                        <span
                                                                            class="ml-1 text-sm">{{ \Carbon\Carbon::parse($task->end_time)->format('d M Y') }}</span>
                                                                    </div>

                                                                    <div class="w-1/2 flex items-center">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                                                        </svg>
                                                                        <span class="ml-1 text-sm">Prioritas:
                                                                            {{ ucfirst($task->priority_level ?? 'Normal') }}</span>
                                                                    </div>

                                                                    <div class="w-1/2 flex items-center justify-end">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                                                        </svg>
                                                                        <span class="ml-1 text-sm">Kesulitan:
                                                                            {{ ucfirst($task->difficulty_level ?? 'Medium') }}</span>
                                                                    </div>
                                                                </div>

                                                                <!-- Progress Bar -->
                                                                @php
                                                                    $daysPassed = \Carbon\Carbon::parse($task->start_time)->diffInDays(now());
                                                                    $totalDays = \Carbon\Carbon::parse($task->start_time)->diffInDays(\Carbon\Carbon::parse($task->end_time));
                                                                    $progress = $totalDays > 0 ? min(100, round(($daysPassed / $totalDays) * 100)) : 0;
                                                                @endphp
                                                                <div class="mt-3">
                                                                    <div class="flex justify-between items-center mb-1">
                                                                        <span class="text-xs text-gray-500">Progress</span>
                                                                        <span class="text-xs font-medium">{{ $progress }}%</span>
                                                                    </div>
                                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                        @endforeach
                                    </div>
                    @else
                        <div class="py-8 text-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            </svg>
                            <p>Tidak ada tugas yang sedang dikerjakan saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Informasi Proyek -->
        <div class="hidden md:block w-80 lg:w-96 bg-gray-50 border-l border-gray-200 overflow-y-auto">
            <div class="p-6 space-y-6">
                <!-- Project Info Section -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Informasi Proyek</h3>

                    <!-- Status -->
                    @php
                        $statusColors = [
                            'open' => 'bg-blue-100 text-blue-800',
                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                        ];
                        $statusLabels = [
                            'open' => 'Terbuka',
                            'in_progress' => 'Sedang Berjalan',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ];
                    @endphp
                    <div class="mb-4">
                        <span
                            class="px-2 py-1 {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800' }} rounded-full text-xs">
                            {{ $statusLabels[$project->status] ?? ucfirst($project->status) }}
                        </span>
                    </div>

                    <!-- Timeline -->
                    <div class="mb-4">
                        @php
                            $startDate = \Carbon\Carbon::parse($project->start_date);
                            $endDate = \Carbon\Carbon::parse($project->end_date);
                            $totalDays = $startDate->diffInDays($endDate);
                            $daysPassed = $startDate->diffInDays(now());
                            $progress = $totalDays > 0 ? min(100, round(($daysPassed / $totalDays) * 100)) : 0;

                            // Calculate time left
                            $timeLeft = now()->diffInDays($endDate, false);
                            $isOverdue = $timeLeft < 0;
                        @endphp

                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>{{ $startDate->format('d M Y') }}</span>
                            <span>{{ $endDate->format('d M Y') }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $isOverdue ? 'bg-red-600' : 'bg-blue-600' }} h-2 rounded-full"
                                style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="mt-1 text-xs">
                            @if($isOverdue)
                                <span class="text-red-600 font-medium">Terlambat {{ abs($timeLeft) }} hari</span>
                            @else
                                <span class="text-gray-600">{{ $timeLeft }} hari tersisa</span>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Deskripsi</h4>
                        <p class="text-sm">{{ $project->description ?? 'Tidak ada deskripsi' }}</p>
                    </div>

                    <!-- Budget -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Anggaran</h4>
                        <p class="text-sm font-semibold">Rp {{ number_format($project->budget, 0, ',', '.') }}</p>
                    </div>

                    <!-- Categories -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Kategori</h4>
                        <div class="flex flex-wrap gap-2">
                            @forelse($project->categories as $category)
                                <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">{{ $category->name }}</span>
                            @empty
                                <span class="text-sm text-gray-500">Tidak ada kategori</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Team Section -->
                <div>
                    <div class="flex justify-between items-center">
                        <h3   h3 class="text-lg font-semibold mb-4">Tim Proyek</h3>
                        <a href="{{ route('projects.team', $project) }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a>
                    </div>
    
                    <!-- Owner -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Project Owner</h4>
                        <div class="flex items-center">
                            <div
                                class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-700">
                                {{ substr($project->owner->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">{{ $project->owner->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $project->owner->email ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Anggota Tim ({{ $acceptedWorkers->count() }})</h4>
                        @if($acceptedWorkers->count() > 0)
                            <div class="space-y-3">
                                @foreach($acceptedWorkers as $worker)
                                    <div class="flex items-center">
                                        <div
                                            class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-700">
                                            {{ substr($worker->name, 0, 1) }}
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="flex justify-between">
                                                <p class="text-sm font-medium">{{ $worker->name }}</p>
                                                <span class="text-xs bg-gray-100 px-2 py-1 rounded-full">
                                                    {{ $worker->pivot->position ?? 'Member' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500">{{ $worker->email }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-3 text-center text-gray-500 bg-gray-50 rounded-lg">
                                <p class="text-sm">Belum ada anggota tim.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Task Status Chart
                const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
                const taskStatusChart = new Chart(taskStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Todo', 'Sedang Dikerjakan', 'Review', 'Selesai'],
                        datasets: [{
                            data: [
                                {{ $taskStats['todo'] }},
                                {{ $taskStats['in_progress'] }},
                                {{ $taskStats['review'] }},
                                {{ $taskStats['done'] }}
                            ],
                            backgroundColor: [
                                '#E5E7EB', // Light gray
                                '#FEF3C7', // Light yellow
                                '#DBEAFE', // Light blue
                                '#D1FAE5', // Light green
                            ],
                            borderColor: [
                                '#9CA3AF', // Gray
                                '#F59E0B', // Yellow
                                '#3B82F6', // Blue
                                '#10B981', // Green
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });

                // Weekly Progress Chart - Contoh data, seharusnya dari database
                const labels = [];
                const completedData = [];

                // Mendapatkan 7 hari terakhir untuk label
                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('id-ID', { weekday: 'short' }));
                    // Random data untuk contoh
                    completedData.push(Math.floor(Math.random() * 5));
                }

                const weeklyProgressCtx = document.getElementById('weeklyProgressChart').getContext('2d');
                const weeklyProgressChart = new Chart(weeklyProgressCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Tugas Selesai',
                            data: completedData,
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>