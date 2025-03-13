<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-3xl font-bold">Buat Proyek Baru</h1>
                <a href="{{ route('projects.index') }}" class="text-blue-500 hover:text-blue-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Kembali
                </a>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6">
                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf
                    <!-- Nama Proyek -->
                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Nama Proyek <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <!-- Deskripsi Proyek -->
                    <div class="mb-6">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Deskripsi Proyek</label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    </div>
                    <!-- Kategori Proyek -->
                    <div class="mb-6">
                        <label for="categories" class="block text-gray-700 font-medium mb-2">Kategori Proyek</label>
                        <div class="relative">
                            <select name="categories[]" id="categories" multiple
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                            <input type="date" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <!-- Tanggal Akhir -->
                        <div>
                            <label for="end_date" class="block text-gray-700 font-medium mb-2">Tanggal Akhir <span class="text-red-500">*</span></label>
                            <input type="date" id="end_date" name="end_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <!-- Anggaran Proyek -->
                    <div class="mb-6">
                        <label for="budget" class="block text-gray-700 font-medium mb-2">Anggaran Proyek (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" id="budget" name="budget" value="1000000" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <!-- Hidden fields -->
                    <input type="hidden" name="status" value="open">
                    <input type="hidden" name="owner_id" value="{{ Auth::id() }}">
                    <!-- Submit button - no Alpine.js -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            Buat Proyek
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>