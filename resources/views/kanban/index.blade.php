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
                                @can('create', [App\Models\Task::class, $project])
                                <button @click="$dispatch('open-task-modal', { status: '{{ $status }}' })"
                                        class="btn-icon" title="Add Task to {{ $status }}">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                </button>
                                @endcan
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js" defer></script>

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

    // Kanban Application Object
    window.KanbanApp = {
        statusMessageElement: null,
        filterElements: {},
        allTaskElements: [],
        projectId: {{ $project->id }},
        isInitialized: false,
        activeTaskListObserver: null,
        sortableInstances: [],
        _debouncedApplyFilters: null,
        _boundApplyFilters: null,
        _boundClearFilters: null,
        _boundHandleTaskFormSuccess: null,
        _boundHandleShowStatusMessage: null,

        // Status message display
        showStatusMessage: function(message, isSuccess = true) {
            if (!this.statusMessageElement) this.statusMessageElement = document.getElementById('status-message');
            if (!this.statusMessageElement) return;

            this.statusMessageElement.textContent = message;
            this.statusMessageElement.className = `fixed bottom-4 right-4 py-2 px-4 rounded-lg shadow-lg transform transition-all duration-300 ease-out z-[60] ${
                isSuccess ? 'bg-green-500' : 'bg-red-500'
            } text-white text-sm`;

            requestAnimationFrame(() => {
                this.statusMessageElement.classList.remove('translate-y-full', 'opacity-0');
                this.statusMessageElement.classList.add('translate-y-0', 'opacity-100');
            });

            setTimeout(() => {
                this.statusMessageElement.classList.remove('translate-y-0', 'opacity-100');
                this.statusMessageElement.classList.add('translate-y-full', 'opacity-0');
            }, 3500);
        },

        // Update task counts in columns
        updateTaskCounts: function() {
            document.querySelectorAll('.task-list').forEach(list => {
                const status = list.dataset.status;
                const columnDiv = list.closest('.flex.flex-col');
                const visibleTasks = list.querySelectorAll('.task:not([style*="display: none"])');
                const totalTasksInList = list.querySelectorAll('.task').length;
                const count = visibleTasks.length;

                const countElement = columnDiv?.querySelector('.task-count');
                if (countElement) countElement.textContent = `${count}`;

                const countCollapsedElement = columnDiv?.querySelector('.task-count-collapsed');
                if (countCollapsedElement) countCollapsedElement.textContent = `(${totalTasksInList})`;

                const emptyPlaceholder = list.querySelector('.task-list-empty-template');
                if (emptyPlaceholder) {
                    emptyPlaceholder.style.display = totalTasksInList === 0 ? 'flex' : 'none';
                }
            });
        },

        // Build order data for saving
        buildOrderData: function() {
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
        },

        // Debounced task order update
        debouncedUpdateTaskOrders: debounce(function(draggedTaskId = null) { // Terima parameter
    const orderData = this.buildOrderData();
    if (Object.keys(orderData).length === 0) return;

    const payload = {
        data: orderData,
        project_id: this.projectId
    };
    if (draggedTaskId) {
        payload.dragged_task_id = draggedTaskId; // Tambahkan ke payload
    }

    fetch('/tasks/batch-update', {
        method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                
        body: JSON.stringify(payload) // Kirim payload baru
    })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error("Batch update failed:", data);
                    this.showStatusMessage('Error saving task order.', false);
                }
            })
            .catch(error => {
                console.error('Error updating tasks:', error);
                this.showStatusMessage('Network error saving task order.', false);
            });
        }, 750),

        // Initialize SortableJS
        initializeSortable: function() {
    if (typeof Sortable === 'undefined') {
        console.warn('Sortable.js is not loaded yet. Retrying in 200ms.');
        setTimeout(() => this.initializeSortable(), 200);
        return;
    }

    console.log('KanbanApp: Initializing SortableJS...');

    this.sortableInstances.forEach(instance => {
        try { instance.destroy(); } catch(e) {}
    });
    this.sortableInstances = [];

    document.querySelectorAll('.task-list').forEach(list => {
        if (list.sortableInstance) {
            try { list.sortableInstance.destroy(); } catch(e) {}
        }

        const sortable = new Sortable(list, {
            group: 'shared',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            draggable: '.task',

            filter: function (/**Event*/evt, /**HTMLElement*/target) {
                if (target && target.classList.contains('task')) {
                    const canMove = target.dataset.canMove === 'true';
                    if (!canMove) {
                        console.log(`Task ${target.dataset.id} cannot be moved by this user.`);
                        target.style.cursor = 'not-allowed';
                        setTimeout(() => { target.style.cursor = 'grab'; }, 1000);
                        return true; // Filter out (cegah drag)
                    }
                }
                return false; // Allow drag
            },

            onStart: (evt) => {
                const item = evt.item;
                if (item.dataset.canMove !== 'true') {
                    console.warn("Drag started on a non-movable item, filter might have an issue.");
                    // Mencoba membatalkan drag jika filter tidak bekerja dengan benar,
                    // ini mungkin tidak selalu efektif tergantung implementasi SortableJS.
                    // Paling aman adalah mengandalkan filter dan otorisasi backend.
                    return false;
                }

                document.querySelectorAll('.task-list-empty-template').forEach(el => {
                    el.style.visibility = 'hidden';
                });
            },

            // --- INI BAGIAN YANG ANDA MINTA ---
            onEnd: (evt) => {
                console.log('[KanbanApp onEnd] Drag ended. Event details:', evt);

                document.querySelectorAll('.task-list-empty-template').forEach(el => {
                    el.style.visibility = 'visible';
                });

                const item = evt.item; // Ini adalah elemen task yang di-drag
                if (!item || !item.classList.contains('task')) {
                    console.warn('[KanbanApp onEnd] Dragged item is not a task element. Aborting.');
                    return;
                }

                const draggedTaskId = item.dataset.id; // Dapatkan ID task yang di-drag

                // Validasi tambahan: Pastikan item ini memang boleh dipindahkan
                // Meskipun sudah ada 'filter', ini sebagai double check.
                if (item.dataset.canMove !== 'true') {
                    console.error(`[KanbanApp onEnd] Attempted to finalize move of a non-movable task (ID: ${draggedTaskId}). This should have been prevented by the 'filter' option or onStart.`);
                    // Opsional: Coba kembalikan item ke posisi semula secara visual jika bisa
                    // Ini bisa rumit dan mungkin tidak selalu mulus.
                    // Contoh sederhana (mungkin perlu disesuaikan):
                    if (evt.from && evt.oldIndex !== undefined) {
                        // evt.from.insertBefore(item, evt.from.children[evt.oldIndex]);
                        // Daripada mencoba memanipulasi DOM secara manual yang berisiko,
                        // lebih baik reload atau membiarkan backend menolak perubahan
                        // untuk menjaga konsistensi data.
                        console.warn("[KanbanApp onEnd] Visual rollback of non-movable item might be complex. Relying on backend authorization.");
                    }
                    // Jangan panggil update ke backend jika item tidak boleh dipindah.
                    return;
                }

                const newStatus = evt.to.dataset.status; // Kolom tujuan (status baru)
                const oldStatus = evt.from.dataset.status; // Kolom asal (status lama)

                console.log(`[KanbanApp onEnd] Task ID: ${draggedTaskId}, Moved from status: ${oldStatus} (index: ${evt.oldIndex}) to status: ${newStatus} (index: ${evt.newIndex})`);

                // Update data-status pada elemen task jika berpindah kolom
                if (item.dataset.status !== newStatus) {
                    item.dataset.status = newStatus;
                    console.log(`[KanbanApp onEnd] Updated data-status for task ${draggedTaskId} to ${newStatus}`);
                }

                // Panggil fungsi untuk mengirim update ke backend,
                // sertakan ID task yang di-drag.
                this.debouncedUpdateTaskOrders(draggedTaskId);

                // Update UI setelah jeda singkat untuk memastikan DOM stabil
                // dan data dari backend (jika ada) telah diproses.
                setTimeout(() => {
                    this.updateTaskCounts(); // Perbarui hitungan task di setiap kolom
                    this.initializeFiltering(); // Cache ulang elemen task jika ada penambahan/penghapusan
                    this.applyFilters(); // Terapkan filter yang ada
                    console.log('[KanbanApp onEnd] UI updates (counts, filters) scheduled.');
                }, 100); // Jeda sedikit lebih lama untuk memastikan
            }
            // --- AKHIR BAGIAN YANG ANDA MINTA ---
        });

        list.sortableInstance = sortable;
        this.sortableInstances.push(sortable);
    });
},

        handleTaskFormSuccess: function(detail) { // Menerima 'detail' object langsung
            const { isEdit, task, taskHtml } = detail;

            console.log('[KanbanApp] handleTaskFormSuccess CALLED. isEdit:', isEdit, 'Task ID:', task ? task.id : 'N/A');
            // console.log('[KanbanApp] Received Task Object:', JSON.parse(JSON.stringify(task))); // Opsional, bisa di-uncomment jika perlu
            // console.log('[KanbanApp] Received Task HTML:', taskHtml ? taskHtml.substring(0,100) + "..." : "No HTML"); // Log snippet HTML

            // Pemeriksaan `this.isInitialized` tidak terlalu krusial jika dipanggil langsung,
            // tapi tetap baik untuk ada sebagai pengaman.
            if (!this.isInitialized) {
                console.warn('[KanbanApp] Not fully initialized when handleTaskFormSuccess was called. Aborting.');
                // Mungkin perlu memberi tahu pengguna atau mencoba lagi nanti
                this.showStatusMessage('Kanban board is still initializing. Please try again in a moment.', false);
                // Untuk mencoba lagi, bisa simpan 'detail' dan panggil lagi setelah inisialisasi,
                // tapi ini bisa jadi kompleks. Lebih baik pastikan inisialisasi selesai.
                return;
            }

            if (!task || !task.id || !task.status || !taskHtml) {
                console.error('[KanbanApp] Task data or HTML is incomplete in detail:', detail);
                this.showStatusMessage('Error: Incomplete task data received from server.', false);
                return;
            }

            const statusColumn = document.querySelector(`.task-list[data-status="${task.status}"]`);
            if (!statusColumn) {
                console.error(`[KanbanApp] Target column for status "${task.status}" not found.`);
                this.showStatusMessage(`Error: Column for status "${task.status}" not found.`, false);
                return;
            }

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = taskHtml.trim();
            const newTaskCard = tempDiv.firstChild;

            if (!newTaskCard || !(newTaskCard instanceof Element) || !newTaskCard.classList.contains('task')) {
                console.error('[KanbanApp] Failed to create a valid task card element from HTML:', taskHtml);
                this.showStatusMessage('Error displaying new task card.', false);
                return;
            }

            // Pastikan data attributes penting ada di newTaskCard (taskHtml dari server seharusnya sudah benar)
            newTaskCard.dataset.id = task.id;
            newTaskCard.dataset.status = task.status;
            newTaskCard.dataset.assignedUserId = task.assigned_user_id || task.assigned_to || '';
            newTaskCard.dataset.difficultyId = task.difficulty_level_id || '';
            newTaskCard.dataset.priorityId = task.priority_level_id || '';
            newTaskCard.dataset.startDate = task.start_time ? task.start_time.substring(0, 10) : '';
            newTaskCard.dataset.endDate = task.end_time ? task.end_time.substring(0, 10) : '';
            newTaskCard.dataset.titleLower = (task.title || '').toLowerCase();
            newTaskCard.dataset.descLower = (task.description || '').toLowerCase();

            let cardToFocus = null;

            if (isEdit) {
                const existingCard = document.querySelector(`.task[data-id="${task.id}"]`);
                if (existingCard) {
                    const oldStatus = existingCard.dataset.status;
                    console.log(`[KanbanApp] Replacing task card ${task.id}. Old status: ${oldStatus}, New status: ${task.status}`);
                    if (oldStatus !== task.status && existingCard.parentElement !== statusColumn) {
                        console.log(`[KanbanApp] Task ${task.id} moved from ${oldStatus} to ${task.status}. Removing from old column.`);
                        existingCard.remove();
                        statusColumn.appendChild(newTaskCard);
                    } else {
                        existingCard.replaceWith(newTaskCard);
                    }
                    cardToFocus = newTaskCard;
                } else {
                    console.warn(`[KanbanApp] Edit mode, but existing card for ${task.id} not found. Appending to ${task.status}.`);
                    statusColumn.appendChild(newTaskCard);
                    cardToFocus = newTaskCard;
                }
            } else { // New task
                console.log(`[KanbanApp] Adding new task card ${task.id} to column ${task.status}.`);
                statusColumn.appendChild(newTaskCard);
                cardToFocus = newTaskCard;
            }

            if (cardToFocus) {
                const deleteButton = cardToFocus.querySelector('.delete-task-btn');
                if (deleteButton) {
                    this.attachDeleteListener(deleteButton);
                }
            }
            
            console.log('[KanbanApp] DOM updated. Re-caching task elements and applying filters.');
            this.initializeFiltering(); // WAJIB untuk memperbarui cache this.allTaskElements
            this.applyFilters();      // Terapkan filter (akan menggunakan cache yang baru)
            this.updateTaskCounts();  // Perbarui hitungan di header kolom

            // Pesan sukses sudah di-handle oleh modal jika pemanggilan langsung berhasil.
            // Jika tidak, event 'show-status-message' akan ditangani oleh listener global atau KanbanApp sendiri.

            // Penting: Beri tahu SortableJS tentang perubahan pada list jika ada masalah
            // Ini bisa dilakukan dengan menghancurkan dan menginisialisasi ulang instance Sortable
            // untuk kolom yang relevan, atau untuk semua kolom jika lebih mudah.
            // this.refreshSortableForColumn(statusColumn); // Fungsi hipotetis
            // Atau, jika sering terjadi perubahan status:
            // this.initializeSortable(); // Ini mungkin berat, tapi paling aman.
        },

        // Handle task deletion
        handleDeleteClick: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const button = e.currentTarget;
            const form = button.closest('form');
            if (!form) return;

            const taskId = form.dataset.taskId;
            if (!taskId) {
                console.error("Delete button form is missing data-task-id");
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
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const taskCard = document.querySelector(`.task[data-id="${taskId}"]`);
                        if (taskCard) {
                            taskCard.remove();
                            this.showStatusMessage('Task deleted.');
                            this.initializeFiltering();
                            this.applyFilters();
                            this.updateTaskCounts();
                        }
                    } else {
                        this.showStatusMessage(`Error: ${data.message || 'Server error during deletion.'}`, false);
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    this.showStatusMessage(`Network or server error: ${error.message || 'Could not delete task.'}`, false);
                })
                .finally(() => {
                    if (button) {
                        button.disabled = false;
                        button.style.opacity = '1';
                    }
                });
            }
        },

        // Attach delete listener to a button
        attachDeleteListener: function(button) {
            if (!button) return;
            // Hapus listener lama jika ada untuk menghindari duplikasi, terutama jika elemen tidak di-clone
            // Cara paling aman adalah clone & replace, atau tandai jika sudah ada listener.
            if (button.dataset.deleteListenerAttached === 'true') {
                 // Jika tombol yang sama (bukan clone) dan listener sudah ada, tidak perlu pasang lagi.
                 // Tapi jika tombol adalah bagian dari HTML baru hasil replaceWith, maka perlu listener baru.
                 // Untuk keamanan, kita akan selalu mencoba remove dan add lagi, ATAU clone.
                 // Mari kita coba pendekatan cloneNode untuk memastikan kebersihan.
            }

            // Clone tombol untuk memastikan tidak ada listener lama yang menempel
            // dan untuk mengatasi masalah jika tombol yang sama dirujuk berkali-kali.
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', (e) => this.handleDeleteClick(e)); // Gunakan arrow function untuk `this`
            newButton.dataset.deleteListenerAttached = 'true'; // Tandai bahwa listener sudah dipasang
        },

        // Initialize task filtering
        // Inside KanbanApp object
