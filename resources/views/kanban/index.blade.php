<!-- resources/views/kanban/index.blade.php (updated without Lucide) -->
<x-app-layout>
    <!-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kanban Board') }} - {{ $project->name }}
        </h2>
    </x-slot> -->
    <!-- Di dalam x-app-layout, setelah task creation modal -->
@include('tasks.partials.edit_modal', ['users' => $users, 'project' => $project])
    <div x-data="{ createTaskModal: false, currentStatus: '' }" 
         x-on:open-create-task-modal.window="createTaskModal = true; currentStatus = $event.detail.status">
        <div x-show="createTaskModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 text-center md:items-center sm:block sm:p-0">
                <!-- Overlay -->
                <div x-show="createTaskModal" 
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-40" 
                     aria-hidden="true"
                     @click="createTaskModal = false">
                </div>

                <!-- Modal Content -->
                <div x-show="createTaskModal"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-full max-w-2xl p-8 my-20 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl 2xl:max-w-3xl">
                    
                    <div class="flex items-center justify-between space-x-4">
                        <h1 class="text-xl font-medium text-gray-800">Create New Task</h1>
                        <button @click="createTaskModal = false" class="text-gray-600 focus:outline-none hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Task Creation Form -->
                    <form id="createTaskForm" method="POST" action="{{ route('tasks.store') }}" x-ref="createTaskForm">
                        @csrf
                        <input type="hidden" name="status" x-bind:value="currentStatus">
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <!-- Task Title -->
                            <div class="mb-4 col-span-2">
                                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">
                                    Task Name:
                                </label>
                                <input type="text" name="title" id="title" required
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            
                            <!-- Task Description -->
                            <div class="mb-4 col-span-2">
                                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                                    Description:
                                </label>
                                <textarea name="description" id="description" rows="4"
                                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                            </div>
                            
                            <!-- Difficulty Level -->
                            <div class="mb-4">
                                <label for="difficulty_level" class="block text-gray-700 text-sm font-bold mb-2">
                                    Difficulty Level:
                                </label>
                                <select name="difficulty_level" id="difficulty_level" required
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">Select Level</option>
                                    <option value="1">1 - Very Easy</option>
                                    <option value="2">2 - Easy</option>
                                    <option value="3">3 - Normal</option>
                                    <option value="4">4 - Hard</option>
                                    <option value="5">5 - Very Hard</option>
                                </select>
                            </div>
                            
                            <!-- Priority Level -->
                            <div class="mb-4">
                                <label for="priority_level" class="block text-gray-700 text-sm font-bold mb-2">
                                    Priority:
                                </label>
                                <select name="priority_level" id="priority_level" required
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">Select Priority</option>
                                    <option value="1">1 - Very Low</option>
                                    <option value="2">2 - Low</option>
                                    <option value="3">3 - Normal</option>
                                    <option value="4">4 - High</option>
                                    <option value="5">5 - Very High</option>
                                </select>
                            </div>
                            
                            <!-- Start Time -->
                            <div class="mb-4">
                                <label for="start_time" class="block text-gray-700 text-sm font-bold mb-2">
                                    Start Date:
                                </label>
                                <input type="date" name="start_time" id="start_time" required
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            
                            <!-- End Time -->
                            <div class="mb-4">
                                <label for="end_time" class="block text-gray-700 text-sm font-bold mb-2">
                                    End Date:
                                </label>
                                <input type="date" name="end_time" id="end_time" required
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            
                            <!-- Assigned User -->
                            <div class="mb-4 col-span-2">
                                <label for="assigned_to" class="block text-gray-700 text-sm font-bold mb-2">
                                    Assign User:
                                </label>
                                <select name="assigned_to" id="assigned_to" required
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ auth()->id() == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-6">
                            <button @click="createTaskModal = false" type="submit" 
                                    class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto">
        <!-- Updated Search and Filter section -->
