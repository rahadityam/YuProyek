<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">{{ $project->name }} - Team Members</h2>
                    
                    <!-- Project Owner Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3 border-b pb-2">Project Owner</h3>
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                @if($owner->profile_photo_path)
                                    <img class="h-12 w-12 rounded-full" src="{{ Storage::url($owner->profile_photo_path) }}" alt="{{ $owner->name }}">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-gray-600">{{ substr($owner->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-medium">{{ $owner->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $owner->email }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Members Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3 border-b pb-2">Team Members</h3>
                        @if($members->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($members as $member)
                                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-shrink-0">
                                            @if($member->profile_photo_path)
                                                <img class="h-12 w-12 rounded-full" src="{{ Storage::url($member->profile_photo_path) }}" alt="{{ $member->name }}">
                                            @else
                                                
                                        <div
                                            class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-700">
                                            {{ substr($member->name, 0, 1) }}
                                        </div>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium">{{ $member->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $member->pivot->position }}</p>
                                        </div>
                                        @if($project->owner_id === auth()->id())
                                            <div>
                                                <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $member->id]) }}" class="text-blue-500 hover:text-blue-700 mr-4 px-1.5">
                                                    View Profile
                                                </a>
                                                <button class="text-red-500 hover:text-red-700" 
                                                        onclick="if(confirm('Are you sure you want to remove this member?')) { 
                                                            document.getElementById('remove-member-{{ $member->id }}').submit(); 
                                                        }">
                                                    Remove
                                                </button>
                                                <form id="remove-member-{{ $member->id }}" action="{{ route('projects.team.remove', ['project' => $project->id, 'user' => $member->id]) }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">No team members yet.</p>
                        @endif
                    </div>
                    
                    <!-- Applicants Section (only visible to project owner) -->
                    @if($project->owner_id === auth()->id())
                        <div>
                            <h3 class="text-lg font-semibold mb-3 border-b pb-2">Applicants</h3>
                            @if($applicants->count() > 0)
                                <div class="space-y-4">
                                    @foreach($applicants as $applicant)
                                        <div class="border rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div class="flex-shrink-0">
                                                        @if($applicant->profile_photo_path)
                                                            <img class="h-12 w-12 rounded-full" src="{{ Storage::url($applicant->profile_photo_path) }}" alt="{{ $applicant->name }}">
                                                        @else
                                                            <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                                                <span class="text-gray-600">{{ substr($applicant->name, 0, 1) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium">{{ $applicant->name }}</h4>
                                                        <p class="text-sm text-gray-500">Applied for: {{ $applicant->pivot->position }}</p>
                                                    </div>
                                                </div>
                                                <div class="space-x-2">
                                                <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $applicant->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                                                    View Profile
                                                </a>
                                                    <!-- For the Accept button (replace the # in action="#") -->
<form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $applicant->id]) }}" class="inline">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="accepted">
    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700">
        Accept
    </button>
</form>

<!-- For the Reject button (replace the # in action="#") -->
<form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $applicant->id]) }}" class="inline">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="rejected">
    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">
        Reject
    </button>
</form>

<!-- For the Remove member button (replace the # in action="#") -->

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">No pending applications.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>