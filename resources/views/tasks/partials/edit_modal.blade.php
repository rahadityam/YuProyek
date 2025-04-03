{{-- resources/views/tasks/partials/edit_modal.blade.php --}}
<div x-data="{
        editTaskModal: false,
        currentTask: null, // Object to hold the task data being edited
        formErrors: {}, // Object to hold validation errors from server
        isLoading: false // Loading state for form submission
     }"
     x-init="
        // Optional: Log when currentTask changes for debugging
        $watch('currentTask', (value) => {
            console.log('Edit Modal - Current Task Updated:', value);
            formErrors = {}; // Clear errors when task changes
        })
     "
     x-on:open-edit-task-modal.window="
        editTaskModal = true;
        // Clone the task data to avoid modifying the original object directly
        // Ensure all expected properties exist, provide defaults if necessary
        let taskDetail = $event.detail.task || {};
        currentTask = {
            id: taskDetail.id || null,
            title: taskDetail.title || '',
            description: taskDetail.description || '',
            difficulty_level_id: taskDetail.difficulty_level_id || '', // Expecting ID
            priority_level_id: taskDetail.priority_level_id || '',     // Expecting ID
            start_time: taskDetail.start_time || '',
            end_time: taskDetail.end_time || '',
            assigned_to: taskDetail.assigned_to || '',
            status: taskDetail.status || '', // Keep track of status if needed
             // Add other fields if they are part of taskData
        };
        formErrors = {}; // Clear previous errors
        console.log('Edit Modal - Opening with Task:', currentTask);
     "
     x-on:close-edit-task-modal.window="editTaskModal = false; currentTask = null; isLoading = false; formErrors = {};" {{-- Reset on close --}}
     >

    {{-- Modal Wrapper --}}
    <div x-show="editTaskModal"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true"
         style="display: none;" {{-- Avoid flash of content --}}
         >
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

            {{-- Overlay --}}
            <div x-show="editTaskModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                 @click="editTaskModal = false" aria-hidden="true">
            </div>

            {{-- Modal Content --}}
            {{-- Prevent closing on click inside modal --}}
            <div @click.stop
                 x-show="editTaskModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-2xl p-6 sm:p-8 my-8 sm:my-20 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:align-middle">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                    <h1 class="text-xl font-medium text-gray-900" id="modal-title">Edit Task</h1>
                    <button @click="editTaskModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Server Side Validation Errors Display --}}
                 <template x-if="Object.keys(formErrors).length > 0">
                     <div class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                         <p class="font-medium">Please correct the following errors:</p>
                         <ul class="list-disc list-inside mt-1">
                             <template x-for="(errorMessages, field) in formErrors" :key="field">
                                 <template x-for="message in errorMessages" :key="message">
                                     <li x-text="message"></li>
                                 </template>
                             </template>
                         </ul>
                     </div>
                 </template>

                {{-- Edit Task Form --}}
                 {{-- We use @submit.prevent to handle submission with Alpine/JS --}}
                <form id="editTaskForm"
                      method="POST"
                      {{-- Action will be set dynamically in JS --}}
                      x-ref="editTaskForm"
                       @submit.prevent="
                           isLoading = true;
                           formErrors = {}; // Clear errors before submit
                           let formData = new FormData($refs.editTaskForm);
                           formData.append('_method', 'PUT'); // Add method spoofing

                           fetch('/tasks/' + currentTask.id, { // Use currentTask.id for URL
                               method: 'POST', // HTML forms only support GET/POST
                               body: formData,
                               headers: {
                                   'X-Requested-With': 'XMLHttpRequest',
                                   'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                   'Accept': 'application/json'
                               }
                           })
                           .then(response => response.json().then(data => ({ status: response.status, body: data }))) // Process JSON response and status
                           .then(({ status, body }) => {
                               if (status === 200 && body.success) { // Check for success status and flag
                                   // Update the task card in the Kanban board
                                   const existingTaskCard = document.querySelector(`.task[data-id='${currentTask.id}']`);
                                   if (existingTaskCard && body.taskHtml) {
                                       const tempDiv = document.createElement('div');
                                       tempDiv.innerHTML = body.taskHtml.trim();
                                       const newTaskCard = tempDiv.firstChild;

                                       // Re-attach listeners might be needed if events are complex
                                        // For simplicity, replacing works, but listeners might need re-init via observer in kanban.index.js
                                       existingTaskCard.parentNode.replaceChild(newTaskCard, existingTaskCard);
                                   } else {
                                       console.warn('Could not find task card or HTML to update:', currentTask.id);
                                       // Optionally, trigger a full page refresh or Kanban refresh
                                   }

                                   // Close the modal
                                   editTaskModal = false;
                                   currentTask = null; // Reset task data
                                    // Show success message from main kanban script
                                   window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: body.message || 'Task updated successfully!', success: true } }));


                               } else {
                                   // Handle errors (Validation or other server errors)
                                   console.error('Update Task Error Response:', body);
                                   if (status === 422 && body.errors) {
                                       formErrors = body.errors; // Display validation errors
                                        window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: body.message || 'Validation errors.', success: false } }));
                                   } else {
                                       alert('Failed to update task: ' + (body.message || 'Server error'));
                                       window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: body.message || 'Failed to update task.', success: false } }));
                                   }
                               }
                           })
                           .catch(error => {
                               console.error('Fetch Error:', error);
                               alert('An error occurred: ' + error.message);
                                window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: 'Network error during update.', success: false } }));
                           })
                           .finally(() => {
                               isLoading = false;
                           });
                      "
                      >
                    @csrf
                    {{-- @method('PUT') will be added via formData --}}

                    {{-- Form Fields --}}
                    <div class="mt-6 space-y-4">
                        {{-- Task Title --}}
                        <div >
                            <label for="edit_title" class="block text-sm font-medium text-gray-700">Task Name:</label>
                            <input type="text" name="title" id="edit_title"
                                   x-model="currentTask.title"
                                   :class="{ 'border-red-500': formErrors.title }"
                                   class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                             <template x-if="formErrors.title">
                                <p class="mt-1 text-xs text-red-600" x-text="formErrors.title[0]"></p>
                            </template>
                        </div>

                        {{-- Task Description --}}
                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700">Description:</label>
                            <textarea name="description" id="edit_description" rows="3"
                                      x-model="currentTask.description"
                                      class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                         {{-- Grid for side-by-side inputs --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Difficulty Level --}}
                            <div>
                                <label for="edit_difficulty_level_id" class="block text-sm font-medium text-gray-700">Tingkat Kesulitan:</label>
                                <select name="difficulty_level_id" id="edit_difficulty_level_id"
                                        x-model="currentTask.difficulty_level_id" {{-- Bind to ID --}}
                                         :class="{ 'border-red-500': formErrors.difficulty_level_id }"
                                        class="mt-1 shadow-sm border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Pilih Tingkat Kesulitan</option>
                                    {{-- Check if $difficultyLevels is available --}}
                                    @isset($difficultyLevels)
                                        @foreach($difficultyLevels as $level)
                                            <option value="{{ $level->id }}">{{ $level->name }} ({{ $level->value }})</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Level data missing</option>
                                    @endisset
                                </select>
                                <template x-if="formErrors.difficulty_level_id">
                                    <p class="mt-1 text-xs text-red-600" x-text="formErrors.difficulty_level_id[0]"></p>
                                </template>
                            </div>

                            {{-- Priority Level --}}
                            <div>
                                <label for="edit_priority_level_id" class="block text-sm font-medium text-gray-700">Prioritas:</label>
                                <select name="priority_level_id" id="edit_priority_level_id"
                                        x-model="currentTask.priority_level_id" {{-- Bind to ID --}}
                                         :class="{ 'border-red-500': formErrors.priority_level_id }"
                                        class="mt-1 shadow-sm border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Pilih Prioritas</option>
                                     {{-- Check if $priorityLevels is available --}}
                                     @isset($priorityLevels)
                                        @foreach($priorityLevels as $level)
                                            <option value="{{ $level->id }}">{{ $level->name }} ({{ $level->value }})</option>
                                        @endforeach
                                    @else
                                         <option value="" disabled>Level data missing</option>
                                    @endisset
                                </select>
                                 <template x-if="formErrors.priority_level_id">
                                    <p class="mt-1 text-xs text-red-600" x-text="formErrors.priority_level_id[0]"></p>
                                </template>
                            </div>

                             {{-- Start Time --}}
                             <div>
                                 <label for="edit_start_time" class="block text-sm font-medium text-gray-700">Start Date:</label>
                                 <input type="date" name="start_time" id="edit_start_time"
                                        {{-- Bind formatted date to input --}}
                                        :value="currentTask && currentTask.start_time ? new Date(currentTask.start_time).toISOString().split('T')[0] : ''"
                                        @change="currentTask.start_time = $event.target.value" {{-- Update Alpine model on change --}}
                                         :class="{ 'border-red-500': formErrors.start_time }"
                                        class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                  <template x-if="formErrors.start_time">
                                    <p class="mt-1 text-xs text-red-600" x-text="formErrors.start_time[0]"></p>
                                </template>
                             </div>

                             {{-- End Time --}}
                             <div>
                                 <label for="edit_end_time" class="block text-sm font-medium text-gray-700">End Date:</label>
                                 <input type="date" name="end_time" id="edit_end_time"
                                        {{-- Bind formatted date to input --}}
                                        :value="currentTask && currentTask.end_time ? new Date(currentTask.end_time).toISOString().split('T')[0] : ''"
                                        @change="currentTask.end_time = $event.target.value" {{-- Update Alpine model on change --}}
                                         :class="{ 'border-red-500': formErrors.end_time }"
                                        class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        required>
                                 <template x-if="formErrors.end_time">
                                    <p class="mt-1 text-xs text-red-600" x-text="formErrors.end_time[0]"></p>
                                </template>
                             </div>
                        </div> {{-- End Grid --}}

                        {{-- Assigned User --}}
                        <div>
                            <label for="edit_assigned_to" class="block text-sm font-medium text-gray-700">Assign User:</label>
                            <select name="assigned_to" id="edit_assigned_to"
                                    x-model="currentTask.assigned_to"
                                     :class="{ 'border-red-500': formErrors.assigned_to }"
                                    class="mt-1 shadow-sm border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                <option value="">Pilih User</option>
                                @isset($users)
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>User data missing</option>
                                @endisset
                            </select>
                             <template x-if="formErrors.assigned_to">
                                <p class="mt-1 text-xs text-red-600" x-text="formErrors.assigned_to[0]"></p>
                            </template>
                        </div>

                         {{-- Achievement Percentage (optional) --}}
                          <div>
                              <label for="edit_achievement_percentage" class="block text-sm font-medium text-gray-700">Pencapaian (%):</label>
                              <input type="number" name="achievement_percentage" id="edit_achievement_percentage" min="0" max="100"
                                     x-model.number="currentTask.achievement_percentage" {{-- Assume achievement is stored if needed --}}
                                      :class="{ 'border-red-500': formErrors.achievement_percentage }"
                                     class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                              <template x-if="formErrors.achievement_percentage">
                                    <p class="mt-1 text-xs text-red-600" x-text="formErrors.achievement_percentage[0]"></p>
                                </template>
                          </div>

                    </div> {{-- End Form Fields --}}

                    {{-- Modal Footer / Submit Button --}}
                    <div class="mt-8 pt-5 border-t border-gray-200 sm:flex sm:items-center sm:justify-end">
                        <button type="button" @click="editTaskModal = false"
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isLoading"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                             <span x-show="!isLoading">Update Task</span>
                             <span x-show="isLoading">Updating...</span>
                        </button>
                    </div>
                </form> {{-- End Form --}}

            </div> {{-- End Modal Content --}}
        </div> {{-- End Modal Centering --}}
    </div> {{-- End Modal Wrapper --}}
</div> {{-- End Alpine Component --}}