<div class="container mx-auto">
    <div class="flex justify-end items-center">
        <div x-data="{ showFilters: false }" class="relative">
            <!-- Search and Filter Toggle -->
            <div class="flex items-center gap-2">
                <div class="w-64">
                    <div class="relative">
                        <input type="text" id="taskSearch" placeholder="Search tasks..." 
                               class="w-full px-3 py-1.5 pr-8 rounded-lg border border-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <button @click="showFilters = !showFilters" 
                        class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'" class="text-sm"></span>
                </button>
            </div>
            
            <!-- Floating Filter Options -->
            <div x-show="showFilters" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-4"
                 class="absolute right-0 mt-2 p-4 bg-white shadow-lg rounded-lg border border-gray-200 z-10 w-80">
                
                <div class="grid grid-cols-1 gap-3">
                    <!-- Assigned User Filter -->
                    <div>
                        <label for="userFilter" class="block text-xs font-medium text-gray-700 mb-1">Assigned To</label>
                        <select id="userFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Date Range Filter -->
                        <div>
                            <label for="startDateFilter" class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" id="startDateFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="endDateFilter" class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" id="endDateFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Difficulty Level Filter -->
                        <div>
                            <label for="difficultyFilter" class="block text-xs font-medium text-gray-700 mb-1">Difficulty</label>
                            <select id="difficultyFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="">All Levels</option>
                                <option value="1">Very Easy</option>
                                <option value="2">Easy</option>
                                <option value="3">Normal</option>
                                <option value="4">Hard</option>
                                <option value="5">Very Hard</option>
                            </select>
                        </div>
                        
                        <!-- Priority Level Filter -->
                        <div>
                            <label for="priorityFilter" class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                            <select id="priorityFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="">All Priorities</option>
                                <option value="1">Very Low</option>
                                <option value="2">Low</option>
                                <option value="3">Normal</option>
                                <option value="4">High</option>
                                <option value="5">Very High</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label for="statusFilter" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select id="statusFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="To Do">To Do</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                    
                    <!-- Clear Filters Button -->
                    <div class="flex mt-1">
                        <button id="clearFilters" class="w-full px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Active Indicators -->
    <div id="activeFilters" class="flex flex-wrap gap-1 mt-2 justify-end">
        <!-- Active filters will be added here dynamically -->
    </div>
</div>
        <!-- Scrollable Container for Kanban Columns -->
        <div class="overflow-x-auto pb-4 p-4" style="max-width: 100%;">

            <!-- Fixed Width Flex Container for Kanban Columns -->
            <div class="flex flex-row gap-4" style="height: calc(100vh - 12rem); min-width: max-content;">
                @foreach(['To Do' => '#ef4444', 'In Progress' => '#ffd96b', 'Done' => '#10b981'] as $status => $color)
                    <div x-data="{ isCollapsed: false }" class="flex flex-col bg-grey-200 shadow-md transition-all duration-300":style="isCollapsed ? 'width: 50px' : 'width: 340px'"> <!-- Fixed width regardless of viewport -->
                        <!-- Header Kolom Kanban (Tampil saat di-collapse) -->
                        <div x-show="isCollapsed" class="flex flex-col items-center h-full">
                            <!-- Tombol Uncollapse -->
                            <button @click="isCollapsed = false" class="p-2 rounded hover:bg-{{ $color }}-300 transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>

                            <!-- Nama Kolom (Diputar 90 derajat) -->
                            <h3 class="font-bold text-grey-800 whitespace-nowrap transform rotate-90 mt-4">
                                {{ $status }}
                            </h3>
                        </div>

                        <!-- Header Kolom Kanban (Tampil saat tidak di-collapse) -->
                        <div x-show="!isCollapsed" class="py-2 px-4 flex items-center justify-between">
                            <!-- Nama Kolom -->
                            <div class="flex items-center">
                                <!-- Lingkaran Warna -->
                                <div class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $color }};"></div>
                                <h3 class="font-bold text-{{ $color }}-800">
                                    {{ $status }}
                                </h3>
                            </div>

                            <div class="flex items-center space-x-2">
                                <!-- Tombol Add Task (Updated route) -->
                                <button 
            @click="$dispatch('open-create-task-modal', { status: '{{ $status }}' })"
            class="p-1 rounded hover:bg-{{ $color }}-300 transition-colors duration-200 group"
        >
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="w-5 h-5 text-gray-600 group-hover:text-gray-800" 
                 fill="none" 
                 viewBox="0 0 24 24" 
                 stroke="currentColor"
            >
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>
                                
                                <!-- Tombol Collapse -->
                                <button @click="isCollapsed = true" class="p-1 rounded hover:bg-{{ $color }}-300 transition-colors duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto w-5 h-5 group-hover:text-[#5F65DB]" :class="{ 'rotate-90': openDropdown }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Isi Kanban (Bisa Di-scroll, Tersembunyi saat di-collapse) -->
                        <div x-show="!isCollapsed" id="{{ strtolower(str_replace(' ', '-', $status)) }}" 
                             class="task-list flex-1 p-2 border-2 border-{{ $color }}-500 bg-white shadow-inner overflow-y-auto"
                             data-status="{{ $status }}">
                             @foreach($tasks->where('status', $status)->sortBy('order') as $task)
    @php
        $colors = [
            'To Do' => '#ef4444',
            'In Progress' => '#ffd96b',
            'Done' => '#10b981'
        ];
        $color = $colors[$status] ?? '#6b7280';
    @endphp
    @include('tasks.partials.task_card', [
        'task' => $task, 
        'color' => $color
    ])
@endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Status message -->
    <div id="status-message" class="fixed top-4 right-4 bg-green-500 text-white py-2 px-4 rounded shadow-md transform translate-y-0 opacity-0 transition-all duration-300 hidden">
        Changes saved successfully
    </div>

    <!-- Load Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Load SortableJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let lists = document.querySelectorAll('.task-list');
            let updateTimeout;
            const statusMessage = document.getElementById('status-message');
            const createTaskForm = document.getElementById('createTaskForm');

            createTaskForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(createTaskForm);
    
    fetch('{{ route('tasks.store') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reset form
            createTaskForm.reset();
            
            // Find the correct column to append the task
            const statusColumn = document.querySelector(`[data-status="${data.task.status}"]`);
            

            if (statusColumn) {
                // Parse the HTML and insert it into the column
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.taskHtml.trim();
                const newTaskElement = tempDiv.firstChild;
                
                // Append the new task to the column
                statusColumn.appendChild(newTaskElement);
                
                // Close the modal
                window.dispatchEvent(new CustomEvent('alpine:init'));
                Alpine.store('createTaskModal', false);
            }
            
        } else {
            alert('Failed to create task');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the task');
    });
});

            // Modify the existing Add Task buttons to trigger the modal
            document.querySelectorAll('[data-status]').forEach(column => {
            const status = column.dataset.status;
            const addTaskButton = column.closest('[x-data]').querySelector('button[type="button"]');

            if (addTaskButton) {
                addTaskButton.addEventListener('click', () => {
                    window.dispatchEvent(new CustomEvent('open-create-task-modal', { 
                        detail: { status: status } 
                    }));
                });
            }
            });
            
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
            const observer = new MutationObserver((mutationsList, observer) => {
    mutationsList.forEach(mutation => {
        if (mutation.type === 'childList') {
            // Pasang ulang event listener untuk edit task
            document.querySelectorAll('.edit-task-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const taskId = this.dataset.taskId;
                    const taskData = JSON.parse(this.dataset.taskData);
                    window.dispatchEvent(new CustomEvent('open-edit-task-modal', { 
                        detail: { task: taskData } 
                    }));
                });
            });
        }
    });
});

observer.observe(document.body, { childList: true, subtree: true });
        });
        // Add this to your JavaScript section
