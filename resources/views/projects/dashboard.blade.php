<x-app-layout>
    {{-- AlpineJS Data Setup untuk Tabs --}}
    <div x-data="{ activeTab: 'tasks' }" class="flex h-full">

        {{-- Bagian Utama Konten (dengan Tabs) --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Dashboard: {{ $project->name }}</h2>
                <p class="text-gray-600">Ringkasan status, progres, dan keuangan proyek</p>
            </div>

            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    {{-- Tombol Tab Tugas --}}
                    <button @click="activeTab = 'tasks'" type="button"
                            :class="activeTab === 'tasks' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                        {{-- Icon Tugas --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 transition-colors duration-150" :class="activeTab === 'tasks' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Ringkasan Tugas
                    </button>
                    {{-- Tombol Tab Keuangan --}}
                    <button @click="activeTab = 'finance'" type="button"
                            :class="activeTab === 'finance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150 flex items-center group">
                        {{-- Icon Keuangan --}}
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 transition-colors duration-150" :class="activeTab === 'finance' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0c-1.657 0-3-.895-3-2s1.343-2 3-2 3-.895 3-2 1.343-2 3-2m0 8c1.11 0 2.08-.402 2.599-1M12 16v1m0-1v-4m0 4H9m3 0h3m-3 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                         </svg>
                        Ringkasan Keuangan
                    </button>
                </nav>
            </div>

            {{-- Konten Tab Tugas --}}
            <div x-show="activeTab === 'tasks'" x-transition.opacity>
                {{-- Grafik Tugas (2 Grafik) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    {{-- Task Status Chart --}}
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
                    {{-- Tasks by Assignee Status Chart --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="font-semibold text-gray-800">Beban Tugas per Anggota (by Status)</h3>
                        </div>
                        <div class="p-6">
                            <div class="h-64">
                                <canvas id="tasksByAssigneeStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Task Statistics Cards (Layout 4 kolom) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-gray-100 text-gray-500">
                             {{-- Icon Todo --}}
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                         </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Todo</h4> <p class="text-xl font-semibold">{{ $taskStats['todo'] }}</p> </div>
                     </div>
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-yellow-100 text-yellow-600">
                            {{-- Icon In Progress --}}
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0Z" /></svg>
                         </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">In Progress</h4> <p class="text-xl font-semibold">{{ $taskStats['in_progress'] }}</p> </div>
                     </div>
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600">
                            {{-- Icon Review --}}
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                         </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Review</h4> <p class="text-xl font-semibold">{{ $taskStats['review'] }}</p> </div>
                     </div>
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-green-100 text-green-600">
                            {{-- Icon Done --}}
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                         </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Selesai</h4> <p class="text-xl font-semibold">{{ $taskStats['done'] }}</p> </div>
                     </div>
                </div>
            </div> {{-- End Task Tab Content --}}

            {{-- Konten Tab Keuangan --}}
            <div x-show="activeTab === 'finance'" x-transition.opacity>
                {{-- Grafik Keuangan (2 Grafik) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    {{-- Financial Overview Chart --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200"> <h3 class="font-semibold text-gray-800">Ringkasan Pembayaran</h3> </div>
                        <div class="p-6"> <div class="h-64"> <canvas id="financialOverviewChart"></canvas> </div> </div>
                    </div>
                    {{-- Spending vs Budget Chart --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200"> <h3 class="font-semibold text-gray-800">Budget vs Pengeluaran</h3> </div>
                        <div class="p-6"> <div class="h-64"> <canvas id="spendingVsBudgetChart"></canvas> </div> </div>
                    </div>
                </div>

                {{-- Financial Statistics Cards (Layout 4 kolom) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                     {{-- Budget --}}
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-gray-100 text-gray-500"> {{-- Icon Budget --}}<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Budget</h4> <p class="text-xl font-semibold">Rp {{ number_format($financialStats['budget'], 0, ',', '.') }}</p> </div>
                     </div>
                     {{-- Total Hak Gaji (Estimasi) --}}
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600"> {{-- Icon Estimasi Gaji --}}<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg> </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Total Estimasi Gaji</h4> <p class="text-xl font-semibold">Rp {{ number_format($financialStats['totalHakGaji'], 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-400">(Task: {{number_format($financialStats['totalTaskHakGaji'], 0, ',', '.')}} + Lain: {{number_format($financialStats['totalOtherFullHakGaji'], 0, ',', '.')}})</p>
                         </div>
                     </div>
                     {{-- Total Dibayar (Approved) --}}
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-green-100 text-green-600"> {{-- Icon Dibayar --}}<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg> </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">Total Dibayar</h4> <p class="text-xl font-semibold">Rp {{ number_format($financialStats['totalPaid'], 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-400">(Task/Term: {{number_format($financialStats['totalPaidTaskTermin'], 0, ',', '.')}} + Lain: {{number_format($financialStats['totalPaidOtherFull'], 0, ',', '.')}})</p>
                         </div>
                     </div>
                     {{-- Sisa / Lebih Budget --}}
                     @php
                         $budgetDiff = $financialStats['budgetDifference']; $budgetColor = $budgetDiff >= 0 ? 'yellow' : 'red'; $budgetLabel = $budgetDiff >= 0 ? 'Sisa' : 'Lebih';
                     @endphp
                     <div class="bg-white rounded-lg shadow p-4 flex items-center">
                         <div class="rounded-full h-10 w-10 flex items-center justify-center bg-{{ $budgetColor }}-100 text-{{ $budgetColor }}-600">
                             {{-- Icon Sisa/Lebih Budget --}}
                             @if($budgetDiff >= 0) <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.153.04c-1.095-.26-1.956-.925-2.413-1.976L12 11.46m4.5-6.49l-2.62 10.726M12 11.46l-2.413 5.042" /></svg>
                             @else <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" /></svg> @endif
                         </div>
                         <div class="ml-3"> <h4 class="text-xs font-medium text-gray-500 uppercase">{{ $budgetLabel }} Budget</h4> <p class="text-xl font-semibold text-{{ $budgetColor }}-600">Rp {{ number_format(abs($budgetDiff), 0, ',', '.') }}</p> <p class="text-[10px] text-gray-400">(Budget vs Estimasi)</p> </div>
                     </div>
                </div>
            </div> {{-- End Finance Tab Content --}}

            {{-- Bagian yang selalu tampil (di bawah tabs) --}}
             <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                 {{-- Recent Activity --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center"> <h3 class="font-semibold text-gray-800">Aktivitas Terakhir</h3> <a href="{{ route('projects.activity', $project) }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a> </div>
                    <div class="p-6 max-h-96 overflow-y-auto">
                        <div class="space-y-4">
                            @forelse($recentActivities as $activity)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-medium"> {{ substr($activity->user->name ?? '?', 0, 1) }} </div>
                                    <div class="ml-3 flex-grow"> <p class="text-sm text-gray-700"> <span class="font-medium">{{ $activity->user->name ?? 'System' }}</span> <span class="text-gray-500">{{ $activity->description }}</span> </p> <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p> </div>
                                </div>
                            @empty <p class="text-gray-500 italic text-sm">Tidak ada aktivitas terbaru.</p> @endforelse
                        </div>
                    </div>
                </div>
                {{-- In Progress Tasks --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center"> <h3 class="font-semibold text-gray-800">Tugas Sedang Dikerjakan</h3> <a href="{{ route('projects.kanban', $project) }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a> </div>
                    <div class="p-6 max-h-96 overflow-y-auto">
                        @if($inProgressTasks->count() > 0)
                            <div class="space-y-4">
                                @foreach($inProgressTasks as $task)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150">
                                        <div class="flex justify-between items-start mb-2"> <h4 class="font-medium text-gray-800 text-sm">{{ $task->title }}</h4> <span class="text-xs text-gray-500">{{ $task->assignedUser->name ?? 'Unassigned' }}</span> </div>
                                        @php $startDateTask = \Carbon\Carbon::parse($task->start_time ?? $task->created_at); $endDateTask = \Carbon\Carbon::parse($task->end_time ?? now()->addDay()); $totalDaysTask = max(1, $startDateTask->diffInDays($endDateTask)); $daysPassedTask = max(0, $startDateTask->diffInDays(now())); $progressTask = min(100, round(($daysPassedTask / $totalDaysTask) * 100)); $isOverdueTask = now()->gt($endDateTask) && $task->status !== 'Done'; @endphp
                                        <div class="mt-2">
                                            <div class="flex justify-between items-center mb-1"> <span class="text-xs {{ $isOverdueTask ? 'text-red-600' : 'text-gray-500' }}"> Due: {{ $endDateTask->format('d M') }} {{ $isOverdueTask ? '(Overdue)' : '' }} </span> <span class="text-xs font-medium text-gray-600">{{ $progressTask }}%</span> </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5"> <div class="{{ $isOverdueTask ? 'bg-red-500' : 'bg-blue-500' }} h-1.5 rounded-full" style="width: {{ $progressTask }}%"></div> </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-8 text-center text-gray-500"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto mb-2 text-gray-400"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> <p class="text-sm">Tidak ada tugas yang sedang dikerjakan.</p> </div>
                        @endif
                    </div>
                </div>
             </div>

        </div> {{-- End Main Content Area --}}

        {{-- Right Sidebar - Informasi Proyek & Tim --}}
        <div class="hidden md:block w-80 lg:w-96 bg-gray-50 border-l border-gray-200 overflow-y-auto flex-shrink-0">
             {{-- Konten Sidebar Kanan (Info Proyek & Tim) --}}
             <div class="p-6 space-y-6">
                 {{-- Informasi Proyek --}}
                 <div>
                     <h3 class="text-base font-semibold mb-3 text-gray-800">Informasi Proyek</h3>
                     {{-- Status --}}
                     @php $statusColorsSidebar = [ 'open' => 'bg-blue-100 text-blue-800', 'in_progress' => 'bg-yellow-100 text-yellow-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800', ]; $statusLabelsSidebar = [ 'open' => 'Terbuka', 'in_progress' => 'Berjalan', 'completed' => 'Selesai', 'cancelled' => 'Batal', ]; @endphp
                     <div class="mb-4"> <span class="block text-xs text-gray-500 mb-1">Status</span> <span class="px-2 py-0.5 {{ $statusColorsSidebar[$project->status] ?? 'bg-gray-100 text-gray-800' }} rounded-full text-xs font-medium"> {{ $statusLabelsSidebar[$project->status] ?? ucfirst($project->status) }} </span> </div>
                     {{-- Timeline --}}
                     <div class="mb-4"> <span class="block text-xs text-gray-500 mb-1">Timeline</span>
                         @php $startDateSidebar = \Carbon\Carbon::parse($project->start_date); $endDateSidebar = \Carbon\Carbon::parse($project->end_date); $totalDaysSidebar = max(1, $startDateSidebar->diffInDays($endDateSidebar)); $daysPassedSidebar = max(0, $startDateSidebar->diffInDays(now())); $progressSidebar = min(100, round(($daysPassedSidebar / $totalDaysSidebar) * 100)); $timeLeftSidebar = now()->diffInDays($endDateSidebar, false); $isOverdueSidebar = $timeLeftSidebar < 0; @endphp
                         <div class="flex justify-between text-[11px] text-gray-400 mb-0.5"> <span>{{ $startDateSidebar->format('d M') }}</span> <span>{{ $endDateSidebar->format('d M') }}</span> </div>
                         <div class="w-full bg-gray-200 rounded-full h-1.5"> <div class="{{ ($isOverdueSidebar && !in_array($project->status, ['completed', 'cancelled'])) ? 'bg-red-500' : 'bg-blue-500' }} h-1.5 rounded-full" style="width: {{ $progressSidebar }}%"></div> </div>
                         <div class="mt-1 text-[11px]"> @if($isOverdueSidebar && !in_array($project->status, ['completed', 'cancelled'])) <span class="text-red-600 font-medium">Terlambat {{ abs($timeLeftSidebar) }} hr</span> @elseif($project->status === 'completed') <span class="text-green-600">Selesai</span> @elseif($project->status === 'cancelled') <span class="text-red-600">Dibatalkan</span> @else <span class="text-gray-500">{{ max(0, $timeLeftSidebar) }} hr tersisa</span> @endif </div>
                     </div>
                     {{-- Deskripsi --}}
                     <div class="mb-4"> <span class="block text-xs text-gray-500 mb-1">Deskripsi</span> <p class="text-sm text-gray-700 leading-relaxed">{{ $project->description ?? 'Tidak ada deskripsi' }}</p> </div>
                     {{-- Kategori --}}
                     <div class="mb-4"> <span class="block text-xs text-gray-500 mb-1">Kategori</span> <div class="flex flex-wrap gap-1"> @forelse($project->categories as $category) <span class="px-1.5 py-0.5 bg-gray-100 rounded text-[11px] text-gray-700">{{ $category->name }}</span> @empty <span class="text-sm text-gray-500 italic">N/A</span> @endforelse </div> </div>
                 </div>
                 {{-- Tim Proyek --}}
                 <div class="border-t border-gray-200 pt-4">
                     <div class="flex justify-between items-center mb-2"> <h3 class="text-base font-semibold text-gray-800">Tim Proyek</h3> <a href="{{ route('projects.team', $project) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Lihat Semua</a> </div>
                     {{-- Owner --}}
                     <div class="mb-3"> <h4 class="text-xs font-medium text-gray-500 mb-1">Project Owner</h4> <div class="flex items-center space-x-2"> <div class="flex-shrink-0 h-7 w-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-medium"> {{ substr($project->owner->name ?? '?', 0, 1) }} </div> <span class="text-sm text-gray-800 truncate">{{ $project->owner->name ?? 'Unknown' }}</span> </div> </div>
                     {{-- Anggota --}}
                     <div> <h4 class="text-xs font-medium text-gray-500 mb-2">Anggota ({{ $acceptedWorkers->count() }})</h4>
                         @if($acceptedWorkers->count() > 0)
                             <div class="space-y-2">
                                 @foreach($acceptedWorkers->take(4) as $worker) <div class="flex items-center space-x-2"> <div class="flex-shrink-0 h-7 w-7 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 text-xs font-medium"> {{ substr($worker->name, 0, 1) }} </div> <div class="flex-1 min-w-0"> <span class="text-sm text-gray-800 truncate block">{{ $worker->name }}</span> <span class="text-[10px] text-gray-500 truncate block">{{ $worker->pivot->position ?? 'Member' }}</span> </div> </div> @endforeach
                                 @if($acceptedWorkers->count() > 4) <p class="text-xs text-gray-500 italic mt-1">+{{ $acceptedWorkers->count() - 4 }} anggota lainnya...</p> @endif
                             </div>
                         @else <p class="text-sm text-gray-500 italic">Belum ada anggota.</p> @endif
                     </div>
                 </div>
             </div>
        </div> {{-- End Sidebar --}}

    </div> {{-- End Flex Container --}}

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                // --- Task Status Chart ---
                const taskStatusCtx = document.getElementById('taskStatusChart')?.getContext('2d');
                if (taskStatusCtx) { const taskData = @json($taskStats); new Chart(taskStatusCtx, { type: 'doughnut', data: { labels: ['Todo', 'In Progress', 'Review', 'Done'], datasets: [{ label: 'Jumlah Tugas', data: [ taskData.todo, taskData.in_progress, taskData.review, taskData.done ], backgroundColor: ['#E5E7EB', '#FCD34D', '#93C5FD', '#6EE7B7'], borderColor: ['#9CA3AF', '#F59E0B', '#3B82F6', '#10B981'], borderWidth: 1, hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } } } } }); }

                // --- Tasks by Assignee Status Chart ---
                const tasksByAssigneeCtx = document.getElementById('tasksByAssigneeStatusChart')?.getContext('2d');
                if (tasksByAssigneeCtx) { const assigneeStatusData = @json($tasksByAssigneeStatusChartData); new Chart(tasksByAssigneeCtx, { type: 'bar', data: { labels: assigneeStatusData.labels, datasets: assigneeStatusData.datasets }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true } }, plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } }, tooltip: { mode: 'index', intersect: false, callbacks: { label: function(context) { let label = context.dataset.label || ''; if (label) { label += ': '; } if (context.parsed.x !== null) { label += context.parsed.x; } return label; } } } } } }); }

                // --- Financial Overview Chart ---
                const financialCtx = document.getElementById('financialOverviewChart')?.getContext('2d');
                if (financialCtx) {
                    const finData = @json($financialStats['overviewChartData']);
                    new Chart(financialCtx, {
                        type: 'doughnut',
                        data: {
                            labels: [ 'Task/Termin Dibayar', 'Lainnya Dibayar', 'Estimasi Belum Dibayar' ],
                            datasets: [{
                                label: 'Jumlah (Rp)',
                                data: [ finData.paidTaskTermin, finData.paidOtherFull, finData.remainingUnpaid ],
                                backgroundColor: ['#6EE7B7', '#A7F3D0', '#FCD34D'],
                                borderColor: ['#10B981', '#34D399', '#F59E0B'],
                                borderWidth: 1,
                                hoverOffset: 4
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } }, tooltip: { callbacks: { label: (context) => ` ${context.label}: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed)}` } } } }
                    });
                }

                // --- Spending vs Budget Chart ---
                const spendingCtx = document.getElementById('spendingVsBudgetChart')?.getContext('2d');
                if (spendingCtx) {
                    const spendingData = @json($financialStats['spendingVsBudgetChartData']);
                    new Chart(spendingCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Status Keuangan'],
                            datasets: [
                                { label: 'Budget', data: [spendingData.budget], backgroundColor: '#BFDBFE', borderColor: '#60A5FA', borderWidth: 1 },
                                { label: 'Estimasi Gaji', data: [spendingData.hakGaji], backgroundColor: '#FEF3C7', borderColor: '#FBBF24', borderWidth: 1 },
                                { label: 'Sudah Dibayar (Approved)', data: [spendingData.paid], backgroundColor: '#A7F3D0', borderColor: '#34D399', borderWidth: 1 }
                            ]
                        },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', notation: 'compact' }).format(value) } } }, plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12 } }, tooltip: { callbacks: { label: (context) => ` ${context.dataset.label}: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.raw)}` } } } }
                    });
                }

            });
        </script>
    @endpush
</x-app-layout>