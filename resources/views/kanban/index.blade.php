<!-- resources/views/kanban/index.blade.php (updated for project-specific routes) -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kanban Board') }} - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="container mx-auto mt-6">
        <!-- Judul Atas Konten (Tidak Bisa Di-scroll) -->
        <h2 class="text-2xl font-bold mb-4 text-center">Kanban Board - {{ $project->name }}</h2>

        <!-- Flex Container untuk Kolom Kanban -->
        <div class="flex flex-row gap-4" style="height: calc(100vh - 12rem);"> <!-- Sesuaikan tinggi dengan browser -->
            @foreach(['To Do' => 'gray', 'In Progress' => 'blue', 'Done' => 'green'] as $status => $color)
                <div x-data="{ isCollapsed: false }" class="flex flex-col bg-{{ $color }}-200 rounded-lg shadow-md transition-all duration-300"
                     :style="isCollapsed ? 'width: 50px' : 'width: 300px'"> <!-- Lebar kolom berubah saat di-collapse -->
                    <!-- Header Kolom Kanban (Tampil saat di-collapse) -->
                    <div x-show="isCollapsed" class="flex flex-col items-center h-full">
                        <!-- Tombol Uncollapse -->
                        <button @click="isCollapsed = false" class="p-2 rounded hover:bg-{{ $color }}-300 transition-colors duration-200">
                            <i data-lucide="chevron-right" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openDropdown }"></i>
                        </button>

                        <!-- Nama Kolom (Diputar 90 derajat) -->
                        <h3 class="font-bold text-{{ $color }}-800 whitespace-nowrap transform rotate-90 mt-4">
                            {{ $status }}
                        </h3>
                    </div>

                    <!-- Header Kolom Kanban (Tampil saat tidak di-collapse) -->
                    <div x-show="!isCollapsed" class="p-4 flex items-center justify-between">
                        <!-- Nama Kolom -->
                        <h3 class="font-bold text-{{ $color }}-800">
                            {{ $status }}
                        </h3>

                        <div class="flex items-center space-x-2">
                            <!-- Tombol Add Task (Updated route) -->
                            <button @click="window.location.href='{{ route('tasks.create', ['status' => $status, 'project_id' => $project->id]) }}'" 
                                    class="p-1 rounded hover:bg-{{ $color }}-300 transition-colors duration-200">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </button>
                            
                            <!-- Tombol Collapse -->
                            <button @click="isCollapsed = true" class="p-1 rounded hover:bg-{{ $color }}-300 transition-colors duration-200">
                                <i data-lucide="chevron-left" class="ml-auto transition-transform w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openDropdown }"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Isi Kanban (Bisa Di-scroll, Tersembunyi saat di-collapse) -->
                    <div x-show="!isCollapsed" id="{{ strtolower(str_replace(' ', '-', $status)) }}" 
                         class="task-list flex-1 p-2 border-2 border-{{ $color }}-500 rounded-lg bg-white shadow-inner overflow-y-auto"
                         data-status="{{ $status }}">
                        @foreach($tasks->where('status', $status)->sortBy('order') as $task)
                            <div x-data="{ showMenu: false }" class="task bg-white p-4 rounded-lg shadow-md border-l-4 border-{{ $color }}-500 mb-3 cursor-grab relative"
                                 data-id="{{ $task->id }}" data-status="{{ $status }}" data-order="{{ $task->order }}">
                                <div class="flex justify-between">
                                    <h4 class="font-bold text-gray-800 text-lg">{{ $task->title }}</h4>
                                    
                                    <!-- Three dot menu -->
                                    <div class="relative">
                                        <button @click="showMenu = !showMenu" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                                        </button>
                                        
                                        <!-- Dropdown menu (Updated routes) -->
                                        <div x-show="showMenu" @click.away="showMenu = false" 
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                                            <a href="{{ route('tasks.edit', $task->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Edit Task
                                            </a>
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="block">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="project_id" value="{{ $project->id }}">
                                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100" 
                                                        onclick="return confirm('Are you sure you want to delete this task?')">
                                                    Delete Task
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task description if available -->
                                @if ($task->description)
                                    <p class="text-gray-600 mt-1">{{ Str::limit($task->description, 100) }}</p>
                                @endif

                                <!-- Indikator tambahan (sementara) -->
                                <div class="flex flex-wrap items-center gap-2 mt-2 text-sm">
                                    @if($task->difficulty_level)
                                        @php
                                            $difficultyColors = [
                                                '1' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'Sangat Ringan'],
                                                '2' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'label' => 'Ringan'],
                                                '3' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'label' => 'Normal'],
                                                '4' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'label' => 'Berat'],
                                                '5' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Sangat Berat'],
                                            ];
                                            $diffClass = isset($difficultyColors[$task->difficulty_level]) ? 
                                                $difficultyColors[$task->difficulty_level] : 
                                                ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Level ' . $task->difficulty_level];
                                        @endphp
                                        <span class="{{ $diffClass['bg'] }} {{ $diffClass['text'] }} px-2 py-1 rounded text-xs font-semibold">
                                            {{ $diffClass['label'] }}
                                        </span>
                                    @endif
                                
                                    @if($task->priority_level)
                                        @php
                                            $priorityColors = [
                                                '1' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'Sangat Rendah'],
                                                '2' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'label' => 'Rendah'],
                                                '3' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'label' => 'Normal'],
                                                '4' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'label' => 'Tinggi'],
                                                '5' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Sangat Tinggi'],
                                            ];
                                            $prioClass = isset($priorityColors[$task->priority_level]) ? 
                                                $priorityColors[$task->priority_level] : 
                                                ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Prioritas ' . $task->priority_level];
                                        @endphp
                                        <span class="{{ $prioClass['bg'] }} {{ $prioClass['text'] }} px-2 py-1 rounded text-xs font-semibold">
                                            {{ $prioClass['label'] }}
                                        </span>
                                    @endif
                                
                                    @if($task->end_time)
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs flex items-center">
                                            ⏰ {{ \Carbon\Carbon::parse($task->end_time)->format('Y/m/d') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Inisial User -->
                                <div class="absolute bottom-2 right-2 w-8 h-8 bg-blue-500 text-white flex items-center justify-center rounded-full text-xs font-bold">
                                    {{ substr($task->assignedUser->name, 0, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Status message -->
    <div id="status-message" class="fixed top-4 right-4 bg-green-500 text-white py-2 px-4 rounded shadow-md transform translate-y-0 opacity-0 transition-all duration-300 hidden">
        Changes saved successfully
    </div>

    <!-- Load Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Load Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <!-- Load SortableJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let lists = document.querySelectorAll('.task-list');
            let updateTimeout;
            const statusMessage = document.getElementById('status-message');
            
            // Show status message
            function showStatusMessage(message, isSuccess = true) {
                statusMessage.textContent = message;
                statusMessage.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
                statusMessage.classList.add(isSuccess ? 'bg-green-500' : 'bg-red-500');
                
                // Animate in
                setTimeout(() => {
                    statusMessage.classList.add('translate-y-4', 'opacity-100');
                }, 10);
                
                // Animate out
                setTimeout(() => {
                    statusMessage.classList.remove('translate-y-4', 'opacity-100');
                    setTimeout(() => {
                        statusMessage.classList.add('hidden');
                    }, 300);
                }, 3000);
            }

            // Build task order for all columns
            function buildOrderData() {
                const orderData = {};
                
                lists.forEach(list => {
                    const status = list.dataset.status;
                    orderData[status] = [];
                    
                    Array.from(list.children).forEach((item, index) => {
                        orderData[status].push({
                            id: item.dataset.id,
                            order: index
                        });
                        
                        // Update the data-order attribute to reflect new position
                        item.dataset.order = index;
                    });
                });
                
                return orderData;
            }
            
            // Send updates to server with debounce
            function updateTaskOrders() {
                // Clear any pending timeout
                if (updateTimeout) {
                    clearTimeout(updateTimeout);
                }
                
                // Set a new timeout to send the update
                updateTimeout = setTimeout(() => {
                    const orderData = buildOrderData();
                    
                    fetch('/tasks/batch-update', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            data: orderData,
                            project_id: {{ $project->id }} 
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showStatusMessage('Tasks updated successfully');
                        } else {
                            showStatusMessage('Error updating tasks', false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showStatusMessage('Error updating tasks', false);
                    });
                }, 500); // Debounce by 500ms
            }

            lists.forEach(list => {
                new Sortable(list, {
                    group: 'shared',
                    animation: 150,
                    ghostClass: 'bg-gray-100',
                    chosenClass: 'bg-gray-200',
                    dragClass: 'shadow-lg',
                    onEnd: function (evt) {
                        updateTaskOrders();
                    }
                });
            });
        });
    </script>
</x-app-layout>