initializeFiltering: function() {
    console.log('KanbanApp: Caching task elements.');
    this.allTaskElements = Array.from(document.querySelectorAll('.task-list .task'));
    // Tidak perlu lagi mengisi data-title-lower / data-desc-lower di sini
    console.log(`Cached ${this.allTaskElements.length} task elements.`);
},

        // Apply filters to tasks
        applyFilters: function() {
            if (!this.allTaskElements.length && document.querySelectorAll('.task-list .task').length > 0) {
                console.log("applyFilters: Cache empty, re-initializing filtering.")
                this.initializeFiltering();
            }

            if (!this.allTaskElements.length) {
                this.updateTaskCounts();
                return;
            }

            const filters = {
                search: this.filterElements.search?.value.toLowerCase().trim() || '',
                user: this.filterElements.user?.value || '',
                start: this.filterElements.start?.value ? new Date(this.filterElements.start.value + 'T00:00:00Z') : null,
                end: this.filterElements.end?.value ? new Date(this.filterElements.end.value + 'T23:59:59Z') : null,
                difficulty: this.filterElements.difficulty?.value || '',
                priority: this.filterElements.priority?.value || '',
                status: this.filterElements.status?.value || ''
            };

            this.updateActiveFiltersUI(filters);

            this.allTaskElements.forEach(taskEl => {
                const taskStatus = taskEl.dataset.status || '';
                const assignedUserId = taskEl.dataset.assignedUserId || '';
                const difficultyId = taskEl.dataset.difficultyId || '';
                const priorityId = taskEl.dataset.priorityId || '';
                const startDateStr = taskEl.dataset.startDate || '';
                const endDateStr = taskEl.dataset.endDate || '';
                const title = taskEl.dataset.titleLower || '';
                const desc = taskEl.dataset.descLower || '';

                let matches = true;

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
                    const taskStart = startDateStr ? new Date(startDateStr + 'T00:00:00Z') : null;
                    const taskEnd = endDateStr ? new Date(endDateStr + 'T23:59:59Z') : null;

                    if (filters.start && (!taskStart || taskStart < filters.start)) {
                        matches = false;
                    }

                    if (matches && filters.end && (!taskEnd || taskEnd > filters.end)) {
                        matches = false;
                    }
                }

                taskEl.style.display = matches ? '' : 'none';
            });

            this.updateTaskCounts();
        },

        // Update active filters UI
        updateActiveFiltersUI: function(filters) {
            if (!this.filterElements.activeContainer) return;
            this.filterElements.activeContainer.innerHTML = '';

            const addPill = (text, clearFn) => {
                const pill = document.createElement('div');
                pill.className = 'flex items-center bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-medium';
                pill.innerHTML = `<span class="mr-1">${text}</span><button type="button" class="ml-0.5 focus:outline-none text-indigo-400 hover:text-indigo-600" title="Remove filter"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></button>`;
                pill.querySelector('button').addEventListener('click', clearFn);
                this.filterElements.activeContainer.appendChild(pill);
            };

            if (filters.search) {
                addPill(`Search: "${filters.search}"`, () => {
                    this.filterElements.search.value = '';
                    this.applyFilters();
                });
            }

            if (filters.user && this.filterElements.user && this.filterElements.user.value !== '') {
                 const selectedOption = this.filterElements.user.options[this.filterElements.user.selectedIndex];
                addPill(`User: ${selectedOption ? selectedOption.text : filters.user}`, () => {
                    this.filterElements.user.value = '';
                    this.applyFilters();
                });
            }

            if (filters.start) {
                addPill(`Start: ${this.formatDate(filters.start)}`, () => {
                    this.filterElements.start.value = '';
                    this.applyFilters();
                });
            }

            if (filters.end) {
                addPill(`Due: ${this.formatDate(filters.end)}`, () => {
                    this.filterElements.end.value = '';
                    this.applyFilters();
                });
            }

            if (filters.difficulty && this.filterElements.difficulty && this.filterElements.difficulty.value !== '') {
                const selectedOption = this.filterElements.difficulty.options[this.filterElements.difficulty.selectedIndex];
                addPill(`Difficulty: ${selectedOption ? selectedOption.text : filters.difficulty}`, () => {
                    this.filterElements.difficulty.value = '';
                    this.applyFilters();
                });
            }

            if (filters.priority && this.filterElements.priority && this.filterElements.priority.value !== '') {
                 const selectedOption = this.filterElements.priority.options[this.filterElements.priority.selectedIndex];
                addPill(`Priority: ${selectedOption ? selectedOption.text : filters.priority}`, () => {
                    this.filterElements.priority.value = '';
                    this.applyFilters();
                });
            }

            if (filters.status && this.filterElements.status && this.filterElements.status.value !== '') {
                 const selectedOption = this.filterElements.status.options[this.filterElements.status.selectedIndex];
                addPill(`Status: ${selectedOption ? selectedOption.text : filters.status}`, () => {
                    this.filterElements.status.value = '';
                    this.applyFilters();
                });
            }
        },

        // Clear all filters
        clearFilters: function() {
            if (this.filterElements.search) this.filterElements.search.value = '';
            if (this.filterElements.user) this.filterElements.user.value = '';
            if (this.filterElements.start) this.filterElements.start.value = '';
            if (this.filterElements.end) this.filterElements.end.value = '';
            if (this.filterElements.difficulty) this.filterElements.difficulty.value = '';
            if (this.filterElements.priority) this.filterElements.priority.value = '';
            if (this.filterElements.status) this.filterElements.status.value = '';
            this.applyFilters();
        },

        // Format date for display
        formatDate: function(date) {
            if (!date || isNaN(date)) return '';
            const options = { year: 'numeric', month: 'short', day: 'numeric', timeZone: 'UTC' };
            return date.toLocaleDateString(undefined, options);
        },

        // Setup event listeners
        setupEventListeners: function() {
            console.log('[KanbanApp] Setting up event listeners (filters, delete, observer).');

            // Filter Listeners
            if (!this._debouncedApplyFilters) this._debouncedApplyFilters = debounce(() => this.applyFilters(), 350);
            if (!this._boundApplyFilters) this._boundApplyFilters = () => this.applyFilters();
            if (!this._boundClearFilters) this._boundClearFilters = () => this.clearFilters();
            const fe = this.filterElements;
            fe.search?.removeEventListener('input', this._debouncedApplyFilters);
            fe.search?.addEventListener('input', this._debouncedApplyFilters);
            ['user', 'start', 'end', 'difficulty', 'priority', 'status'].forEach(key => {
                fe[key]?.removeEventListener('change', this._boundApplyFilters);
                fe[key]?.addEventListener('change', this._boundApplyFilters);
            });
            fe.clearBtn?.removeEventListener('click', this._boundClearFilters);
            fe.clearBtn?.addEventListener('click', this._boundClearFilters);

            // Delete Button Listeners
            document.querySelectorAll('.task-list .task .delete-task-btn').forEach(button => {
                this.attachDeleteListener(button);
            });

            // Mutation Observer
            if (this.activeTaskListObserver) {
                this.activeTaskListObserver.disconnect();
                // Bersihkan juga flag observerAttached dari list sebelumnya jika ada
                document.querySelectorAll('.task-list[data-observer-attached="true"]').forEach(list => {
                    delete list.dataset.observerAttached;
                });
            }
            
            this.activeTaskListObserver = new MutationObserver((mutationsList) => {
                 let needsUpdate = false;
                 for (const mutation of mutationsList) {
                     if (mutation.type === 'childList') { // Hanya perhatikan perubahan anak langsung
                         if (mutation.addedNodes.length > 0) {
                             mutation.addedNodes.forEach(node => {
                                  if (node.nodeType === Node.ELEMENT_NODE && node.classList.contains('task')) {
                                      const btn = node.querySelector('.delete-task-btn');
                                      if (btn) this.attachDeleteListener(btn);
                                      needsUpdate = true;
                                  }
                              });
                         }
                          if (mutation.removedNodes.length > 0) {
                              mutation.removedNodes.forEach(node => {
                                   if (node.nodeType === Node.ELEMENT_NODE && node.classList.contains('task')) {
                                       needsUpdate = true;
                                   }
                               });
                          }
                     }
                 }
                  if(needsUpdate) {
                      // Beri sedikit delay untuk memastikan semua mutasi telah diproses
                      // dan DOM stabil sebelum melakukan pembaruan.
                      setTimeout(() => {
                          console.log("[KanbanApp MutationObserver] Triggering DOM refresh (initFiltering, applyFilters, updateCounts).");
                          this.initializeFiltering(); // Cache ulang elemen task
                          this.applyFilters();      // Terapkan filter yang ada
                          this.updateTaskCounts();  // Perbarui hitungan
                      }, 50); 
                  }
            });

            document.querySelectorAll('.task-list').forEach(list => {
                if (!list.dataset.observerAttached) {
                    // Amati perubahan pada childList (penambahan/penghapusan node task)
                    // subtree: false karena kita hanya tertarik pada anak langsung (task cards)
                    this.activeTaskListObserver.observe(list, { childList: true, subtree: false });
                    list.dataset.observerAttached = 'true';
                }
            });
        },

        // Initialize the Kanban board
        init: function() {
            if (this.isInitialized) {
                console.log('[KanbanApp] Already initialized. Running cleanup before re-init.');
                this.cleanup();
            }

            console.log('[KanbanApp] Initializing...');
            this.isInitialized = false; // Set false di awal

            this.statusMessageElement = document.getElementById('status-message');
            this.filterElements = {
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

            // PENTING: Panggil initializeSortable SEBELUM setupEventListeners
            // agar task-list sudah siap untuk di-observe oleh MutationObserver
            this.initializeSortable(); 
            
            this.setupEventListeners(); // setupEventListeners akan mengattach MutationObserver
            
            this.initializeFiltering();
            this.applyFilters();
            this.updateTaskCounts();

            this.isInitialized = true; // Set true di akhir setelah semua siap
            console.log('[KanbanApp] Initialization complete.');

            return () => this.cleanup(); // Mengembalikan fungsi cleanup
        },

        // Cleanup function
        cleanup: function() {
            console.log('[KanbanApp] Cleaning up...');

            // Remove filter listeners
            const fe = this.filterElements;
            if (this._debouncedApplyFilters && fe.search) fe.search.removeEventListener('input', this._debouncedApplyFilters);
            if (this._boundApplyFilters) {
                ['user', 'start', 'end', 'difficulty', 'priority', 'status'].forEach(key => {
                    fe[key]?.removeEventListener('change', this._boundApplyFilters);
                });
            }
            if (this._boundClearFilters && fe.clearBtn) fe.clearBtn.removeEventListener('click', this._boundClearFilters);

            // Hapus listener global yang mungkin di-bind di sini (jika ada, tapi sekarang tidak ada)
            // if (this._boundHandleTaskFormSuccess) {
            //     document.removeEventListener('task-form-success', this._boundHandleTaskFormSuccess);
            //     this._boundHandleTaskFormSuccess = null; // Bersihkan referensi
            // }
            // if (this._boundHandleShowStatusMessage) {
            //      document.removeEventListener('show-status-message', this._boundHandleShowStatusMessage);
            //      this._boundHandleShowStatusMessage = null; // Bersihkan referensi
            // }

            // Destroy SortableJS instances
            this.sortableInstances.forEach(instance => {
                try { instance.destroy(); } catch(e) { console.warn("[KanbanApp Cleanup] Sortable destroy error:", e); }
            });
            this.sortableInstances = [];
            
            document.querySelectorAll('.task-list').forEach(list => {
                if (list.sortableInstance) {
                     try { list.sortableInstance.destroy(); list.sortableInstance = null; } catch(e) {}
                }
                // Hapus flag observer saat cleanup
                if (list.dataset.observerAttached) {
                    delete list.dataset.observerAttached;
                }
            });

            // Hentikan MutationObserver
            if (this.activeTaskListObserver) {
                this.activeTaskListObserver.disconnect();
                this.activeTaskListObserver = null;
            }

            this.allTaskElements = [];
            this.isInitialized = false;
            console.log('[KanbanApp] Cleanup complete.');
        }
    }
    // Page setup and cleanup control
    window.currentKanbanPageCleanup = null;

    function setupSpecificKanbanPage() {
        const isKanbanPage = document.getElementById('taskSearch') || document.querySelector('.task-list');

        if (!isKanbanPage) {
            if (typeof window.currentKanbanPageCleanup === 'function') {
                console.log("Navigated away from Kanban page, running previous cleanup.");
                window.currentKanbanPageCleanup();
                window.currentKanbanPageCleanup = null;
            }
            return;
        }

        if (typeof window.currentKanbanPageCleanup === 'function') {
            console.log("Kanban page (re)detected, running previous cleanup before new init.");
            window.currentKanbanPageCleanup();
        }

        console.log("Setting up Kanban page specific logic...");
        window.currentKanbanPageCleanup = window.KanbanApp.init.call(window.KanbanApp);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', setupSpecificKanbanPage);
    document.addEventListener('turbo:load', setupSpecificKanbanPage);

    // Cleanup before Turbo caches the page
    document.addEventListener('turbo:before-cache', () => {
        if (typeof window.currentKanbanPageCleanup === 'function') {
            console.log("Turbo:before-cache, running Kanban cleanup.");
            window.currentKanbanPageCleanup();
            window.currentKanbanPageCleanup = null;
        }
    });
</script>
@endpush

</x-app-layout>