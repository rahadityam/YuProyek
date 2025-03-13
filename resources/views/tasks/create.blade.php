<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Task') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('tasks.store') }}">
                        @csrf
                        <!-- Hidden Fields -->
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="hidden" name="project_id" value="{{ $project_id }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Task Title -->
                            <div class="mb-4 col-span-2">
                                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">
                                    Nama Tugas:
                                </label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       required>
                                @error('title')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Task Description -->
                            <div class="mb-4 col-span-2">
                                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                                    Deskripsi:
                                </label>
                                <textarea name="description" id="description" rows="4"
                                          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Difficulty Level -->
                            <div class="mb-4">
                                <label for="difficulty_level" class="block text-gray-700 text-sm font-bold mb-2">
                                    Tingkat Kesulitan:
                                </label>
                                <select name="difficulty_level" id="difficulty_level"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       required>
                                    <option value="">Pilih Level</option>
                                    <option value="1" {{ old('difficulty_level') == '1' ? 'selected' : '' }}>1 - Sangat Ringan</option>
                                    <option value="2" {{ old('difficulty_level') == '2' ? 'selected' : '' }}>2 - Ringan</option>
                                    <option value="3" {{ old('difficulty_level') == '3' ? 'selected' : '' }}>3 - Normal</option>
                                    <option value="4" {{ old('difficulty_level') == '4' ? 'selected' : '' }}>4 - Berat</option>
                                    <option value="5" {{ old('difficulty_level') == '5' ? 'selected' : '' }}>5 - Sangat Berat</option>
                                </select>
                                @error('difficulty_level')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Priority Level -->
                            <div class="mb-4">
                                <label for="priority_level" class="block text-gray-700 text-sm font-bold mb-2">
                                    Prioritas:
                                </label>
                                <select name="priority_level" id="priority_level"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       required>
                                    <option value="">Pilih Prioritas</option>
                                    <option value="1" {{ old('priority_level') == '1' ? 'selected' : '' }}>1 - Sangat Rendah</option>
                                    <option value="2" {{ old('priority_level') == '2' ? 'selected' : '' }}>2 - Rendah</option>
                                    <option value="3" {{ old('priority_level') == '3' ? 'selected' : '' }}>3 - Normal</option>
                                    <option value="4" {{ old('priority_level') == '4' ? 'selected' : '' }}>4 - Tinggi</option>
                                    <option value="5" {{ old('priority_level') == '5' ? 'selected' : '' }}>5 - Sangat Tinggi</option>
                                </select>
                                @error('priority_level')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Start Time -->
                            <div class="mb-4">
                                <label for="start_time" class="block text-gray-700 text-sm font-bold mb-2">
                                    Tanggal Mulai:
                                </label>
                                <input type="date" name="start_time" id="start_time" value="{{ old('start_time') }}"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       required>
                                @error('start_time')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- End Time -->
                            <div class="mb-4">
                                <label for="end_time" class="block text-gray-700 text-sm font-bold mb-2">
                                    Tanggal Akhir:
                                </label>
                                <input type="date" name="end_time" id="end_time" value="{{ old('end_time') }}"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       required>
                                @error('end_time')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Assigned User -->
                            <div class="mb-4 col-span-2">
                                <label for="assigned_to" class="block text-gray-700 text-sm font-bold mb-2">
                                    Assign User:
                                </label>
                                <select name="assigned_to" id="assigned_to"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       required>
                                    <option value="">Pilih User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id || auth()->id() == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex items-center justify-between mt-6">
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Create Task
                            </button>
                            <a href="{{ route('projects.kanban', $project_id) }}"
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>