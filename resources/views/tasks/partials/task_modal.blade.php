@props([
    'project',
    'users',
    'difficultyLevels',
    'priorityLevels'
])

<div x-data="taskModalData({
        projectId: {{ $project->id }},
        users: {{ Js::from($users->map->only(['id', 'name'])) }},
        difficultyLevels: {{ Js::from($difficultyLevels->map->only(['id', 'name', 'value', 'color', 'display_order'])) }},
        priorityLevels: {{ Js::from($priorityLevels->map->only(['id', 'name', 'value', 'color', 'display_order'])) }},
        csrfToken: '{{ csrf_token() }}'
     })"
     x-show="showModal"
     x-on:open-task-modal.window="openModal($event.detail)"
     x-on:keydown.escape.window="closeModal()"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="task-modal-title" role="dialog" aria-modal="true"
     style="display: none;">

    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Overlay --}}
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-60"
             @click="closeModal()" aria-hidden="true">
        </div>

        {{-- Modal Content --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div @click.stop
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-4xl xl:max-w-5xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">

            {{-- Loading Overlay --}}
            <div x-show="isLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            {{-- Form --}}
            <form @submit.prevent="submitTask" x-ref="taskForm">
                @csrf
                {{-- Method spoofing for update handled in Alpine submit --}}
                <input type="hidden" name="project_id" :value="task.project_id">
                <input type="hidden" name="status" :value="task.status"> {{-- For initial status --}}

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center min-w-0">
                        {{-- Status Indicator --}}
                        <span x-show="task.status" class="inline-block px-2 py-0.5 mr-3 rounded text-xs font-medium capitalize"
                              :class="{
                                'bg-red-100 text-red-800': task.status === 'To Do',
                                'bg-yellow-100 text-yellow-800': task.status === 'In Progress',
                                'bg-blue-100 text-blue-800': task.status === 'Review',
                                'bg-green-100 text-green-800': task.status === 'Done',
                                'bg-gray-100 text-gray-800': !['To Do', 'In Progress', 'Review', 'Done'].includes(task.status)
                              }"
                              x-text="task.status">
                        </span>

                         {{-- Task Title Input --}}
                        <input type="text" name="title" id="task_title" required placeholder="Task Title"
                               x-model="task.title"
                               class="text-lg font-medium text-gray-900 bg-transparent border-none p-0 focus:ring-0 w-full truncate">
                    </div>
                    <div class="flex items-center space-x-2">
                        {{-- More Options Dropdown (Placeholder) --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="p-1 text-gray-500 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 10a2 2 0 110-4 2 2 0 010 4zM10 14a2 2 0 110-4 2 2 0 010 4z" /></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-20" style="display:none;">
                                {{-- Add actions like Duplicate, Watch, etc. here later --}}
                                <a href="#" @click.prevent="deleteTask()" x-show="isEditMode" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete Task</a>
                            </div>
                        </div>
                        {{-- Close Button --}}
                        <button @click="closeModal()" type="button" class="p-1 text-gray-500 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                {{-- Validation Errors --}}
                 <template x-if="Object.keys(formErrors).length > 0">
                     <div class="m-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
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

                {{-- Main Content Area --}}
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        {{-- Left Column (Description, Comments/History) --}}
                        <div class="lg:col-span-2 space-y-6">
                            {{-- Description --}}
                            <div>
                                <label for="task_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="task_description" rows="4"
                                          x-model="task.description"
                                          placeholder="Add a more detailed description..."
                                          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>

                             {{-- Comments / History Tabs --}}
                            <div>
                                <div class="border-b border-gray-200 mb-3">
                                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                                        <button type="button" @click="setActiveTab('comments')"
                                                :class="activeTab === 'comments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm focus:outline-none">
                                            Comments <span x-show="comments.length > 0" x-text="'(' + comments.length + ')'" class="ml-1 text-xs"></span>
                                        </button>
                                        <button type="button" @click="setActiveTab('history')"
                                                :class="activeTab === 'history' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm focus:outline-none">
                                            History <span x-show="historyLogs.length > 0" x-text="'(' + historyLogs.length + ')'" class="ml-1 text-xs"></span>
                                        </button>
                                    </nav>
                                </div>

                                {{-- Comments Section --}}
                                <div x-show="activeTab === 'comments'" x-transition>
                                    {{-- Add Comment Form --}}
                                     <div class="mb-4 flex items-start space-x-3">
                                         <div class="flex-shrink-0">
                                             {{-- Placeholder for User Avatar --}}
                                              <span class="inline-block h-8 w-8 rounded-full overflow-hidden bg-gray-200">
                                                  <svg class="h-full w-full text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                      <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                  </svg>
                                              </span>
                                         </div>
                                         <div class="min-w-0 flex-1">
                                             <textarea x-model="newComment" rows="2" placeholder="Add a comment..."
                                                       class="shadow-sm block w-full focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border border-gray-300 rounded-md"></textarea>
                                             <div class="mt-2 flex justify-end">
                                                 <button @click.prevent="addComment" type="button" :disabled="!newComment.trim() || isSubmittingComment"
                                                         class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                                    <span x-show="!isSubmittingComment">Comment</span>
                                                    <span x-show="isSubmittingComment">Saving...</span>
                                                 </button>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- Comment List --}}
                                    <div class="space-y-4 max-h-60 overflow-y-auto pr-2">
                                        <template x-if="comments.length === 0 && !isLoading">
                                            <p class="text-sm text-gray-500">No comments yet.</p>
                                        </template>
                                        <template x-for="comment in comments" :key="comment.id">
                                             <div class="flex items-start space-x-3">
                                                  <div class="flex-shrink-0">
                                                      {{-- Placeholder Avatar --}}
                                                       <span class="inline-block h-8 w-8 rounded-full overflow-hidden bg-gray-200">
                                                            <svg class="h-full w-full text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                                       </span>
                                                  </div>
                                                  <div class="min-w-0 flex-1 bg-gray-50 p-3 rounded-md">
                                                      <div class="text-sm flex justify-between items-center">
                                                          <span class="font-medium text-gray-900" x-text="comment.user?.name || 'User'"></span>
                                                          <span class="text-gray-500" x-text="timeAgo(comment.created_at)"></span>
                                                      </div>
                                                      <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap" x-text="comment.comment"></p>
                                                      {{-- Optional: Delete comment button --}}
                                                  </div>
                                             </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- History Section --}}
                                <div x-show="activeTab === 'history'" x-transition>
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                         <template x-if="historyLogs.length === 0 && !isLoading">
                                             <p class="text-sm text-gray-500">No history found for this task.</p>
                                         </template>
                                         <template x-for="log in historyLogs" :key="log.id">
                                             <div class="flex items-center space-x-3 text-sm">
                                                  <div class="flex-shrink-0">
                                                       <span class="inline-block h-6 w-6 rounded-full overflow-hidden bg-gray-200"> <svg class="h-full w-full text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg> </span>
                                                  </div>
                                                  <div class="flex-1 text-gray-600">
                                                      <span class="font-medium text-gray-800" x-text="log.user?.name || 'System'"></span>
                                                      <span x-text="renderLogDescription(log)"></span>
                                                       <span class="text-gray-400 ml-1" x-text="timeAgo(log.created_at)"></span>
                                                  </div>
                                             </div>
                                         </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column (Details & Attachments) --}}
                        <div class="lg:col-span-1 space-y-5">
                            {{-- Details Section --}}
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <label for="task_assigned_to" class="font-medium text-gray-600">Assignee</label>
                                    <select name="assigned_to" id="task_assigned_to" required x-model="task.assigned_to"
                                            class="mt-1 block w-1/2 py-1 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select User</option>
                                        <template x-for="user in users" :key="user.id">
                                            <option :value="user.id" x-text="user.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="flex justify-between items-center">
                                    <label for="task_difficulty_level_id" class="font-medium text-gray-600">Difficulty</label>
                                    <select name="difficulty_level_id" id="task_difficulty_level_id" x-model="task.difficulty_level_id"
                                            class="mt-1 block w-1/2 py-1 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select Difficulty</option>
                                        <template x-for="level in getSortedLevels('difficulty')" :key="level.id">
                                            <option :value="level.id" 
                                                    :style="`color: ${level.color};`" 
                                                    x-text="level.name + ' (' + level.value + ')'"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="flex justify-between items-center">
                                    <label for="task_priority_level_id" class="font-medium text-gray-600">Priority</label>
                                    <select name="priority_level_id" id="task_priority_level_id" x-model="task.priority_level_id"
                                            class="mt-1 block w-1/2 py-1 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select Priority</option>
                                        <template x-for="level in getSortedLevels('priority')" :key="level.id">
                                            <option :value="level.id" 
                                                    :style="`color: ${level.color}; background-color:`" 
                                                    x-text="level.name + ' (' + level.value + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="flex justify-between items-center">
                                    <label for="task_start_time" class="font-medium text-gray-600">Start Date</label>
                                    <input type="date" name="start_time" id="task_start_time"
                                           :value="formatDateForInput(task.start_time)"
                                           @change="task.start_time = $event.target.value"
                                           class="mt-1 block w-1/2 py-1 px-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="flex justify-between items-center">
                                    <label for="task_end_time" class="font-medium text-gray-600">Due Date</label>
                                    <input type="date" name="end_time" id="task_end_time"
                                           :value="formatDateForInput(task.end_time)"
                                           @change="task.end_time = $event.target.value"
                                           class="mt-1 block w-1/2 py-1 px-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="flex justify-between items-center">
                                    <label for="task_achievement_percentage" class="font-medium text-gray-600">Achievement (%)</label>
                                    <div class="w-1/2 flex items-center space-x-2">
                                        <input type="range" min="0" max="100" step="5" name="achievement_percentage" id="task_achievement_percentage"
                                               x-model.number="task.achievement_percentage"
                                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                        <span class="text-sm text-gray-700 w-8 text-right" x-text="task.achievement_percentage + '%'"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Attachments Section --}}
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-medium text-gray-800 mb-2">Attachments</h3>
                                <div class="space-y-2 max-h-40 overflow-y-auto pr-2 mb-3">
                                    <template x-if="attachments.length === 0 && !isLoading">
                                        <p class="text-xs text-gray-500">No attachments added.</p>
                                    </template>
                                    <template x-for="attachment in attachments" :key="attachment.id">
                                        <div class="flex items-center justify-between group bg-gray-50 p-2 rounded">
                                            <div class="flex items-center min-w-0 space-x-2">
                                                {{-- Icon Placeholder --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a3 3 0 006 0V7a1 1 0 112 0v4a5 5 0 01-10 0V7a3 3 0 013-3h1z" clip-rule="evenodd" /></svg>
                                                <div class="min-w-0">
                                                    <a :href="attachment.url" target="_blank" class="text-xs font-medium text-indigo-600 hover:underline truncate block" x-text="attachment.file_name"></a>
                                                    <span class="text-xs text-gray-500" x-text="`${attachment.formatted_size} - Added by ${attachment.user?.name || 'User'} ${timeAgo(attachment.created_at)}`"></span>
                                                </div>
                                            </div>
                                            <button @click.prevent="deleteAttachment(attachment.id)" type="button"
                                                    class="opacity-0 group-hover:opacity-100 p-0.5 text-red-500 hover:text-red-700 focus:outline-none transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                {{-- Upload Input --}}
                                <div class="mt-2">
                                    <label for="task_attachment_upload" class="cursor-pointer inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        Upload File
                                    </label>
                                    <input type="file" id="task_attachment_upload" multiple @change="handleFileUpload($event)" class="sr-only">
                                    <span class="ml-2 text-xs text-gray-500" x-show="uploadProgress > 0 && uploadProgress < 100" x-text="`Uploading: ${uploadProgress}%`"></span>
                                    <span class="ml-2 text-xs text-red-500" x-show="uploadError" x-text="uploadError"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer / Submit --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 sm:flex sm:flex-row-reverse">
                    <button type="submit" :disabled="isLoading"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!isLoading" x-text="isEditMode ? 'Update Task' : 'Create Task'"></span>
                        <span x-show="isLoading">Saving...</span>
                    </button>
                    <button @click="closeModal()" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- AlpineJS Data and Methods --}}
