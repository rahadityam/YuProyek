<div x-data="{ 
    editTaskModal: false, 
    currentTask: null 
}" 
x-init="
    $watch('currentTask', (value) => {
        console.log('Current Task Updated:', value);
    })
"
x-on:open-edit-task-modal.window="
    editTaskModal = true; 
    currentTask = $event.detail.task;
    console.log('Opening Edit Modal:', currentTask);
"
x-on:close-edit-task-modal.window="editTaskModal = false; 
    console.log('Opening Edit Modal:', currentTask);">
    <div x-show="editTaskModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 text-center md:items-center sm:block sm:p-0">
            <!-- Overlay -->
            <div x-show="editTaskModal" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-40" 
                 aria-hidden="true"
                 @click="editTaskModal = false">
            </div>

            <!-- Modal Content -->
            <div x-show="editTaskModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-2xl p-8 my-20 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl 2xl:max-w-3xl">
                
                <div class="flex items-center justify-between space-x-4">
                    <h1 class="text-xl font-medium text-gray-800">Edit Task</h1>
                    <button @click="editTaskModal = false" class="text-gray-600 focus:outline-none hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>

                <!-- Edit Task Form -->
                <form id="editTaskForm" method="POST" x-ref="editTaskForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Task Title -->
                        <div class="mb-4 col-span-2">
                            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">
                                Nama Tugas:
                            </label>
                            <input type="text" name="title" id="title" x-model="currentTask.title"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   required>
                        </div>
                        
                        <!-- Task Description -->
                        <div class="mb-4 col-span-2">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                                Deskripsi:
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      x-model="currentTask.description"
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                        
                        <!-- Difficulty Level -->
                        <div class="mb-4">
                            <label for="difficulty_level" class="block text-gray-700 text-sm font-bold mb-2">
                                Tingkat Kesulitan:
                            </label>
                            <select name="difficulty_level" id="difficulty_level"
                                   x-model="currentTask.difficulty_level"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   required>
                                <option value="">Pilih Level</option>
                                <option value="1">1 - Sangat Ringan</option>
                                <option value="2">2 - Ringan</option>
                                <option value="3">3 - Normal</option>
                                <option value="4">4 - Berat</option>
                                <option value="5">5 - Sangat Berat</option>
                            </select>
                        </div>
                        
                        <!-- Priority Level -->
                        <div class="mb-4">
                            <label for="priority_level" class="block text-gray-700 text-sm font-bold mb-2">
                                Prioritas:
                            </label>
                            <select name="priority_level" id="priority_level"
                                   x-model="currentTask.priority_level"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   required>
                                <option value="">Pilih Prioritas</option>
                                <option value="1">1 - Sangat Rendah</option>
                                <option value="2">2 - Rendah</option>
                                <option value="3">3 - Normal</option>
                                <option value="4">4 - Tinggi</option>
                                <option value="5">5 - Sangat Tinggi</option>
                            </select>
                        </div>
                        
                        <!-- Start Time -->
<div class="mb-4">
    <label for="start_time" class="block text-gray-700 text-sm font-bold mb-2">
        Tanggal Mulai:
    </label>
    <input type="date" name="start_time" id="start_time" 
           x-model="currentTask.start_time ? 
                     (new Date(currentTask.start_time)).toISOString().split('T')[0] : ''"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
           required>
</div>

<!-- End Time -->
<div class="mb-4">
    <label for="end_time" class="block text-gray-700 text-sm font-bold mb-2">
        Tanggal Akhir:
    </label>
    <input type="date" name="end_time" id="end_time" 
           x-model="currentTask.end_time ? 
                     (new Date(currentTask.end_time)).toISOString().split('T')[0] : ''"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
           required>
</div>
                        
                        <!-- Assigned User -->
                        <div class="mb-4 col-span-2">
                            <label for="assigned_to" class="block text-gray-700 text-sm font-bold mb-2">
                                Assign User:
                            </label>
                            <select name="assigned_to" id="assigned_to"
                                   x-model="currentTask.assigned_to"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   required>
                                <option value="">Pilih User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex items-center justify-end mt-6">
                        <button type="submit" 
                                class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editTaskForm = document.getElementById('editTaskForm');

    editTaskForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get the form data
        const formData = new FormData(editTaskForm);
        
        // Get the task ID from the Alpine component's currentTask
        const taskId = Alpine.$data(editTaskForm.closest('[x-data]')).currentTask.id;
        
        fetch(`/tasks/${taskId}`, {
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
                // Update the task card in the Kanban board
                const existingTaskCard = document.querySelector(`.task[data-id="${taskId}"]`);
                
                if (existingTaskCard) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.taskHtml.trim();
                    const newTaskCard = tempDiv.firstChild;
                    
                    // Replace the existing task card
                    existingTaskCard.parentNode.replaceChild(newTaskCard, existingTaskCard);
                }
                
                // Close the modal
                window.dispatchEvent(new CustomEvent('close-edit-task-modal'));
            } else {
                alert('Failed to update task');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the task');
        });
    });
});

// Modify task cards to trigger edit modal
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to "Edit Task" links in task cards
    document.querySelectorAll('.edit-task-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const taskId = this.dataset.taskId;
            const taskData = JSON.parse(this.dataset.taskData);
            
            // Store the current task ID for form submission
            Alpine.store('currentEditTaskId', taskId);
            
            // Dispatch event to open edit modal with task data
            window.dispatchEvent(new CustomEvent('open-edit-task-modal', { 
                detail: { task: taskData } 
            }));
        });
    });
});
</script>