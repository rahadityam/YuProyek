<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Profile: ') . $user->name }}
            </h2>
            <a href="{{ route('projects.team', $project) }}" class="px-4 py-2 bg-gray-300 rounded-md text-gray-800 text-sm font-medium hover:bg-gray-400">
                {{ __('Back to Team') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Project Member Details -->
                    @if(isset($projectUser))
                    <div class="mb-8 bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                        <h3 class="text-lg font-medium text-indigo-800 mb-2">{{ __('Project Role') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-indigo-600">{{ __('Position') }}</p>
                                <p class="font-medium">{{ $projectUser->position }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-indigo-600">{{ __('Status') }}</p>
                                <p class="font-medium">
                                    @if($projectUser->status == 'applied')
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-semibold">Applicant</span>
                                    @elseif($projectUser->status == 'accepted')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">Active Member</span>
                                    @elseif($projectUser->status == 'rejected')
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold">Rejected</span>
                                    @endif
                                </p>
                            </div>
                            @if($projectUser->salary)
                            <div>
                                <p class="text-sm text-indigo-600">{{ __('Salary') }}</p>
                                <p class="font-medium">{{ number_format($projectUser->salary, 0, ',', '.') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Personal Data Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">{{ __('Personal Data') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Profile Photo -->
                            @if ($user->profile_photo_path)
                            <div class="col-span-2 flex justify-center md:justify-start">
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                     alt="{{ $user->name }}" 
                                     class="rounded-full h-32 w-32 object-cover">
                            </div>
                            @endif
                            
                            <!-- Name -->
                            <div>
                                <p class="text-sm text-gray-600">{{ __('Name') }}</p>
                                <p class="font-medium">{{ $user->name }}</p>
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <p class="text-sm text-gray-600">{{ __('Email') }}</p>
                                <p class="font-medium">{{ $user->email }}</p>
                            </div>
                            
                            <!-- Phone Number -->
                            <div>
                                <p class="text-sm text-gray-600">{{ __('Phone Number') }}</p>
                                <p class="font-medium">{{ $user->phone_number ?? 'Not provided' }}</p>
                            </div>
                            
                            <!-- Birth Date -->
                            <div>
                                <p class="text-sm text-gray-600">{{ __('Birth Date') }}</p>
                                <p class="font-medium">{{ $user->birth_date ? $user->birth_date->format('d F Y') : 'Not provided' }}</p>
                            </div>
                            
                            <!-- Address -->
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">{{ __('Address') }}</p>
                                <p class="font-medium">{{ $user->address ?? 'Not provided' }}</p>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">{{ __('Description') }}</p>
                                <p class="font-medium">{{ $user->description ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Education Data Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">{{ __('Education') }}</h3>
                        
                        @if($educations->count() > 0)
                            <div class="space-y-4">
                                @foreach($educations as $education)
                                <div class="border rounded-md p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-2">
                                        <div>
                                            <p class="text-sm text-gray-600">{{ __('Level') }}</p>
                                            <p class="font-medium">{{ $education->level }}</p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm text-gray-600">{{ __('Institution') }}</p>
                                            <p class="font-medium">{{ $education->institution }}</p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm text-gray-600">{{ __('Major') }}</p>
                                            <p class="font-medium">{{ $education->major }}</p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm text-gray-600">{{ __('Graduation Year') }}</p>
                                            <p class="font-medium">{{ $education->graduation_year }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">{{ __('No education data available.') }}</p>
                        @endif
                    </div>
                    
                    <!-- Documents Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">{{ __('Documents') }}</h3>
                        
                        <div class="space-y-4">
                            <!-- CV -->
                            <div class="border rounded-md p-4">
                                <p class="text-sm text-gray-600">{{ __('CV/Resume') }}</p>
                                @if($cv)
                                    <a href="{{ asset('storage/' . $cv->file_path) }}" target="_blank" rel="noopener noreferrer" 
                                       class="text-blue-600 hover:text-blue-800 flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ __('View CV') }}
                                    </a>
                                @else
                                    <p class="text-gray-500 mt-1 italic">{{ __('No CV uploaded.') }}</p>
                                @endif
                            </div>
                            
                            <!-- Portfolio -->
                            <div class="border rounded-md p-4">
                                <p class="text-sm text-gray-600">{{ __('Portfolio') }}</p>
                                @if($portfolio)
                                    <a href="{{ asset('storage/' . $portfolio->file_path) }}" target="_blank" rel="noopener noreferrer" 
                                       class="text-blue-600 hover:text-blue-800 flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ __('View Portfolio') }}
                                    </a>
                                @else
                                    <p class="text-gray-500 mt-1 italic">{{ __('No portfolio uploaded.') }}</p>
                                @endif
                            </div>
                            
                            <!-- Certificates -->
                            <div class="border rounded-md p-4">
                                <p class="text-sm text-gray-600">{{ __('Certificates') }}</p>
                                @if($certificates->count() > 0)
                                    <div class="mt-2 space-y-2">
                                        @foreach($certificates as $cert)
                                            <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank" rel="noopener noreferrer" 
                                               class="text-blue-600 hover:text-blue-800 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $cert->title }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 mt-1 italic">{{ __('No certificates uploaded.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions Section (only for project owner) -->
                    @if($project->owner_id === auth()->id() && isset($projectUser) && $projectUser->status === 'applied')
                    <div class="mt-8 pt-4 border-t">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Application Actions') }}</h3>
                        <div class="flex space-x-4">
                            <form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $user->id]) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Accept Application
                                </button>
                            </form>

                            <form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $user->id]) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Reject Application
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                    
                    @if($project->owner_id === auth()->id() && isset($projectUser) && $projectUser->status === 'accepted')
                    <div class="mt-8 pt-4 border-t">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Team Member Actions') }}</h3>
                        <form method="POST" action="{{ route('projects.team.remove', ['project' => $project->id, 'user' => $user->id]) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to remove this team member?')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Remove from Team
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>