<script>
    function taskModalData(initData) {
        return {
            showModal: false,
            isLoading: false,
            isEditMode: false,
            isSubmittingComment: false,
            task: {}, // Holds current task data
            comments: [],
            historyLogs: [],
            attachments: [],
            newComment: '',
            activeTab: 'comments', // 'comments' or 'history'
            formErrors: {},
            uploadProgress: 0,
            uploadError: '',
            // Initial static data passed from Blade
            projectId: initData.projectId,
            users: initData.users,
            difficultyLevels: initData.difficultyLevels,
            priorityLevels: initData.priorityLevels,
            csrfToken: initData.csrfToken,
            
            // Get sorted levels based on display_order
            getSortedLevels(type) {
                if (type === 'difficulty') {
                    return [...this.difficultyLevels].sort((a, b) => a.display_order - b.display_order);
                } else if (type === 'priority') {
                    return [...this.priorityLevels].sort((a, b) => a.display_order - b.display_order);
                }
                return [];
            },

            // Methods
            openModal(detail) {
                this.resetModal();
                this.isLoading = true;
                this.showModal = true;
                if (detail.taskId) { // Editing existing task
                    this.isEditMode = true;
                    this.fetchTaskData(detail.taskId);
                } else { // Creating new task
                    this.isEditMode = false;
                     this.task = { // Default values for new task
                         id: null,
                         title: '',
                         description: '',
                         status: detail.status || 'To Do', // Use status from button click
                         project_id: this.projectId,
                         assigned_to: '{{ auth()->id() }}', // Default to logged-in user
                         difficulty_level_id: '',
                         priority_level_id: '',
                         start_time: null,
                         end_time: null,
                         achievement_percentage: 0, // Default percentage
                     };
                    this.isLoading = false; // No data to fetch for new task initially
                }
                 // Auto focus title maybe?
                 // setTimeout(() => this.$refs.taskForm.querySelector('#task_title').focus(), 100);
            },

            fetchTaskData(taskId) {
                // Fetch FULL task details including comments, attachments, history logs
                 fetch(`/tasks/${taskId}/details`) // NEW ROUTE NEEDED: returns JSON with task, comments, attachments, history
                    .then(response => {
                         if (!response.ok) throw new Error('Network response was not ok');
                         return response.json();
                    })
                    .then(data => {
                        this.task = data.task;
                         // Ensure percentage is number
                         this.task.achievement_percentage = parseInt(this.task.achievement_percentage || 0);
                        this.comments = data.comments || [];
                        this.historyLogs = data.history || [];
                        this.attachments = data.attachments || [];
                        this.setActiveTab('comments'); // Default to comments
                    })
                    .catch(error => {
                        console.error('Error fetching task details:', error);
                        alert('Failed to load task details. Please try again.');
                        this.closeModal();
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            },

            closeModal() {
                this.showModal = false;
                // Delay reset to allow transition out
                setTimeout(() => this.resetModal(), 300);
            },

            resetModal() {
                this.isLoading = false;
                this.isEditMode = false;
                this.isSubmittingComment = false;
                this.task = {};
                this.comments = [];
                this.historyLogs = [];
                this.attachments = [];
                this.newComment = '';
                this.activeTab = 'comments';
                this.formErrors = {};
                this.uploadProgress = 0;
                this.uploadError = '';
            },

            setActiveTab(tabName) {
                this.activeTab = tabName;
                 // Optionally fetch history only when tab is clicked if not loaded initially
                 // if (tabName === 'history' && this.isEditMode && this.historyLogs.length === 0) {
                 //     this.fetchHistory();
                 // }
            },

            addComment() {
                if (!this.newComment.trim() || !this.isEditMode) return;
                this.isSubmittingComment = true;
                 fetch(`/tasks/${this.task.id}/comments`, { // NEW ROUTE NEEDED
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': this.csrfToken
                     },
                     body: JSON.stringify({ comment: this.newComment })
                 })
                 .then(response => response.json())
                 .then(data => {
                     if(data.success) {
                         this.comments.unshift(data.comment); // Add to top
                         this.newComment = '';
                          // Dispatch event to update comment count on card maybe?
                     } else {
                         alert('Failed to add comment: ' + (data.message || 'Server error'));
                     }
                 })
                 .catch(error => {
                     console.error('Error adding comment:', error);
                     alert('Error adding comment.');
                 })
                 .finally(() => {
                     this.isSubmittingComment = false;
                 });
            },

             fetchHistory() { // If loading history on demand
                 if (!this.isEditMode) return;
                 this.isLoading = true; // Show loading indicator for history tab
                 fetch(`/tasks/${this.task.id}/history`) // NEW ROUTE NEEDED
                     .then(response => response.json())
                     .then(data => {
                         this.historyLogs = data.history || [];
                     })
                     .catch(error => console.error('Error fetching history:', error))
                     .finally(() => this.isLoading = false);
             },

             handleFileUpload(event) {
                if (!this.task.id) {
                    alert("Please save the task before adding attachments.");
                    event.target.value = null; // Clear file input
                    return;
                }
                const files = event.target.files;
                if (!files.length) return;

                this.uploadError = '';
                this.uploadProgress = 0;
                const formData = new FormData();
                 for (let i = 0; i < files.length; i++) {
                     formData.append('attachments[]', files[i]);
                 }

                 // Use XMLHttpRequest for progress tracking
                 const xhr = new XMLHttpRequest();
                 xhr.open('POST', `/tasks/${this.task.id}/attachments`, true); // NEW ROUTE NEEDED
                 xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
                 xhr.setRequestHeader('Accept', 'application/json');

                 xhr.upload.onprogress = (event) => {
                     if (event.lengthComputable) {
                         this.uploadProgress = Math.round((event.loaded / event.total) * 100);
                     }
                 };

                 xhr.onload = () => {
                     this.uploadProgress = 0; // Reset progress
                     event.target.value = null; // Clear file input
                     if (xhr.status >= 200 && xhr.status < 300) {
                         try {
                             const data = JSON.parse(xhr.responseText);
                             if (data.success && data.attachments) {
                                data.attachments.forEach(att => this.attachments.unshift(att));
                                 // Dispatch event to update attachment count on card
                                 window.dispatchEvent(new CustomEvent('task-updated', { detail: { taskId: this.task.id } }));
                             } else {
                                 this.uploadError = data.message || 'Upload failed.';
                             }
                         } catch (e) {
                              console.error("Error parsing upload response:", e, xhr.responseText);
                             this.uploadError = 'Error processing server response.';
                         }
                     } else {
                         console.error("Upload failed:", xhr.status, xhr.responseText);
                         try {
                              const errorData = JSON.parse(xhr.responseText);
                              this.uploadError = errorData.message || `Upload failed (${xhr.status})`;
                         } catch (e) {
                              this.uploadError = `Upload failed (${xhr.status})`;
                         }
                     }
                 };

                 xhr.onerror = () => {
                     this.uploadProgress = 0;
                     event.target.value = null;
                     this.uploadError = 'Network error during upload.';
                     console.error('Upload network error');
                 };

                 xhr.send(formData);
            },

             deleteAttachment(attachmentId) {
                 if (!confirm('Are you sure you want to delete this attachment?')) return;

                 fetch(`/tasks/${this.task.id}/attachments/${attachmentId}`, { // NEW ROUTE NEEDED
                     method: 'DELETE',
                     headers: {
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': this.csrfToken
                     }
                 })
                 .then(response => response.json())
                 .then(data => {
                     if(data.success) {
                         this.attachments = this.attachments.filter(att => att.id !== attachmentId);
                          // Dispatch event to update attachment count on card
                          window.dispatchEvent(new CustomEvent('task-updated', { detail: { taskId: this.task.id } }));
                     } else {
                         alert('Failed to delete attachment: ' + (data.message || 'Server error'));
                     }
                 })
                 .catch(error => {
                     console.error('Error deleting attachment:', error);
                     alert('Error deleting attachment.');
                 });
            },

            submitTask() {
                this.isLoading = true;
                this.formErrors = {};
                let url = this.isEditMode ? `/tasks/${this.task.id}` : '/tasks';
                let method = this.isEditMode ? 'POST' : 'POST'; // Use POST for both, PUT via _method

                // Create FormData from the form
                let formData = new FormData(this.$refs.taskForm);

                // Append _method for PUT request if editing
                if (this.isEditMode) {
                    formData.append('_method', 'PUT');
                }

                // Append potentially missing data not directly in form inputs if needed
                // Example: if status dropdown was added: formData.append('status', this.task.status);

                fetch(url, {
                    method: method,
                    body: formData, // Send FormData
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                         // 'Content-Type' is set automatically by browser for FormData
                        'X-Requested-With': 'XMLHttpRequest' // Important for Laravel request()->ajax()
                    }
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(({ status, body }) => {
                    if (status === 200 && body.success) {
                        // Use the existing event dispatch from the original code
                         window.dispatchEvent(new CustomEvent('task-form-success', {
                             detail: {
                                 isEdit: this.isEditMode,
                                 task: body.task, // Send back the full task object
                                 taskHtml: body.taskHtml // Send back the rendered HTML
                             }
                         }));
                        this.closeModal();
                        window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: body.message || `Task ${this.isEditMode ? 'updated' : 'created'}!`, success: true } }));

                    } else {
                        console.error('Task Submit Error Response:', body);
                        if (status === 422 && body.errors) {
                            this.formErrors = body.errors;
                            window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: body.message || 'Validation errors.', success: false } }));
                        } else {
                             alert(`Failed to ${this.isEditMode ? 'update' : 'create'} task: ` + (body.message || 'Server error'));
                            window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: body.message || `Failed to ${this.isEditMode ? 'update' : 'create'} task.`, success: false } }));
                        }
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('An error occurred: ' + error.message);
                     window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: `Network error ${this.isEditMode ? 'updating' : 'creating'} task.`, success: false } }));
                })
                .finally(() => {
                    this.isLoading = false;
                });
            },

            deleteTask() {
                if (!this.isEditMode || !this.task.id) return;
                 if (confirm('Are you sure you want to delete this task? This cannot be undone.')) {
                    // Use the existing delete logic via event dispatch if preferred,
                    // or implement direct fetch delete here.
                    // Using fetch for consistency within the modal:
                    this.isLoading = true;
                     fetch(`/tasks/${this.task.id}`, {
                         method: 'POST', // Use POST for method spoofing
                         headers: {
                             'X-CSRF-TOKEN': this.csrfToken,
                             'Content-Type': 'application/json',
                             'Accept': 'application/json'
                         },
                         body: JSON.stringify({ _method: 'DELETE' })
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                            // Find the card and remove it (similar to original delete logic)
                             const taskCard = document.querySelector(`.task[data-id="${this.task.id}"]`);
                             if (taskCard) {
                                 taskCard.remove();
                                 // Trigger count update via existing mechanism if available
                                 // Or dispatch a new event if needed: window.dispatchEvent(new Event('update-task-counts'));
                             }
                             this.closeModal();
                              window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: 'Task deleted successfully.', success: true } }));
                         } else {
                             alert('Error deleting task: ' + (data.message || 'Unknown error'));
                              window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: data.message || 'Failed to delete task.', success: false } }));
                         }
                     })
                     .catch(error => {
                         console.error('Error deleting task:', error);
                         alert('Network error deleting task.');
                          window.dispatchEvent(new CustomEvent('show-status-message', { detail: { message: 'Network error deleting task.', success: false } }));
                     })
                    .finally(() => {
                        this.isLoading = false;
                     });
                 }
            },

             // Helper functions
             formatDateForInput(dateString) {
                 if (!dateString) return '';
                 try {
                     // Handles both YYYY-MM-DD HH:MM:SS and ISO strings
                     return new Date(dateString).toISOString().split('T')[0];
                 } catch (e) {
                     console.warn("Could not parse date:", dateString);
                     return '';
                 }
             },
             timeAgo(dateString) {
                 if (!dateString) return '';
                 const date = new Date(dateString);
                 const now = new Date();
                 const seconds = Math.round((now - date) / 1000);
                 const minutes = Math.round(seconds / 60);
                 const hours = Math.round(minutes / 60);
                 const days = Math.round(hours / 24);
                 const weeks = Math.round(days / 7);
                 const months = Math.round(days / 30);
                 const years = Math.round(days / 365);

                 if (seconds < 60) return seconds + 's ago';
                 if (minutes < 60) return minutes + 'm ago';
                 if (hours < 24) return hours + 'h ago';
                 if (days < 7) return days + 'd ago';
                 if (weeks < 4) return weeks + 'w ago';
                 if (months < 12) return months + 'mo ago';
                 return years + 'y ago';
             },
             renderLogDescription(log) {
                 // Customize how log descriptions are shown
                 let desc = log.description || `performed action: ${log.action}`;
                 // Example: Make specific actions more readable
                 if (log.action === 'status_changed' && log.properties?.old_status && log.properties?.new_status) {
                     desc = `changed status from "${log.properties.old_status}" to "${log.properties.new_status}"`;
                 } else if (log.action === 'created') {
                     desc = 'created this task';
                 } else if (log.action === 'updated' && log.properties?.changed) {
                     // Basic display of changed fields
                     const changedFields = Object.keys(log.properties.changed).join(', ');
                     desc = `updated field(s): ${changedFields}`;
                 }
                 // Add more specific formatting based on your log actions/properties
                 return desc;
             }
        }
    }
</script>