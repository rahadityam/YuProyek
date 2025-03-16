<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-row pt-6 pr-6 space-x-8" style="height: calc(100vh - 110px);">
                <!-- Global Projects (Left Side) - Fixed width -->
                <div class="w-3/4 overflow-y-auto pr-2" style="flex: 0 0 75%;">
                    <div>
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('All Projects') }}</h3>
                                <a href="{{ route('projects.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($projects as $project)
                                    <div class="border rounded-lg shadow p-4 hover:shadow-md transition bg-white">
                                        <h4 class="font-semibold text-lg mb-2">{{ $project->name }}</h4>
                                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($project->description, 100) }}</p>
                                        <div class="flex justify-between items-center text-xs text-gray-500">
                                            <span>{{ date('d M Y', strtotime($project->start_date)) }}</span>
                                            <span class="px-2 py-1 rounded 
                                                @if($project->status == 'open') bg-blue-100 text-blue-800 
                                                @elseif($project->status == 'in_progress') bg-yellow-100 text-yellow-800
                                                @elseif($project->status == 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                            </span>
                                        </div>
                                        <a href="{{ route('projects.show', $project->id) }}" 
                                        class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-200">
                                            Lihat Detail
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User's Projects (Right Side) - Fixed width -->
                <div class="w-1/4" style="flex: 0 0 25%;">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 h-full flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ Auth::user()->role === 'project_owner' ? __('My Projects') : __('Projects I Follow') }}
                                </h3>
                                <a href="{{ route('projects.my-projects') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                            </div>
                            <div class="grid grid-cols-1 gap-4 overflow-y-auto pr-2">
                                @if(count($userProjects) > 0)
                                    @foreach($userProjects as $project)
                                        <div class="border rounded-lg shadow p-4 hover:shadow-md transition">
                                            <h4 class="font-semibold text-lg mb-2">{{ $project->name }}</h4>
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($project->description, 100) }}</p>
                                            <div class="flex justify-between items-center text-xs text-gray-500">
                                                <span>{{ date('d M Y', strtotime($project->start_date)) }}</span>
                                                <span class="px-2 py-1 rounded 
                                                    @if($project->status == 'open') bg-blue-100 text-blue-800 
                                                    @elseif($project->status == 'in_progress') bg-yellow-100 text-yellow-800
                                                    @elseif($project->status == 'completed') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                </span>
                                            </div>
                                            <a href="{{ route('projects.dashboard', $project->id) }}" 
                       class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-200">
                        Lihat Detail
                    </a>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-sm text-gray-500">No projects found.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>