document.addEventListener('DOMContentLoaded', function() {
    // Set up a delegated event listener for delete forms
    document.body.addEventListener('click', function(e) {
        // Find if we clicked a delete button
        const deleteButton = e.target.closest('.delete-task-btn');
        
        if (deleteButton) {
            e.preventDefault();
            
            // Confirm deletion
            if (confirm('Are you sure you want to delete this task?')) {
                const form = deleteButton.closest('form');
                const taskId = form.getAttribute('data-task-id');
                const projectId = form.querySelector('input[name="project_id"]').value;
                
                // Send AJAX request to delete the task
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE'
                    },
                    body: JSON.stringify({ 
                        project_id: projectId,
                        _method: 'DELETE'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Find and remove the task card
                        const taskCard = document.querySelector(`.task[data-id="${taskId}"]`);
                        if (taskCard) {
                            taskCard.remove();
                            
                            // Show success message
                            showStatusMessage('Task deleted successfully');
                            
                            // Update task orders
                            updateTaskOrders();
                        }
                    } else {
                        showStatusMessage('Error deleting task', false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showStatusMessage('Error deleting task', false);
                });
            }
        }
    });
});
// Add this to your existing JavaScript section or as a new script
document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const taskSearch = document.getElementById('taskSearch');
    const userFilter = document.getElementById('userFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const activeFiltersContainer = document.getElementById('activeFilters');
    
    // Store all tasks for filtering
    let allTasks;
    
    // Initialize by capturing all tasks
    function initializeFiltering() {
        // Get all task elements from the DOM
        allTasks = document.querySelectorAll('.task');
    }
    
    // Initialize
    initializeFiltering();
    
    // Event listeners for filter inputs
    taskSearch.addEventListener('input', applyFilters);
    userFilter.addEventListener('change', applyFilters);
    startDateFilter.addEventListener('change', applyFilters);
    endDateFilter.addEventListener('change', applyFilters);
    difficultyFilter.addEventListener('change', applyFilters);
    priorityFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    clearFiltersBtn.addEventListener('click', clearFilters);
    
    // Apply all filters
    function applyFilters() {
        // Get filter values
        const searchText = taskSearch.value.toLowerCase();
        const userId = userFilter.value;
        const startDate = startDateFilter.value ? new Date(startDateFilter.value) : null;
        const endDate = endDateFilter.value ? new Date(endDateFilter.value) : null;
        const difficulty = difficultyFilter.value;
        const priority = priorityFilter.value;
        const status = statusFilter.value;
        
        // Update active filters display
        updateActiveFilters();
        
        // Apply filters to each task
        allTasks.forEach(task => {
            const taskTitle = task.querySelector('h4').textContent.toLowerCase();
            const taskDesc = task.querySelector('p') ? task.querySelector('p').textContent.toLowerCase() : '';
            const taskUserId = task.querySelector('.edit-task-link').dataset.taskData;
            let assignedTo = '';
            
            // Parse JSON data from the dataset
            try {
                const taskData = JSON.parse(taskUserId);
                assignedTo = taskData.assigned_to.toString();
            } catch (e) {
                console.error('Error parsing task data:', e);
            }
            
            // Get task dates
            const taskStartDateEl = task.querySelector('.edit-task-link').dataset.taskData;
            const taskEndDateEl = task.querySelector('.edit-task-link').dataset.taskData;
            let taskStartDate = null;
            let taskEndDate = null;
            
            try {
                const taskData = JSON.parse(taskStartDateEl);
                taskStartDate = taskData.start_time ? new Date(taskData.start_time) : null;
                taskEndDate = taskData.end_time ? new Date(taskData.end_time) : null;
            } catch (e) {
                console.error('Error parsing task dates:', e);
            }
            
            // Get difficulty and priority from task data
            let taskDifficulty = '';
            let taskPriority = '';
            let taskStatus = task.dataset.status;
            
            try {
                const taskData = JSON.parse(task.querySelector('.edit-task-link').dataset.taskData);
                taskDifficulty = taskData.difficulty_level.toString();
                taskPriority = taskData.priority_level.toString();
            } catch (e) {
                console.error('Error parsing task difficulty/priority:', e);
            }
            
            // Determine if task should be visible
            const matchesSearch = searchText === '' || 
                                 taskTitle.includes(searchText) || 
                                 taskDesc.includes(searchText);
            
            const matchesUser = userId === '' || assignedTo === userId;
            
            const matchesStartDate = !startDate || 
                                    (taskStartDate && taskStartDate >= startDate);
            
            const matchesEndDate = !endDate || 
                                  (taskEndDate && taskEndDate <= endDate);
            
            const matchesDifficulty = difficulty === '' || 
                                     taskDifficulty === difficulty;
            
            const matchesPriority = priority === '' || 
                                   taskPriority === priority;
            
            const matchesStatus = status === '' || 
                                 taskStatus === status;
            
            // Show/hide task based on all filters
            if (matchesSearch && matchesUser && matchesStartDate && 
                matchesEndDate && matchesDifficulty && matchesPriority && matchesStatus) {
                task.style.display = '';
            } else {
                task.style.display = 'none';
            }
        });
    }
    
    // Update visible active filters
    function updateActiveFilters() {
        // Clear existing active filters
        activeFiltersContainer.innerHTML = '';
        
        // Add filter pills for active filters
        if (userFilter.value) {
            addFilterPill('User: ' + userFilter.options[userFilter.selectedIndex].text, () => {
                userFilter.value = '';
                applyFilters();
            });
        }
        
        if (startDateFilter.value) {
            addFilterPill('From: ' + formatDate(new Date(startDateFilter.value)), () => {
                startDateFilter.value = '';
                applyFilters();
            });
        }
        
        if (endDateFilter.value) {
            addFilterPill('To: ' + formatDate(new Date(endDateFilter.value)), () => {
                endDateFilter.value = '';
                applyFilters();
            });
        }
        
        if (difficultyFilter.value) {
            addFilterPill('Difficulty: ' + difficultyFilter.options[difficultyFilter.selectedIndex].text, () => {
                difficultyFilter.value = '';
                applyFilters();
            });
        }
        
        if (priorityFilter.value) {
            addFilterPill('Priority: ' + priorityFilter.options[priorityFilter.selectedIndex].text, () => {
                priorityFilter.value = '';
                applyFilters();
            });
        }
        
        if (statusFilter.value) {
            addFilterPill('Status: ' + statusFilter.value, () => {
                statusFilter.value = '';
                applyFilters();
            });
        }
        
        if (taskSearch.value) {
            addFilterPill('Search: ' + taskSearch.value, () => {
                taskSearch.value = '';
                applyFilters();
            });
        }
    }
    
    // Helper to add filter pill
    function addFilterPill(text, removeCallback) {
        const pill = document.createElement('div');
        pill.className = 'flex items-center bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm';
        pill.innerHTML = `
            <span>${text}</span>
            <button class="ml-1 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </button>
        `;
        
        pill.querySelector('button').addEventListener('click', removeCallback);
        activeFiltersContainer.appendChild(pill);
    }
    
    // Clear all filters
    function clearFilters() {
        taskSearch.value = '';
        userFilter.value = '';
        startDateFilter.value = '';
        endDateFilter.value = '';
        difficultyFilter.value = '';
        priorityFilter.value = '';
        statusFilter.value = '';
        
        // Show all tasks
        allTasks.forEach(task => {
            task.style.display = '';
        });
        
        // Clear active filters display
        activeFiltersContainer.innerHTML = '';
    }
    
    // Helper to format date as DD MMM YYYY
    function formatDate(date) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }
    
    // Observer to re-capture tasks when new ones are added
    const taskListObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Reinitialize filtering when tasks change
                setTimeout(() => {
                    initializeFiltering();
                    applyFilters();
                }, 100);
            }
        });
    });
    
    // Observe each task list
    document.querySelectorAll('.task-list').forEach(list => {
        taskListObserver.observe(list, { childList: true });
    });
});
    </script>
</x-app-layout>