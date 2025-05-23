<div> {{-- Root element wajib --}}

    {{-- Modal Task (Include partial Blade biasa, tapi pastikan variabel tersedia) --}}
    {{-- Variabel $project, $users, $difficultyLevels, $priorityLevels berasal dari properti publik komponen Livewire --}}
    @include('tasks.partials.task_modal', [
        'project' => $project,
        'users' => $users,
        'difficultyLevels' => $difficultyLevels,
        'priorityLevels' => $priorityLevels
    ])

    {{-- Kanban Board Layout (Ambil dari view lama, buang <x-app-layout>) --}}
    {{-- Sesuaikan tinggi jika perlu, misal dikurangi tinggi breadcrumb --}}
    <div class="container mx-auto flex flex-col" style="height: calc(100vh - 4rem - 4rem);">

        {{-- Header: Filter & Search --}}
        {{-- PENTING: Filter ini masih menggunakan Javascript Bawaan. Jika ingin filter Livewire, --}}
        {{-- ganti input/select dengan wire:model dan hapus event listener JS filter --}}
        <div class="px-4 pb-1 flex-shrink-0 border-b border-gray-200">
            <div class="container mx-auto">
                <div class="flex justify-end items-center mb-3">
                    <div x-data="{ showFilters: false }" class="relative">
                        <div class="flex items-center gap-2">
                            <div class="w-64 relative">
                                <input type="text" id="taskSearch" placeholder="Search tasks..." class="w-full px-3 py-1.5 pr-8 rounded-lg border border-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"> <svg>...</svg> </div>
                            </div>
                            <button @click="showFilters = !showFilters" class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg flex items-center">
                                <svg>...</svg> <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
                            </button>
                        </div>
                        {{-- Dropdown Filter --}}
                        <div x-show="showFilters" @click.away="showFilters = false" class="absolute right-0 mt-2 p-4 bg-white shadow-lg rounded-lg border border-gray-200 z-10 w-80" style="display: none;">
                            <div class="grid grid-cols-1 gap-3">
                                {{-- User Filter --}}
                                <div>
                                    <label for="userFilter" class="block text-xs font-medium text-gray-700 mb-1">Assigned To</label>
                                    {{-- Select ini dikontrol JS, bukan Livewire --}}
                                    <select id="userFilter" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="">All Users</option>
                                        @foreach($users as $user) {{-- Ambil dari properti publik $users --}}
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Filter lainnya (Date, Difficulty, Priority, Status) --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div> <label for="startDateFilter">Start Date</label> <input type="date" id="startDateFilter" class="..."> </div>
                                    <div> <label for="endDateFilter">End Date</label> <input type="date" id="endDateFilter" class="..."> </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="difficultyFilter">Difficulty</label>
                                        <select id="difficultyFilter" class="...">
                                            <option value="">All Levels</option>
                                            @foreach($difficultyLevels as $level) {{-- Ambil dari properti publik --}}
                                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="priorityFilter">Priority</label>
                                        <select id="priorityFilter" class="...">
                                            <option value="">All Priorities</option>
                                             @foreach($priorityLevels as $level) {{-- Ambil dari properti publik --}}
                                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="statusFilter">Status</label>
                                    <select id="statusFilter" class="...">
                                        <option value="">All Statuses</option>
                                        <option>To Do</option> <option>In Progress</option> <option>Review</option> <option>Done</option>
                                    </select>
                                </div>
                                <div class="flex mt-1"> <button id="clearFilters" class="..."> Clear Filters </button> </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="activeFilters" class="flex flex-wrap gap-1 mt-2 justify-end"></div>
            </div>
        </div>

        <!-- Scrollable Kanban Columns Container -->
        {{-- wire:ignore.self PENTING agar SortableJS tidak bentrok dengan Livewire --}}
        <div class="overflow-x-auto overflow-y-hidden flex-1 pb-4 px-4" wire:ignore.self>
            <div class="flex flex-row gap-4 h-full min-w-max">
                @php
                    $kanbanStatuses = [
                        'To Do'       => ['color' => '#ef4444'],
                        'In Progress' => ['color' => '#f59e0b'],
                        'Review'      => ['color' => '#3b82f6'],
                        'Done'        => ['color' => '#10b981']
                    ];
                @endphp

                @foreach($kanbanStatuses as $status => $config)
                    <div x-data="{ isCollapsed: false }"
                         class="flex flex-col bg-gray-100 shadow-sm transition-all duration-300 rounded-lg overflow-hidden h-full border border-gray-200"
                         :style="isCollapsed ? 'width: 50px; min-width: 50px;' : 'width: 340px; min-width: 340px;'">

                        {{-- Header Collapse --}}
                        <div x-show="isCollapsed" @click="isCollapsed = false" ...>
                             <h3 class="... transform -rotate-90 ...">
                                 {{ $status }}
                                 {{-- Count total dari $tasks (karena $tasksByStatus mungkin berubah saat filter JS) --}}
                                 <span class="task-count-collapsed text-xs font-normal text-gray-500">({{ $tasks->where('status', $status)->count() }})</span>
                             </h3>
                        </div>

                        {{-- Header Expand --}}
                        <div x-show="!isCollapsed" class="py-2.5 px-3 ...">
                             <div class="flex items-center">
                                 <div class="w-2.5 h-2.5 rounded-full mr-2 ..." style="background-color: {{ $config['color'] }};"></div>
                                 <h3 class="font-semibold text-gray-700 text-sm flex items-center">
                                     {{ $status }}
                                     {{-- Count akan diupdate JS --}}
                                     <span class="text-xs font-medium text-gray-500 ml-1.5 bg-gray-100 rounded px-1.5 py-0.5 task-count">
                                         ({{ $tasksByStatus[$status]->count() ?? 0 }}) {{-- Tampilkan count awal --}}
                                     </span>
                                 </h3>
                             </div>
                            <div class="flex items-center space-x-1">
                                {{-- Tombol Add Task tetap trigger Alpine event --}}
                                <button @click="$dispatch('open-task-modal', { status: '{{ $status }}' })" class="btn-icon" title="Add Task"> <svg>...</svg> </button>
                                <button @click="isCollapsed = true" class="btn-icon" title="Collapse"> <svg>...</svg> </button>
                            </div>
                        </div>

                        {{-- Task List (Scrollable Area) --}}
                        {{-- ID dibuat unik dengan component ID --}}
                        <div x-show="!isCollapsed" id="{{ strtolower(str_replace(' ', '-', $status)) }}_{{ $this->id }}"
                             class="task-list flex-1 p-2 overflow-y-auto"
                             data-status="{{ $status }}">
                             {{-- Template Kosong --}}
                             <div class="task-list-empty-template text-center text-xs text-gray-500 py-6 px-3 italic absolute inset-0 flex items-center justify-center pointer-events-none" style="display: none;">
                                 No tasks in {{ $status }}.
                             </div>
                             {{-- Loop Task Cards --}}
                             {{-- Gunakan $tasksByStatus yang dikirim dari render() --}}
                             @foreach($tasksByStatus[$status] ?? [] as $task)
                                 @include('tasks.partials.task_card', ['task' => $task, 'color' => $config['color']])
                             @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Status message (jika perlu) --}}
    <div id="status-message_{{ $this->id }}" class="fixed bottom-4 right-4 ... z-[60]" style="display: none;"></div>


    {{-- Javascript untuk Sortable, Filter JS, dan Modal Interaction --}}
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <script>
        // Pastikan fungsi debounce ada (bisa ditaruh global di app.js atau di sini)
        function debounce(func, wait) { let timeout; return function executedFunction(...args) { const later = () => { clearTimeout(timeout); func.apply(this, args); }; clearTimeout(timeout); timeout = setTimeout(later, wait); }; }

        // Buat fungsi inisialisasi Kanban spesifik untuk komponen ini
        function initializeKanbanBoard_{{ $this->id }}() {
            // console.log('Initializing Kanban Board JS for component {{ $this->id }}');
            const statusMessage = document.getElementById('status-message_{{ $this->id }}'); // ID Unik
            const filterElements = {
                search: document.getElementById('taskSearch'), // Asumsi ID global unik atau perlu ID unik juga
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

            function showStatusMessage(message, isSuccess = true) {
                if (!statusMessage) return;
                statusMessage.textContent = message;
                statusMessage.className = `fixed bottom-4 right-4 py-2 px-4 rounded-lg shadow-lg transform transition-all duration-300 ease-out z-[60] ${isSuccess ? 'bg-green-500' : 'bg-red-500'} text-white text-sm`;
                statusMessage.style.display = 'block'; // Tampilkan
                requestAnimationFrame(() => { statusMessage.classList.remove('translate-y-full', 'opacity-0'); statusMessage.classList.add('translate-y-0', 'opacity-100'); });
                setTimeout(() => { statusMessage.classList.remove('translate-y-0', 'opacity-100'); statusMessage.classList.add('translate-y-full', 'opacity-0'); setTimeout(()=> statusMessage.style.display = 'none', 300); }, 3500);
            }

            // Listener global untuk status message (jika modal bukan Livewire)
            // Jika modal juga Livewire, bisa emit event Livewire
            window.addEventListener('show-status-message', event => {
                showStatusMessage(event.detail.message, event.detail.success);
            });


            function updateTaskCounts() {
                // Target kolom berdasarkan ID unik komponen
                document.querySelectorAll('.task-list[id$="_{{ $this->id }}"]').forEach(list => {
                    const status = list.dataset.status;
                    const columnDiv = list.closest('.flex.flex-col');
                    const visibleTasks = list.querySelectorAll('.task:not([style*="display: none"])');
                    const totalTasksInList = list.querySelectorAll('.task').length;
                    const count = visibleTasks.length;

                    const countElement = columnDiv?.querySelector('.task-count');
                    if (countElement) { countElement.textContent = `(${count})`; } // Update format count

                    const countCollapsedElement = columnDiv?.querySelector('.task-count-collapsed');
                    if (countCollapsedElement) { countCollapsedElement.textContent = `(${totalTasksInList})`; } // Count total untuk collapsed

                    const emptyPlaceholder = list.querySelector('.task-list-empty-template');
                    if (emptyPlaceholder) {
                        emptyPlaceholder.style.display = totalTasksInList === 0 ? 'flex' : 'none';
                    }
                });
            }

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
                 const orderData = {};
                 // Target kolom spesifik komponen ini
                 document.querySelectorAll('.task-list[id$="_{{ $this->id }}"]').forEach(list => {
                     const status = list.dataset.status;
                     orderData[status] = Array.from(list.querySelectorAll('.task'))
                         .map((item, index) => ({ id: item.dataset.id, order: index }));
                 });
                 if (Object.keys(orderData).length === 0) return;
                 // console.log('Sending batch update:', orderData);
                 fetch('{{ route('tasks.batchUpdate') }}', { // Gunakan route helper
                     method: 'POST',
                     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                     body: JSON.stringify({ data: orderData, project_id: {{ $project->id }} })
                 })
                 .then(response => response.json())
                 .then(data => { if (!data.success) { console.error("Batch update failed:", data); showStatusMessage('Error saving task order.', false); } })
                 .catch(error => { console.error('Error updating tasks:', error); showStatusMessage('Network error saving task order.', false); });
            }, 750);

            function initializeSortable() {
                document.querySelectorAll('.task-list[id$="_{{ $this->id }}"]').forEach(list => {
                    if (list.sortableInstance) { list.sortableInstance.destroy(); }
                    list.sortableInstance = new Sortable(list, {
                        group: 'shared_{{ $this->id }}', // Group unik per komponen
                        animation: 150, ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen', dragClass: 'sortable-drag',
                        draggable: '.task',
                        onEnd: function(evt) {
                            const item = evt.item;
                            if (!item || !item.classList.contains('task')) return;
                            const newStatus = evt.to.dataset.status;
                            if (item.dataset.status !== newStatus) item.dataset.status = newStatus;
                            debouncedUpdateTaskOrders();
                            setTimeout(() => { updateTaskCounts(); if(typeof applyFilters === 'function') applyFilters(); }, 50);
                        }
                    });
                });
            }

            // --- Filter Logic JS (Sama seperti sebelumnya, pastikan elemen selector benar) ---
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
                  if (!allTaskElements.length && document.querySelectorAll('.task-list[id$="_{{ $this->id }}"] .task').length > 0) initializeFiltering();
                  if (!allTaskElements.length) { updateTaskCounts(); return; }
                  const filters = { search: filterElements.search?.value.toLowerCase().trim() ?? '', user: filterElements.user?.value ?? '', start: filterElements.start?.value ? new Date(filterElements.start.value + 'T00:00:00Z') : null, end: filterElements.end?.value ? new Date(filterElements.end.value + 'T23:59:59Z') : null, difficulty: filterElements.difficulty?.value ?? '', priority: filterElements.priority?.value ?? '', status: filterElements.status?.value ?? '' };
                  updateActiveFiltersUI(filters);
                  allTaskElements.forEach(taskEl => {
                       const taskStatus = taskEl.dataset.status; const assignedUserId = taskEl.dataset.assignedUserId || ''; const difficultyId = taskEl.dataset.difficultyId || ''; const priorityId = taskEl.dataset.priorityId || ''; const startDateStr = taskEl.dataset.startDate || ''; const endDateStr = taskEl.dataset.endDate || ''; const title = taskEl.dataset.titleLower || ''; const desc = taskEl.dataset.descLower || '';
                       let matches = true;
                       if (filters.search && !(title.includes(filters.search) || desc.includes(filters.search))) matches = false;
                       if (matches && filters.status && taskStatus !== filters.status) matches = false;
                       if (matches && filters.user && assignedUserId !== filters.user) matches = false;
                       if (matches && filters.difficulty && difficultyId !== filters.difficulty) matches = false;
                       if (matches && filters.priority && priorityId !== filters.priority) matches = false;
                       if (matches && (filters.start || filters.end)) { const taskStart = startDateStr ? new Date(startDateStr + 'T00:00:00Z') : null; const taskEnd = endDateStr ? new Date(endDateStr + 'T23:59:59Z') : null; if (filters.start && (!taskStart || taskStart < filters.start)) matches = false; if (matches && filters.end && (!taskEnd || taskEnd > filters.end)) matches = false; }
                       taskEl.style.display = matches ? '' : 'none';
                  });
                 updateTaskCounts();
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
             function clearFilters() { if(filterElements.search) filterElements.search.value = ''; if(filterElements.user) filterElements.user.value = ''; if(filterElements.start) filterElements.start.value = ''; if(filterElements.end) filterElements.end.value = ''; if(filterElements.difficulty) filterElements.difficulty.value = ''; if(filterElements.priority) filterElements.priority.value = ''; if(filterElements.status) filterElements.status.value = ''; applyFilters(); }
             function formatDate(date) {
                if (!date || isNaN(date)) return '';
                const options = { year: 'numeric', month: 'short', day: 'numeric', timeZone: 'UTC' }; // Specify UTC for consistent dates
                return date.toLocaleDateString(undefined, options);
            }
             // --- End Filter Logic JS ---

            // Attach listeners untuk filter JS
             if(filterElements.search) filterElements.search.addEventListener('input', debounce(applyFilters, 350));
             if(filterElements.user) filterElements.user.addEventListener('change', applyFilters);
             if(filterElements.start) filterElements.start.addEventListener('change', applyFilters);
             if(filterElements.end) filterElements.end.addEventListener('change', applyFilters);
             if(filterElements.difficulty) filterElements.difficulty.addEventListener('change', applyFilters);
             if(filterElements.priority) filterElements.priority.addEventListener('change', applyFilters);
             if(filterElements.status) filterElements.status.addEventListener('change', applyFilters);
             if(filterElements.clearBtn) filterElements.clearBtn.addEventListener('click', clearFilters);


             // --- Delete Task Listener ---
            function handleDeleteClick(e) {
                 e.preventDefault(); e.stopPropagation();
                 const button = e.currentTarget; const form = button.closest('form'); if (!form) return;
                 const taskId = form.dataset.taskId; if (!taskId) return;
                 if (confirm('Are you sure you want to delete this task?')) {
                     button.disabled = true; button.style.opacity = '0.5';
                     fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ _method: 'DELETE' }) })
                     .then(response => response.json().then(data => ({ ok: response.ok, status: response.status, body: data })))
                     .then(({ ok, status, body }) => {
                         if (ok && body.success) {
                             const taskCard = document.querySelector(`.task[data-id="${taskId}"]`);
                             if (taskCard) { taskCard.remove(); showStatusMessage('Task deleted.'); updateTaskCounts(); initializeFiltering(); applyFilters(); }
                         } else { showStatusMessage(`Error: ${body.message || 'Server error'}`, false); }
                     })
                     .catch(error => { showStatusMessage('Network error.', false); })
                     .finally(() => { if (button) { button.disabled = false; button.style.opacity = '1'; } });
                 }
             }
             window.attachDeleteListener = function(button) {
                 if (!button || button.dataset.listenerAttached === 'true') return;
                 button.addEventListener('click', handleDeleteClick);
                 button.dataset.listenerAttached = 'true';
             }
             document.querySelectorAll('.task-list[id$="_{{ $this->id }}"] .delete-task-btn').forEach(attachDeleteListener);


            // Initial setup Panggil fungsi inisialisasi utama
            initializeSortable();
            initializeFiltering(); // Panggil jika filter JS
            applyFilters(); // Panggil jika filter JS
            updateTaskCounts();

            // Listener untuk event 'task-form-success' dari modal (Non-Livewire)
            window.addEventListener('task-form-success', event => {
                 console.log('task-form-success received by Kanban', event.detail);
                 // Panggil refresh Livewire untuk memuat ulang data dari server
                 @this.call('$refresh');
                 // Atau, jika ingin update DOM manual (lebih kompleks):
                 // const { isEdit, task, taskHtml } = event.detail;
                 // ... (logika update/tambah card manual seperti di view lama) ...
                 // updateTaskCounts();
                 // initializeFiltering();
                 // applyFilters();
                 // document.querySelectorAll('.task-list[id$="_{{ $this->id }}"] .delete-task-btn:not([data-listener-attached="true"])').forEach(attachDeleteListener);
             });


        } // End initializeKanbanBoard function

        // Jalankan inisialisasi saat Livewire load dan rehydrate (jika ada interaksi Livewire lain)
        document.addEventListener('livewire:load', function () {
            initializeKanbanBoard_{{ $this->id }}();
        });
        document.addEventListener('livewire:update', function () {
             // Re-initialize Sortable setelah update DOM oleh Livewire
             // Mungkin perlu timeout kecil agar DOM benar-benar siap
             setTimeout(() => {
                 // console.log('Livewire update detected, re-initializing Sortable for {{ $this->id }}');
                 initializeKanbanBoard_{{ $this->id }}(); // Panggil fungsi init lagi
             }, 50);
        });

        // Cleanup saat komponen Livewire dihancurkan
        document.addEventListener('livewire:unload', function () {
            // console.log('Unloading Kanban JS for component {{ $this->id }}');
            document.querySelectorAll('.task-list[id$="_{{ $this->id }}"]').forEach(list => {
                if (list.sortableInstance) {
                    try { list.sortableInstance.destroy(); list.sortableInstance = null; } catch (e) {}
                }
            });
            // Hapus event listener lain jika ada
        });

    </script>
    @endpush

    {{-- Style untuk Kanban (sama seperti sebelumnya) --}}
    @push('styles')
    <style>
        .btn-icon { @apply p-1 rounded text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors duration-150 focus:outline-none focus:ring-1 focus:ring-indigo-400; }
        .task-list::-webkit-scrollbar { width: 6px; height: 6px;}
        .task-list::-webkit-scrollbar-track { background: transparent; }
        .task-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .task-list::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        #activeFilters button svg { transition: transform 0.1s ease-in-out; }
        #activeFilters button:hover svg { transform: scale(1.15); }
        .sortable-ghost { opacity: 0.4; background-color: #e0e7ff; border: 1px dashed #a5b4fc; }
        .sortable-drag { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transform: scale(1.03); }
    </style>
    @endpush

</div>