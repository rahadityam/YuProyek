<x-app-layout>
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-900">Pengaturan Bobot - {{ $project->name }}</h2>
            </div>
             <!-- Back Button -->
             <div class="mb-4">
                 <a href="{{ route('projects.pengaturan', $project) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                     <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                       <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                     </svg>
                     Kembali ke Pengaturan
                 </a>
             </div>

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
             @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form action="{{ route('projects.settings.weights.update', $project) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <p class="text-sm text-gray-600 mb-4">Atur bobot persentase untuk perhitungan skor WSM. Total bobot harus 100.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="difficulty_weight" class="block text-sm font-medium text-gray-700">Bobot Tingkat Kesulitan (%)</label>
                                <input type="number" name="difficulty_weight" id="difficulty_weight" min="0" max="100"
                                       value="{{ old('difficulty_weight', $project->difficulty_weight) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label for="priority_weight" class="block text-sm font-medium text-gray-700">Bobot Prioritas (%)</label>
                                <input type="number" name="priority_weight" id="priority_weight" min="0" max="100"
                                       value="{{ old('priority_weight', $project->priority_weight) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Simpan Bobot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-app-layout>