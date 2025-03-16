<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <!-- Project Details Card -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden" x-data="{ showFullDescription: false }">
            <!-- Header with Project Name and Back Button -->
            <div class="px-6 pt-6 pb-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold">{{ $project->name }}</h1>
                    <a href="{{ route('projects.index') }}" class="text-blue-500 hover:text-blue-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            <!-- Main Details Section -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column - Basic Info -->
                    <div>
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-700">Deskripsi Proyek</h2>
                            <div class="mt-2 text-gray-600">
                                <p x-show="!showFullDescription">{{ Str::limit($project->description, 150) }}</p>
                                <p x-show="showFullDescription">{{ $project->description }}</p>
                                @if(Str::length($project->description) > 150)
                                    <button @click="showFullDescription = !showFullDescription"
                                        class="text-blue-500 text-sm mt-2">
                                        <span x-text="showFullDescription ? 'Sembunyikan' : 'Baca selengkapnya'"></span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-700">Pemilik Proyek</h2>
                            <div class="flex items-center mt-2">
                                <div
                                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                    <span class="font-medium">{{ substr($project->owner->name, 0, 2) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-800 font-medium">{{ $project->owner->name }}</p>
                                    <p class="text-gray-500 text-sm">{{ $project->owner->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Time & Budget Info -->
                    <div>
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-700">Anggaran</h2>
                            <p class="mt-2 text-2xl font-bold text-gray-800">Rp
                                {{ number_format($project->budget, 0, ',', '.') }}</p>
                        </div>

                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-700">Waktu Pelaksanaan</h2>
                            <div class="mt-2 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Tanggal Mulai</p>
                                    <p class="font-medium">
                                        {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Tanggal Selesai</p>
                                    <p class="font-medium">
                                        {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-gray-700">Status Proyek</h2>
                            <p class="mt-2">
                                <span class="px-3 py-1 inline-flex text-sm rounded-full font-semibold
                                    {{ $project->status === 'open' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $project->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $project->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $project->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Categories Section -->
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-2">Kategori Proyek</h2>
                    <div class="flex flex-wrap">
                        @forelse($project->categories as $category)
                            <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm mr-2 mb-2">
                                {{ $category->name }}
                            </span>
                        @empty
                            <span class="text-gray-500 italic">Tidak ada kategori</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Timeline Proyek</h2>
                <div class="relative">
                    @php
                        $startDate = \Carbon\Carbon::parse($project->start_date);
                        $endDate = \Carbon\Carbon::parse($project->end_date);
                        $totalDays = max(1, $startDate->diffInDays($endDate));
                        $daysElapsed = min($totalDays, max(0, $startDate->diffInDays(now())));
                        $progress = min(100, max(0, ($daysElapsed / $totalDays) * 100));
                    @endphp

                    <div class="h-2 bg-gray-200 rounded">
                        <div class="h-2 bg-blue-500 rounded" style="width: {{ $progress }}%"></div>
                    </div>

                    <div class="flex justify-between mt-2 text-sm text-gray-600">
                        <span>{{ $startDate->format('d M Y') }}</span>
                        <span>{{ $endDate->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Register Button -->
            @if(auth()->check() && auth()->user()->id !== $project->owner_id)
                        <!-- Check if user has already applied -->
                        @php
                            $hasApplied = $project->workers()
                                ->where('user_id', auth()->id())
                                ->where('status', 'applied')
                                ->exists();
                        @endphp

                        @if($hasApplied)
                            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                                <span class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md cursor-not-allowed">
                                    {{ __('Application Submitted') }}
                                </span>
                            </div>
                        @else
                            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                                <a href="{{ route('projects.apply', $project) }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    {{ __('Apply to This Project') }}
                                </a>
                            </div>
                        @endif
            @endif
        </div>
    </div>
</x-app-layout>