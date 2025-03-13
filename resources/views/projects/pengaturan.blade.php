<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <form action="{{ route('projects.update', $project) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Nama Proyek -->
                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Nama Proyek <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $project->name) }}" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <!-- Deskripsi Proyek -->
                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Deskripsi Proyek</label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $project->description) }}</textarea>
                    </div>
                    <!-- Kategori Proyek -->
                    <div class="mb-6">
                        <label for="categories" class="block text-gray-700 font-medium mb-2">Kategori Proyek</label>
                        <div class="relative">
                            <select name="categories[]" id="categories" multiple
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Tahan tombol Ctrl (Windows) atau Command (Mac) untuk memilih beberapa kategori</p>
                        </div>
                    </div>
                    <!-- Tanggal (Mulai dan Akhir) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Tanggal Mulai -->
                        <div>
                            <label for="start_date" class="block text-gray-700 font-medium mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $project->start_date) }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <!-- Tanggal Akhir -->
                        <div>
                            <label for="end_date" class="block text-gray-700 font-medium mb-2">Tanggal Akhir <span class="text-red-500">*</span></label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $project->end_date) }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <!-- Anggaran Proyek -->
                    <div class="mb-6">
                        <label for="budget" class="block text-gray-700 font-medium mb-2">Anggaran Proyek (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" id="budget" name="budget" value="{{ old('budget', $project->budget) }}" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <!-- Status Proyek -->
                    <div class="mb-6">
                        <label for="status" class="block text-gray-700 font-medium mb-2">Status Proyek</label>
                        <select id="status" name="status" 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="open" {{ $project->status == 'open' ? 'selected' : '' }}>Terbuka</option>
                            <option value="in_progress" {{ $project->status == 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                            <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ $project->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <!-- Submit button -->
                    <div class="flex justify-between">
                        <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                            Batal
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>