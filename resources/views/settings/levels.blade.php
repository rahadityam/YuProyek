<x-app-layout>
    {{-- AlpineJS Data Setup --}}
    <div x-data="levelSettings({
            project: {{ json_encode($project->only('id')) }},
            initialDifficultyLevels: {{ json_encode($difficultyLevels) }},
            initialPriorityLevels: {{ json_encode($priorityLevels) }},
            updateOrderUrl: '{{ route('projects.settings.levels.order', $project) }}',
            csrfToken: '{{ csrf_token() }}'
        })"
         class="py-6 px-4 sm:px-6 lg:px-8">

         {{-- Header & Flash Messages --}}
          <div class="mb-6 flex justify-between items-center">
              <h2 class="text-2xl font-semibold text-gray-900">Pengaturan Level - {{ $project->name }}</h2>
              <a href="{{ route('projects.pengaturan', $project) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                   <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                   Kembali
              </a>
          </div>
          {{-- Status message display --}}
          <template x-if="statusMessage">
              <div x-text="statusMessage"
                   :class="isSuccess ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'"
                   class="mb-4 border px-4 py-3 rounded relative" role="alert"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="opacity-0"
                   x-transition:enter-end="opacity-100"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="opacity-100"
                   x-transition:leave-end="opacity-0">
              </div>
          </template>
         {{-- Display Blade validation errors if they exist (e.g., non-AJAX fallback) --}}
          @if($errors->any())
              <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                   <p><strong>Error!</strong> Periksa inputan Anda.</p>
                   <ul>@foreach ($errors->all() as $error) <li>-> {{ $error }}</li> @endforeach</ul>
              </div>
          @endif
         <p class="text-sm text-gray-600 mb-6">Atur nama, nilai numerik, warna, dan urutan untuk Tingkat Kesulitan dan Prioritas.</p>

        {{-- Grid Layout --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Difficulty Levels Section --}}
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-medium text-gray-800">Tingkat Kesulitan</h3>
                    <button @click="openModal('difficulty')"
                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                        Tambah
                    </button>
                </div>
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul x-ref="difficultyList" role="list" class="divide-y divide-gray-200">
                        <template x-for="level in difficultyLevels" :key="level.id">
                            <li :data-id="level.id" class="px-4 py-3 sm:px-6 flex justify-between items-center group hover:bg-gray-50 cursor-grab"> {{-- Hover effect --}}
                                <div class="flex items-center flex-grow min-w-0"> {{-- Allow text to wrap --}}
                                     <svg class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                                     <span class="inline-block w-4 h-4 rounded-full mr-3 border border-gray-300 flex-shrink-0" :style="{ backgroundColor: level.color }"></span>
                                     <div class="min-w-0"> {{-- Wrap text --}}
                                        <span class="font-medium text-gray-900 block truncate" x-text="level.name"></span>
                                        <span class="text-xs text-gray-500" x-text="'Nilai: ' + level.value"></span>
                                     </div>
                                </div>
                                <div class="flex space-x-2 flex-shrink-0 ml-4">
                                    <button @click="editLevel('difficulty', level)"
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                    {{-- ============================================= --}}
                                    {{-- PERBAIKAN: Form Delete hanya trigger JS --}}
                                    {{-- ============================================= --}}
                                    <form @submit.prevent="deleteLevel($event, 'difficulty', level.id)">
                                        @csrf {{-- Tetap perlu untuk JS fetch header --}}
                                        {{-- Tidak perlu @method('DELETE') atau action --}}
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>
                                    </form>
                                    {{-- ============================================= --}}
                                </div>
                            </li>
                        </template>
                        <template x-if="difficultyLevels.length === 0">
                            <li class="px-4 py-4 sm:px-6 text-center text-sm text-gray-500">Belum ada level kesulitan.</li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Priority Levels Section (Struktur sama) --}}
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-medium text-gray-800">Prioritas</h3>
                    <button @click="openModal('priority')"
                             class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                        Tambah
                    </button>
                </div>
                 <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul x-ref="priorityList" role="list" class="divide-y divide-gray-200">
                        <template x-for="level in priorityLevels" :key="level.id">
                            <li :data-id="level.id" class="px-4 py-3 sm:px-6 flex justify-between items-center group hover:bg-gray-50 cursor-grab">
                                <div class="flex items-center flex-grow min-w-0">
                                     <svg class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                                     <span class="inline-block w-4 h-4 rounded-full mr-3 border border-gray-300 flex-shrink-0" :style="{ backgroundColor: level.color }"></span>
                                     <div class="min-w-0">
                                        <span class="font-medium text-gray-900 block truncate" x-text="level.name"></span>
                                        <span class="text-xs text-gray-500" x-text="'Nilai: ' + level.value"></span>
                                     </div>
                                </div>
                                <div class="flex space-x-2 flex-shrink-0 ml-4">
                                    <button @click="editLevel('priority', level)"
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</button>
                                    {{-- ============================================= --}}
                                    {{-- PERBAIKAN: Form Delete hanya trigger JS --}}
                                    {{-- ============================================= --}}
                                    <form @submit.prevent="deleteLevel($event, 'priority', level.id)">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>
                                    </form>
                                    {{-- ============================================= --}}
                                </div>
                            </li>
                        </template>
                         <template x-if="priorityLevels.length === 0">
                             <li class="px-4 py-4 sm:px-6 text-center text-sm text-gray-500">Belum ada level prioritas.</li>
                         </template>
                    </ul>
                 </div>
            </div>
        </div>

        {{-- Modal for Add/Edit Level (Kode Modal tetap sama seperti sebelumnya) --}}
        <div x-show="isModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true"
             >
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()" aria-hidden="true"></div>

                {{-- Modal Panel --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitLevelForm">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start w-full">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"
                                        x-text="levelToEdit ? 'Edit Level ' + modalTypeLabel : 'Tambah Level ' + modalTypeLabel">
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        {{-- Name Input --}}
                                        <div>
                                            <label for="level-name" class="block text-sm font-medium text-gray-700">Nama Level</label>
                                            <input type="text" name="name" id="level-name" x-model="currentLevel.name" required maxlength="255"
                                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <template x-if="formErrors.name"><p class="mt-1 text-xs text-red-600" x-text="formErrors.name[0]"></p></template>
                                        </div>
                                        {{-- Value Input --}}
                                        <div>
                                            <label for="level-value" class="block text-sm font-medium text-gray-700">Nilai Numerik</label>
                                            <input type="number" name="value" id="level-value" x-model.number="currentLevel.value" required min="1"
                                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <template x-if="formErrors.value"><p class="mt-1 text-xs text-red-600" x-text="formErrors.value[0]"></p></template>
                                        </div>
                                        {{-- Color Picker Input --}}
                                         <div>
                                             <label for="level-color" class="block text-sm font-medium text-gray-700">Warna</label>
                                             <div class="mt-1 flex items-center space-x-3">
                                                  <input type="color" name="color" id="level-color" x-model="currentLevel.color" required pattern="^#[a-fA-F0-9]{6}$"
                                                          class="h-8 w-10 border-gray-300 rounded-md p-0 cursor-pointer shadow-sm">
                                                  <input type="text" x-model="currentLevel.color" @input="currentLevel.color = $event.target.value.startsWith('#') ? $event.target.value : '#' + $event.target.value" required pattern="^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" placeholder="#rrggbb" maxlength="7"
                                                          class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                             </div>
                                             <template x-if="formErrors.color"><p class="mt-1 text-xs text-red-600" x-text="formErrors.color[0]"></p></template>
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="isSubmitting"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-show="!isSubmitting" x-text="levelToEdit ? 'Simpan Perubahan' : 'Tambah Level'"></span>
                                <span x-show="isSubmitting">Menyimpan...</span>
                            </button>
                            <button type="button" @click="closeModal()" :disabled="isSubmitting"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- End Modal --}}

    </div>

    {{-- Include SortableJS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    {{-- AlpineJS Logic (Sama seperti sebelumnya, pastikan fungsi deleteLevel sudah ada dan benar) --}}
    <script>
        function levelSettings(config) {
    return {
        // Data
        project: config.project,
        difficultyLevels: config.initialDifficultyLevels,
        priorityLevels: config.initialPriorityLevels,
        isModalOpen: false,
        modalType: 'difficulty',
        levelToEdit: null,
        currentLevel: { id: null, name: '', value: '', color: '#cccccc' },
        isSubmitting: false,
        formErrors: {},
        statusMessage: '',
        isSuccess: true,
        csrfToken: config.csrfToken,
        updateOrderUrl: config.updateOrderUrl,
        difficultySort: null,
        prioritySort: null,

        // Computed Property
        get modalTypeLabel() { 
            return this.modalType === 'difficulty' ? 'Kesulitan' : 'Prioritas';
        },

        // Methods
        openModal(type, level = null) {
            this.modalType = type;
            this.formErrors = {};
            
            if (level) {
                // Edit mode - clone the level object to prevent direct modification
                this.levelToEdit = level;
                this.currentLevel = {
                    id: level.id,
                    name: level.name,
                    value: level.value,
                    color: level.color
                };
            } else {
                // Add mode
                this.levelToEdit = null;
                this.currentLevel = { id: null, name: '', value: '', color: '#cccccc' };
            }
            
            this.isModalOpen = true;
        },
        
        closeModal() { 
            this.isModalOpen = false;
            this.formErrors = {};
            // Delay clearing data to allow animations to complete
            setTimeout(() => {
                this.levelToEdit = null;
                this.currentLevel = { id: null, name: '', value: '', color: '#cccccc' };
            }, 300);
        },
        
        submitLevelForm() {
    this.isSubmitting = true;
    this.formErrors = {};
    
    // Check if required data is available
    if (!this.currentLevel.name || !this.currentLevel.value || !this.currentLevel.color) {
        this.showFlashMessage('Semua field harus diisi dengan benar.', false);
        this.isSubmitting = false;
        return;
    }
    
    const formData = {
        name: this.currentLevel.name,
        value: this.currentLevel.value,
        color: this.currentLevel.color
    };
    
    // Log the data being sent (for debugging)
    console.log('Sending data:', formData);
    
    const type = this.modalType; // 'difficulty' or 'priority'
    let url, method;
    
    if (this.levelToEdit) {
        // Update mode
        url = `/projects/${this.project.id}/settings/levels/${type}/${this.currentLevel.id}`;
        method = 'PATCH';
    } else {
        // Create mode
        url = `/projects/${this.project.id}/settings/levels/${type}`;
        method = 'POST';
    }
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        // First log the raw response for debugging
        response.clone().text().then(text => {
            console.log('Raw response:', text);
        });
        
        if (!response.ok) {
            return response.json().then(data => {
                throw { status: response.status, errors: data.errors || {}, message: data.message || 'Terjadi kesalahan' };
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        const listKey = type + 'Levels';
        
        if (this.levelToEdit) {
            // Update existing level in the list
            this[listKey] = this[listKey].map(level => 
                level.id === this.currentLevel.id ? data.level : level
            );
        } else {
            // Add new level to the list
            this[listKey].push(data.level);
            // Re-initialize sortable to include the new item
            this.initSortable();
        }
        
        this.showFlashMessage(data.message || (this.levelToEdit ? 'Level berhasil diperbarui.' : 'Level berhasil ditambahkan.'), true);
        this.closeModal();
    })
    .catch(error => {
        console.error('Form submission error:', error);
        if (error.errors) {
            this.formErrors = error.errors;
        }
        this.showFlashMessage(error.message || 'Gagal menyimpan data level.', false);
    })
    .finally(() => {
        this.isSubmitting = false;
    });
},

        editLevel(type, level) {
            this.modalType = type;
            this.formErrors = {};
            
            // Edit mode - clone the level object to prevent direct modification
            this.levelToEdit = level;
            this.currentLevel = {
                id: level.id,
                name: level.name,
                value: level.value,
                color: level.color
            };
            
            this.isModalOpen = true;
        },
        
        // Update the submitLevelForm function to better handle responses
        submitLevelForm() {
            this.isSubmitting = true;
            this.formErrors = {};
            
            const formData = {
                name: this.currentLevel.name,
                value: this.currentLevel.value,
                color: this.currentLevel.color
            };
            
            const type = this.modalType; // 'difficulty' or 'priority'
            let url, method;
            
            if (this.levelToEdit) {
                // Update mode
                url = `/projects/${this.project.id}/settings/levels/${type}/${this.currentLevel.id}`;
                method = 'PATCH';
            } else {
                // Create mode
                url = `/projects/${this.project.id}/settings/levels/${type}`;
                method = 'POST';
            }
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw { status: response.status, errors: data.errors || {}, message: data.message || 'Terjadi kesalahan' };
                    });
                }
                return response.json();
            })
            .then(data => {
                const listKey = type + 'Levels';
                
                if (this.levelToEdit) {
                    // Update existing level in the list
                    this[listKey] = this[listKey].map(level => 
                        level.id === this.currentLevel.id ? data.level : level
                    );
                } else {
                    // Add new level to the list
                    this[listKey].push(data.level);
                    // Re-initialize sortable to include the new item
                    this.initSortable();
                }
                
                this.showFlashMessage(data.message || (this.levelToEdit ? 'Level berhasil diperbarui.' : 'Level berhasil ditambahkan.'), true);
                this.closeModal();
            })
            .catch(error => {
                console.error('Form submission error:', error);
                if (error.errors) {
                    this.formErrors = error.errors;
                }
                this.showFlashMessage(error.message || 'Gagal menyimpan data level.', false);
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        deleteLevel(event, type, levelId) {
    event.preventDefault();
    if (!confirm('Yakin ingin menghapus level ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }

    const url = `/projects/${this.project.id}/settings/levels/${type}/${levelId}`;
    const button = event.currentTarget.querySelector('button[type="submit"]');

    if (button) button.disabled = true;

    // Log the delete request for debugging
    console.log(`Deleting ${type} level with ID: ${levelId}`);

    fetch(url, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
        }
    })
    .then(response => {
        // First log the raw response for debugging
        response.clone().text().then(text => {
            console.log('Raw delete response:', text);
        });
        
        if (!response.ok) {
            return response.json().then(err => { 
                throw { status: response.status, body: err }; 
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Delete response:', data);
        this.showFlashMessage(data.message || 'Level berhasil dihapus.', true);
        const listKey = type + 'Levels';
        this[listKey] = this[listKey].filter(l => l.id !== levelId);
    })
    .catch(errorInfo => {
        console.error('Delete error:', errorInfo);
        let message = 'Gagal menghapus level.';
        if (errorInfo.body && errorInfo.body.message) {
            message = errorInfo.body.message;
        } else if (errorInfo.message) {
            message = errorInfo.message;
        }
        this.showFlashMessage(message, false);
        
        // Even if there's an error in the response handling, 
        // if we know the item was deleted from the database, let's update the UI
        const listKey = type + 'Levels';
        this[listKey] = this[listKey].filter(l => l.id !== levelId);
    })
    .finally(() => {
        if (button) button.disabled = false;
    });
},

        showFlashMessage(message, success = true) {
            this.statusMessage = message;
            this.isSuccess = success;
            setTimeout(() => {
                this.statusMessage = '';
            }, 4000);
        },
        
        initSortable() {
            // Destroy existing sortable instances if they exist
            if (this.difficultySort) {
                this.difficultySort.destroy();
            }
            if (this.prioritySort) {
                this.prioritySort.destroy();
            }
            
            // Initialize sortable for difficulty levels
            const difficultyEl = this.$refs.difficultyList;
            if (difficultyEl) {
                this.difficultySort = new Sortable(difficultyEl, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    handle: '.cursor-move',
                    onEnd: (evt) => {
                        const ids = Array.from(difficultyEl.querySelectorAll('li'))
                            .map(li => parseInt(li.getAttribute('data-id'), 10));
                        this.saveOrder('difficulty', ids);
                    }
                });
            }
            
            // Initialize sortable for priority levels
            const priorityEl = this.$refs.priorityList;
            if (priorityEl) {
                this.prioritySort = new Sortable(priorityEl, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    handle: '.cursor-move',
                    onEnd: (evt) => {
                        const ids = Array.from(priorityEl.querySelectorAll('li'))
                            .map(li => parseInt(li.getAttribute('data-id'), 10));
                        this.saveOrder('priority', ids);
                    }
                });
            }
        },
        
        saveOrder(levelType, orderedIds) {
            fetch(this.updateOrderUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    level_type: levelType,
                    ordered_ids: orderedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showFlashMessage(data.message || 'Urutan berhasil diperbarui.', true);
                } else {
                    this.showFlashMessage(data.message || 'Gagal memperbarui urutan.', false);
                }
            })
            .catch(error => {
                console.error('Order update error:', error);
                this.showFlashMessage('Gagal memperbarui urutan.', false);
            });
        },
        
        init() {
            // Initial setup
            this.$nextTick(() => {
                this.initSortable();
            });
        }
    };
}
    </script>
     @push('styles')
        <style>
            /* Style untuk item yang sedang di-drag oleh SortableJS */
            .sortable-ghost {
                background-color: #dee2e6; /* Warna latar abu-abu muda */
                opacity: 0.6;
                border: 1px dashed #6c757d; /* Border putus-putus */
            }
            .sortable-chosen {
                /* Mungkin tidak perlu style khusus */
            }
             .cursor-grab { cursor: grab; }
             .cursor-move { cursor: move; } /* Untuk handle */
        </style>
     @endpush
</x-app-layout>