{{-- resources/views/kanban/index.blade.php --}}
<x-app-layout>
    {{-- =============================================== --}}
    {{-- Include Task Modal (For Add & Edit) --}}
    {{-- Variables must be passed from TaskController@kanban --}}
    {{-- =============================================== --}}
    @include('tasks.partials.task_modal', [
        'project' => $project,
        'users' => $users,
        'difficultyLevels' => $difficultyLevels,
        'priorityLevels' => $priorityLevels
    ])

    {{-- ========================== --}}
    {{-- Kanban Board Layout --}}
    {{-- ========================== --}}
    {{-- Main container: flex column, viewport height minus navbar, light background --}}
    <div class="container mx-auto flex flex-col" style="height: calc(100vh - 4rem);"> {{-- Adjust '4rem' if navbar/header height is different --}}

        {{-- Header: Filter & Search (Fixed Top Area) --}}
        <div class="px-4 pb-1 flex-shrink-0 border-b border-gray-200"> {{-- White header --}}
            <div class="container mx-auto">
                {{-- Search and Filter section --}}
                <div class="flex justify-end items-center mb-3"> {{-- Reduced bottom margin --}}
                    <div x-data="{ showFilters: false }" class="relative">
                        {{-- Search and Filter Toggle --}}
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
                        
                        {{-- Floating Filter Options --}}
                        <div x-show="showFilters" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-4"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-4"
                            @click.away="showFilters = false"
                            class="absolute right-0 mt-2 p-4 bg-white shadow-lg rounded-lg border border-gray-200 z-10 w-80">
                            
                            <div class="grid grid-cols-1 gap-3">
                                {{-- Assigned User Filter --}}
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
                                    {{-- Date Range Filter --}}
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
                                    {{-- Difficulty Level Filter --}}
                                    <div>
                                        <label for="difficultyFilter" class="block text-xs font-medium text-gray-700 mb-1">Difficulty</label>
                                        <select id="difficultyFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="">All Levels</option>
                                            @foreach($difficultyLevels as $level)
                                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Priority Level Filter --}}
                                    <div>
                                        <label for="priorityFilter" class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                                        <select id="priorityFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="">All Priorities</option>
                                            @foreach($priorityLevels as $level)
                                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                {{-- Status Filter --}}
                                <div>
                                    <label for="statusFilter" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                    <select id="statusFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">All Statuses</option>
                                        <option>To Do</option>
                                        <option>In Progress</option>
                                        <option>Review</option>
                                        <option>Done</option>
                                    </select>
                                </div>
                                
                                {{-- Clear Filters Button --}}
                                <div class="flex mt-1">
                                    <button id="clearFilters" class="w-full px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm">
                                        Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Filter Active Indicators --}}
                <div id="activeFilters" class="flex flex-wrap gap-1 mt-2 justify-end">
                    {{-- Active filters will be added here dynamically --}}
                </div>
            </div>
        </div>

        <!-- Scrollable Kanban Columns Container -->
        {{-- flex-1 fills remaining height, overflow-x scroll horizontal, overflow-y hidden (lists will scroll) --}}
        <div class="overflow-x-auto overflow-y-hidden flex-1 pb-4 px-4">
            <div class="flex flex-row gap-4 h-full min-w-max">
                @php
                    $kanbanStatuses = [
                        'To Do'       => ['color' => '#ef4444'], // Red-500
                        'In Progress' => ['color' => '#f59e0b'], // Amber-500
                        'Review'      => ['color' => '#3b82f6'], // Blue-500
                        'Done'        => ['color' => '#10b981']  // Emerald-500
                    ];
                @endphp

                @foreach($kanbanStatuses as $status => $config)
                    <div x-data="{ isCollapsed: false }"
                         class="flex flex-col bg-gray-100 shadow-sm transition-all duration-300 rounded-lg overflow-hidden h-full border border-gray-200"
                         :style="isCollapsed ? 'width: 50px; min-width: 50px;' : 'width: 340px; min-width: 340px;'">

                        {{-- Header Collapse --}}
                        <div x-show="isCollapsed" @click="isCollapsed = false"
                             class="flex flex-col items-center h-full py-2 flex-shrink-0 cursor-pointer hover:bg-gray-200 transition-colors duration-150"
                             title="Expand {{ $status }}">
                            <svg class="w-5 h-5 text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            <h3 class="font-medium text-gray-600 whitespace-nowrap transform -rotate-90 mt-16 text-sm">
                                {{ $status }}
                                <span class="task-count-collapsed text-xs font-normal text-gray-500">({{ $tasks->where('status', $status)->count() }})</span>
                            </h3>
                        </div>

                        {{-- Header Expand --}}
                        <div x-show="!isCollapsed"
                             class="py-2.5 px-3 flex items-center justify-between border-b border-gray-200 bg-white flex-shrink-0">
                             <div class="flex items-center">
                                 <div class="w-2.5 h-2.5 rounded-full mr-2 flex-shrink-0" style="background-color: {{ $config['color'] }};"></div>
                                 <h3 class="font-semibold text-gray-700 text-sm flex items-center">
                                     {{ $status }}
                                     <span class="text-xs font-medium text-gray-500 ml-1.5 bg-gray-100 rounded px-1.5 py-0.5 task-count">
                                         {{-- Count will be updated by JS --}}
                                     </span>
                                 </h3>
                             </div>
                            <div class="flex items-center space-x-1">
                                <button @click="$dispatch('open-task-modal', { status: '{{ $status }}' })"
                                        class="btn-icon" title="Add Task to {{ $status }}">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                </button>
                                <button @click="isCollapsed = true" class="btn-icon" title="Collapse Column">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Task List (Scrollable Area) --}}
                        <div x-show="!isCollapsed" id="{{ strtolower(str_replace(' ', '-', $status)) }}"
                             class="task-list flex-1 p-2 overflow-y-auto" {{-- flex-1 to fill remaining height --}}
                             data-status="{{ $status }}">
                             {{-- Empty Placeholder Template --}}
                             <div class="task-list-empty-template text-center text-xs text-gray-500 py-6 px-3 italic absolute inset-0 pointer-events-none" style="display: none;">
                                 No tasks in {{ $status }}.
                             </div>
                             {{-- Loop Task Cards --}}
                             @foreach($tasks->where('status', $status)->sortBy('order') as $task)
                                 @include('tasks.partials.task_card', ['task' => $task, 'color' => $config['color']])
                             @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Status message --}}
    <div id="status-message" class="fixed bottom-4 right-4 bg-green-600 text-white py-2 px-4 rounded-lg shadow-md transform translate-y-full opacity-0 transition-all duration-300 ease-out z-[60]"></div>

    {{-- Styles --}}
    @push('styles')
    <style>
        /* Filter input styling */
        .filter-input { @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm py-1.5 px-2; }
        .filter-label { @apply block text-xs font-medium text-gray-600 mb-1; }
        /* Icon Button Style */
        .btn-icon { @apply p-1 rounded text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors duration-150 focus:outline-none focus:ring-1 focus:ring-indigo-400; }
        /* Custom scrollbar */
        .task-list::-webkit-scrollbar { width: 6px; height: 6px;}
        .task-list::-webkit-scrollbar-track { background: transparent; }
        .task-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .task-list::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        /* Filter Pill */
        #activeFilters button svg { transition: transform 0.1s ease-in-out; }
        #activeFilters button:hover svg { transform: scale(1.15); }
        /* Style for SortableJS ghost/chosen/drag (optional, adjustable) */
        .sortable-ghost { opacity: 0.4; background-color: #e0e7ff; border: 1px dashed #a5b4fc; }
        .sortable-chosen { /* May not need special styling if @click card should continue to function */ }
        .sortable-drag { /* Style when element is being dragged */ box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transform: scale(1.03); }
        
        /* Difficulty Levels - Brighter Colors */
        .difficulty-easy { @apply bg-green-400 text-white; }
        .difficulty-medium { @apply bg-blue-400 text-white; }
        .difficulty-hard { @apply bg-yellow-400 text-gray-800; }
        .difficulty-expert { @apply bg-orange-400 text-white; }
        .difficulty-extreme { @apply bg-red-500 text-white; }
        
        /* Priority Levels - Brighter Colors */
        .priority-low { @apply bg-gray-400 text-white; }
        .priority-normal { @apply bg-blue-400 text-white; }
        .priority-medium { @apply bg-yellow-400 text-gray-800; }
        .priority-high { @apply bg-orange-400 text-white; }
        .priority-critical { @apply bg-red-500 text-white; }
    </style>
    @endpush

    {{-- JavaScript --}}
    @push('scripts')
    {{-- Load SortableJS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>

    <script>
        // Helper Function Debounce
        function debounce(func, wait, immediate) { 
            var timeout; 
            return function() { 
                var context = this, args = arguments; 
                var later = function() { 
                    timeout = null; 
                    if (!immediate) func.apply(context, args); 
                }; 
                var callNow = immediate && !timeout; 
                clearTimeout(timeout); 
                timeout = setTimeout(later, wait); 
                if (callNow) func.apply(context, args); 
            }; 
        }

        // --- Main Kanban Logic ---
        function kanbanBoard() {
            const statusMessage = document.getElementById('status-message');
            const filterElements = {
                search: document.getElementById('taskSearch'),
                user: document.getElementById('userFilter'),
                start: document.getElementById('startDateFilter'),
                end: document.getElementById('endDateFilter'),
                difficulty: document.getElementById('difficultyFilter'),
                priority: document.getElementById('priorityFilter'),
                status: document.getElementById('statusFilter'),
                clearBtn: document.getElementById('clearFilters'),
                activeContainer: document.getElementById('activeFilters')
            };
            let allTaskElements = []; // Cache task DOM elements

            // --- Status Message ---
            function showStatusMessage(message, isSuccess = true) {
                if (!statusMessage) return;
                statusMessage.textContent = message;
                statusMessage.className = `fixed bottom-4 right-4 py-2 px-4 rounded-lg shadow-lg transform transition-all duration-300 ease-out z-[60] ${isSuccess ? 'bg-green-500' : 'bg-red-500'} text-white text-sm`;
                requestAnimationFrame(() => {
                    statusMessage.classList.remove('translate-y-full', 'opacity-0');
                    statusMessage.classList.add('translate-y-0', 'opacity-100');
                });
                setTimeout(() => {
                    statusMessage.classList.remove('translate-y-0', 'opacity-100');
                    statusMessage.classList.add('translate-y-full', 'opacity-0');
                }, 3500);
            }
            
            window.addEventListener('show-status-message', event => {
                showStatusMessage(event.detail.message, event.detail.success);
            });

            // --- Update Task Counts & Placeholders ---
            function updateTaskCounts() {
                document.querySelectorAll('.task-list').forEach(list => {
                    const status = list.dataset.status;
                    const columnDiv = list.closest('.flex.flex-col');
                    // Count tasks without style display: none
                    const visibleTasks = list.querySelectorAll('.task:not([style*="display: none"])');
                    const totalTasksInList = list.querySelectorAll('.task').length;
                    const count = visibleTasks.length;

                    const countElement = columnDiv?.querySelector('.task-count');
                    if (countElement) { countElement.textContent = `${count}`; }

                    const countCollapsedElement = columnDiv?.querySelector('.task-count-collapsed');
                    if (countCollapsedElement) { countCollapsedElement.textContent = `(${totalTasksInList})`; }

                    // Toggle empty placeholder visibility based on total tasks
                    const emptyPlaceholder = list.querySelector('.task-list-empty-template');
                    if (emptyPlaceholder) {
                        emptyPlaceholder.style.display = totalTasksInList === 0 ? 'flex' : 'none';
                    }
                });
            }

            // --- Task Order/Status Update (Drag & Drop) ---
            function buildOrderData() {
                const orderData = {};
                document.querySelectorAll('.task-list').forEach(list => {
                    const status = list.dataset.status;
                    orderData[status] = Array.from(list.querySelectorAll('.task'))
                        .map((item, index) => {
                            item.dataset.order = index;
                            return { id: item.dataset.id, order: index };
                        });
                });
                return orderData;
            }

            const debouncedUpdateTaskOrders = debounce(function() {
                const orderData = buildOrderData();
                if (Object.keys(orderData).length === 0) return; // Don't send if empty

                fetch('/tasks/batch-update', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify({ data: orderData, project_id: {{ $project->id }} })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error("Batch update failed:", data);
                        showStatusMessage('Error saving task order.', false);
                    }
                    // No success message to avoid clutter
                })
                .catch(error => {
                    console.error('Error updating tasks:', error);
                    showStatusMessage('Network error saving task order.', false);
                });
            }, 750);

            // --- Initialize SortableJS ---
            function initializeSortable() {
                document.querySelectorAll('.task-list').forEach(list => {
                    if (list.sortableInstance) {
                        try { 
                            list.sortableInstance.destroy(); 
                        } catch (e) { 
                            console.warn("Could not destroy previous Sortable instance:", e); 
                        }
                    }
                    
                    list.sortableInstance = new Sortable(list, {
                        group: 'shared',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        draggable: '.task', // Only .task elements can be dragged
                        onStart: function() {
                            // Hide empty placeholders when dragging starts
                            document.querySelectorAll('.task-list-empty-template').forEach(el => {
                                el.style.visibility = 'hidden';
                            });
                        },
                        onEnd: function(evt) {
                            // Show empty placeholders again when drag ends
                            document.querySelectorAll('.task-list-empty-template').forEach(el => {
                                el.style.visibility = 'visible';
                            });
                            
                            const item = evt.item;
                            if (!item || !item.classList.contains('task')) return;

                            const newStatus = evt.to.dataset.status;
                            // Update task's data-status if moved to a different column
                            if (item.dataset.status !== newStatus) {
                                item.dataset.status = newStatus;
                            }
                            // Call server update
                            debouncedUpdateTaskOrders();
                            // Update UI counts & filter (after timeout for stable DOM)
                            setTimeout(() => {
                                updateTaskCounts();
                                initializeFiltering();
                                applyFilters();
                            }, 50);
                        }
                    });
                });
            }

            // --- Handle Success from Modal Form (Add/Edit) ---
            window.addEventListener('task-form-success', event => {
                const { isEdit, task, taskHtml } = event.detail;
                const statusColumn = document.querySelector(`.task-list[data-status='${task.status}']`);

                if (!statusColumn) { 
                    console.error(`Target column "${task.status}" not found.`); 
                    return; 
                }

                let targetCardElement = null;

                if (isEdit) {
                    const existingTaskCard = document.querySelector(`.task[data-id='${task.id}']`);
                    if (existingTaskCard) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = taskHtml.trim();
                        const newTaskCard = tempDiv.firstChild;
                        if (newTaskCard && newTaskCard.nodeType === Node.ELEMENT_NODE) {
                            // Important: Transfer sortable instance if any before replacing node
                            if (existingTaskCard.sortableInstance) {
                                newTaskCard.sortableInstance = existingTaskCard.sortableInstance;
                            }
                            existingTaskCard.parentNode.replaceChild(newTaskCard, existingTaskCard);
                            targetCardElement = newTaskCard;
                        }
                    } else {
                        statusColumn.insertAdjacentHTML('beforeend', taskHtml.trim());
                        targetCardElement = statusColumn.lastElementChild;
                    }
                } else {
                    statusColumn.insertAdjacentHTML('beforeend', taskHtml.trim());
                    targetCardElement = statusColumn.lastElementChild;
                }

                // Attach delete listener
                if (targetCardElement && targetCardElement.matches('.task')) {
                    const deleteButton = targetCardElement.querySelector('.delete-task-btn');
                    if (deleteButton) {
                        deleteButton.removeEventListener('click', handleDeleteClick);
                        deleteButton.removeAttribute('data-listener-attached');
                        attachDeleteListener(deleteButton);
                    }
                }

                // Update UI
                updateTaskCounts();
                initializeFiltering(); // Re-index tasks
                applyFilters();      // Apply filters to include/exclude new/updated task
            });

            // --- Delete Task Listener ---
            function handleDeleteClick(e) {
                e.preventDefault();
                e.stopPropagation(); // Stop card click event

                const button = e.currentTarget;
                const form = button.closest('form');
                if (!form) { 
                    console.error("Delete button's form not found."); 
                    return; 
                }
                
                const taskId = form.dataset.taskId;
                if (!taskId) { 
                    console.error("Form missing data-task-id."); 
                    return; 
                }

                if (confirm('Are you sure you want to delete this task?')) {
                    button.disabled = true;
                    button.style.opacity = '0.5';

                    fetch(form.action, { 
                        method: 'POST', 
                        headers: { 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json'
                        }, 
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                    .then(response => response.json().then(data => ({ ok: response.ok, status: response.status, body: data })))
                    .then(({ ok, status, body }) => {
                        if (ok && body.success) {
                            const taskCard = document.querySelector(`.task[data-id="${taskId}"]`);
                            if (taskCard) {
                                // Remove sortable instance if any before removing element
                                if (taskCard.sortableInstance) {
                                    try { 
                                        taskCard.sortableInstance.destroy(); 
                                    } catch(e){ }
                                }
                                taskCard.remove();
                                showStatusMessage('Task deleted.');
                                // Update UI
                                updateTaskCounts();
                                initializeFiltering();
                                applyFilters();
                            }
                        } else { 
                            showStatusMessage(`Error: ${body.message || 'Server error'}`, false); 
                        }
                    })
                    .catch(error => { 
                        showStatusMessage('Network error.', false); 
                    })
                    .finally(() => {
                        // Make sure button is enabled again
                        if (button) { 
                            button.disabled = false; 
                            button.style.opacity = '1'; 
                        }
                    });
                }
            }

            window.attachDeleteListener = function(button) {
                if (!button || button.dataset.listenerAttached === 'true') return;
                button.addEventListener('click', handleDeleteClick);
                button.dataset.listenerAttached = 'true';
            }

            // Initial attachment for delete buttons
            document.querySelectorAll('.delete-task-btn').forEach(attachDeleteListener);

            // --- Filter Logic ---
            function initializeFiltering() {
                allTaskElements = Array.from(document.querySelectorAll('.task-list .task'));
                
                // Add data attributes with lowercase text content for easier filtering
                allTaskElements.forEach(task => {
                    const titleEl = task.querySelector('h4');
                    const descEl = task.querySelector('p');
                    
                    if (titleEl) {
                        task.dataset.titleLower = titleEl.textContent.toLowerCase();
                    }
                    
                    if (descEl) {
                        task.dataset.descLower = descEl.textContent.toLowerCase();
                    }
                });
            }

            function applyFilters() {
                // Ensure task cache exists
                if (!allTaskElements.length && document.querySelectorAll('.task-list .task').length > 0) {
                    initializeFiltering();
                }
                
                if (!allTaskElements.length) { 
                    updateTaskCounts(); // Ensure placeholders are correct
                    return;
                }

                const filters = {
                    search: filterElements.search.value.toLowerCase().trim(),
                    user: filterElements.user.value,
                    start: filterElements.start.value ? new Date(filterElements.start.value + 'T00:00:00Z') : null,
                    end: filterElements.end.value ? new Date(filterElements.end.value + 'T23:59:59Z') : null,
                    difficulty: filterElements.difficulty.value,
                    priority: filterElements.priority.value,
                    status: filterElements.status.value
                };

                updateActiveFiltersUI(filters);

                allTaskElements.forEach(taskEl => {
                    // Get data from data-* attributes
                    const taskStatus = taskEl.dataset.status;
                    const assignedUserId = taskEl.dataset.assignedUserId || '';
                    const difficultyId = taskEl.dataset.difficultyId || '';
                    const priorityId = taskEl.dataset.priorityId || '';
                    const startDateStr = taskEl.dataset.startDate || ''; // YYYY-MM-DD
                    const endDateStr = taskEl.dataset.endDate || '';     // YYYY-MM-DD
                    const title = taskEl.dataset.titleLower || '';
                    const desc = taskEl.dataset.descLower || '';

                    let matches = true;

                    // Apply each filter check
                    if (filters.search && !(title.includes(filters.search) || desc.includes(filters.search))) {
                        matches = false;
                    }
                    
                    if (matches && filters.status && taskStatus !== filters.status) {
                        matches = false;
                    }
                    
                    if (matches && filters.user && assignedUserId !== filters.user) {
                        matches = false;
                    }
                    
                    if (matches && filters.difficulty && difficultyId !== filters.difficulty) {
                        matches = false;
                    }
                    
                    if (matches && filters.priority && priorityId !== filters.priority) {
                        matches = false;
                    }
                    
                    if (matches && (filters.start || filters.end)) {
                        // Parse date strings as UTC date objects for reliable comparison
                        const taskStart = startDateStr ? new Date(startDateStr + 'T00:00:00Z') : null;
                        const taskEnd = endDateStr ? new Date(endDateStr + 'T23:59:59Z') : null;

                        if (filters.start && (!taskStart || taskStart < filters.start)) {
                            matches = false;
                        }
                        
                        if (matches && filters.end && (!taskEnd || taskEnd > filters.end)) {
                            matches = false;
                        }
                    }

                    // Apply visibility based on filter matches
                    taskEl.style.display = matches ? '' : 'none';
                });

                updateTaskCounts(); // Update counts after visibility changes
            }

            function updateActiveFiltersUI(filters) {
                filterElements.activeContainer.innerHTML = '';
                
                const addPill = (text, clearFn) => {
                    const pill = document.createElement('div');
                    pill.className = 'flex items-center bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-medium'; // Pill style
                    pill.innerHTML = `<span class="mr-1">${text}</span><button type="button" class="ml-0.5 focus:outline-none text-indigo-400 hover:text-indigo-600" title="Remove filter"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></button>`;
                    pill.querySelector('button').addEventListener('click', clearFn);
                    filterElements.activeContainer.appendChild(pill);
                };
                
                // Add filter pills for active filters
                if (filters.search) {
                    addPill(`Search: "${filters.search}"`, () => { 
                        filterElements.search.value = ''; 
                        applyFilters(); 
                    });
                }
                
                if (filters.user) {
                    addPill(`User: ${filterElements.user.options[filterElements.user.selectedIndex].text}`, () => { 
                        filterElements.user.value = ''; 
                        applyFilters(); 
                    });
                }
                
                if (filters.start) {
                    addPill(`Start: ${formatDate(filters.start)}`, () => { 
                        filterElements.start.value = ''; 
                        applyFilters(); 
                    });
                }
                
                if (filters.end) {
                    addPill(`Due: ${formatDate(filters.end)}`, () => { 
                        filterElements.end.value = ''; 
                        applyFilters(); 
                    });
                }
                
                if (filters.difficulty) {
                    addPill(`Difficulty: ${filterElements.difficulty.options[filterElements.difficulty.selectedIndex].text}`, () => { 
                        filterElements.difficulty.value = ''; 
                        applyFilters(); 
                    });
                }
                
                if (filters.priority) {
                    addPill(`Priority: ${filterElements.priority.options[filterElements.priority.selectedIndex].text}`, () => { 
                        filterElements.priority.value = ''; 
                        applyFilters(); 
                    });
                }
                
                if (filters.status) {
                    addPill(`Status: ${filters.status}`, () => { 
                        filterElements.status.value = ''; 
                        applyFilters(); 
                    });
                }
            }
            
            function clearFilters() {
                filterElements.search.value = ''; 
                filterElements.user.value = '';
                filterElements.start.value = '';
                filterElements.end.value = '';
                filterElements.difficulty.value = '';
                filterElements.priority.value = '';
                filterElements.status.value = '';
                applyFilters();
            }
            
            function formatDate(date) {
                if (!date || isNaN(date)) return '';
                const options = { year: 'numeric', month: 'short', day: 'numeric', timeZone: 'UTC' }; // Specify UTC for consistent dates
                return date.toLocaleDateString(undefined, options);
            }

            // Attach filter event listeners
            filterElements.search.addEventListener('input', debounce(applyFilters, 350));
            filterElements.user.addEventListener('change', applyFilters);
            filterElements.start.addEventListener('change', applyFilters);
            filterElements.end.addEventListener('change', applyFilters);
            filterElements.difficulty.addEventListener('change', applyFilters);
            filterElements.priority.addEventListener('change', applyFilters);
            filterElements.status.addEventListener('change', applyFilters);
            filterElements.clearBtn.addEventListener('click', clearFilters);

            // --- Mutation Observer for DOM changes ---
            const taskListObserver = new MutationObserver((mutationsList) => {
                let needsReInitAndFilter = false;
                let needsDeleteReattach = false;
                
                for (const mutation of mutationsList) {
                    if (mutation.type === 'childList') {
                        if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                            needsReInitAndFilter = true;
                            
                            // Check if we need to attach delete listeners
                            for (const node of mutation.addedNodes) {
                                if (node.nodeType === Node.ELEMENT_NODE) {
                                    if (node.classList.contains('task') || node.querySelector('.delete-task-btn')) {
                                        needsDeleteReattach = true;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (needsReInitAndFilter) {
                    setTimeout(() => {
                        initializeFiltering();
                        applyFilters();
                        updateTaskCounts();
                        if (needsDeleteReattach) {
                            document.querySelectorAll('.delete-task-btn:not([data-listener-attached="true"])').forEach(attachDeleteListener);
                        }
                    }, 50);
                }
            });

            // Observe each task list for changes
            document.querySelectorAll('.task-list').forEach(list => {
                taskListObserver.observe(list, { childList: true });
            });

            // --- AJAX Filter Implementation ---
            function ajaxFilter() {
                const filters = {
                    project_id: {{ $project->id }},
                    search: filterElements.search.value,
                    user_id: filterElements.user.value,
                    start_date: filterElements.start.value,
                    end_date: filterElements.end.value,
                    difficulty: filterElements.difficulty.value,
                    priority: filterElements.priority.value,
                    status: filterElements.status.value
                };
                
                // Show loading state
                document.querySelectorAll('.task-list').forEach(list => {
                    list.classList.add('opacity-50');
                });
                
                fetch('/tasks/search', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(filters)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update each column with filtered HTML
                        Object.keys(data.data).forEach(status => {
                            const list = document.querySelector(`.task-list[data-status="${status}"]`);
                            if (list) {
                                // Save the reference to original sortable instance
                                const sortableInstance = list.sortableInstance;
                                
                                // Update HTML content
                                list.innerHTML = data.data[status];
                                
                                // Re-attach sortable instance 
                                list.sortableInstance = sortableInstance;
                                
                                // Re-attach delete event listeners
                                list.querySelectorAll('.delete-task-btn').forEach(attachDeleteListener);
                            }
                        });
                        
                        updateTaskCounts();
                    } else {
                        showStatusMessage('Error loading filtered tasks.', false);
                    }
                })
                .catch(error => {
                    console.error('Error fetching filtered tasks:', error);
                    showStatusMessage('Network error loading filtered tasks.', false);
                })
                .finally(() => {
                    // Remove loading state
                    document.querySelectorAll('.task-list').forEach(list => {
                        list.classList.remove('opacity-50');
                    });
                });
            }
            
            // Use AJAX Filter button (optional feature)
            // document.getElementById('ajaxFilterBtn')?.addEventListener('click', ajaxFilter);

            // --- Initial Setup on Load ---
            initializeFiltering(); // Cache initial tasks
            initializeSortable();  // Initialize drag and drop
            applyFilters();       // Apply any filters from load
            updateTaskCounts();    // Set initial counts and placeholder visibility

        } // --- End kanbanBoard Function ---

        // --- Initialize the Kanban Board ---
        document.addEventListener('DOMContentLoaded', kanbanBoard);
    </script>
    @endpush

</x-app-layout>