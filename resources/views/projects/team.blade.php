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
                            <div class="flex-1">
                                <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $owner->id]) }}" class="font-medium hover:text-blue-600">{{ $owner->name }}</a>
                                <p class="text-sm text-gray-500">{{ $owner->email }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Members Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3 border-b pb-2">Team Members</h3>
                        @if($members->count() > 0)
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($members as $member)
                                    <div class="flex flex-col md:flex-row md:items-center p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center flex-1">
                                            <div class="flex-shrink-0 mr-4">
                                                @if($member->profile_photo_path)
                                                    <img class="h-12 w-12 rounded-full" src="{{ Storage::url($member->profile_photo_path) }}" alt="{{ $member->name }}">
                                                @else
                                                    <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-gray-600">{{ substr($member->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $member->id]) }}" class="font-medium hover:text-blue-600">{{ $member->name }}</a>
                                                <p class="text-sm text-gray-500">{{ $member->pivot->position }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-4 mt-3 md:mt-0">
                                            @if($project->owner_id === auth()->id())
                                                <!-- Wage Standard Assignment (Smaller and right-aligned) -->
                                                <div class="flex-shrink-0 w-48">
                                                    <select id="wage_standard_{{ $member->id }}" 
                                                            data-member-id="{{ $member->id }}"
                                                            data-project-id="{{ $project->id }}"
                                                            class="wage-standard-select block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                                                        <option value="">Select wage</option>
                                                        @foreach($wageStandards as $wageStandard)
                                                            <option value="{{ $wageStandard->id }}" {{ $member->pivot->wage_standard_id == $wageStandard->id ? 'selected' : '' }}>
                                                                {{ $wageStandard->job_category }} - {{ number_format($wageStandard->task_price, 0, ',', '.') }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <!-- Three-dot menu for actions -->
                                                <div class="relative" x-data="{ open: false }">
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                        </svg>
                                                    </button>
                                                    <div x-show="open" 
                                                         @click.away="open = false" 
                                                         class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-10">
                                                        <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $member->id]) }}" 
                                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            View Profile
                                                        </a>
                                                        <button class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                                                onclick="if(confirm('Are you sure you want to remove this member?')) { 
                                                                    document.getElementById('remove-member-{{ $member->id }}').submit(); 
                                                                }">
                                                            Remove Member
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <form id="remove-member-{{ $member->id }}" action="{{ route('projects.team.remove', ['project' => $project->id, 'user' => $member->id]) }}" method="POST" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @else
                                                <!-- Display wage standard info for non-owners -->
                                                <div class="text-right">
                                                    @if($member->pivot->wage_standard_id)
                                                        @php
                                                            $memberWageStandard = $wageStandards->where('id', $member->pivot->wage_standard_id)->first();
                                                        @endphp
                                                        @if($memberWageStandard)
                                                            <p class="text-sm text-gray-600">
                                                                {{ $memberWageStandard->job_category }} ({{ number_format($memberWageStandard->task_price, 0, ',', '.') }})
                                                            </p>
                                                        @endif
                                                    @else
                                                        <p class="text-sm text-gray-500 italic">No wage assigned</p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
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
                                                        <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $applicant->id]) }}" class="font-medium hover:text-blue-600">{{ $applicant->name }}</a>
                                                        <p class="text-sm text-gray-500">Applied for: {{ $applicant->pivot->position }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <!-- Applicant actions -->
                                                    <div class="flex space-x-2">
                                                        <!-- For the Accept button -->
                                                        <form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $applicant->id]) }}" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="accepted">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700">
                                                                Accept
                                                            </button>
                                                        </form>

                                                        <!-- For the Reject button -->
                                                        <form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $applicant->id]) }}" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">
                                                                Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                    
                                                    <!-- Three-dot menu for applicant actions -->
                                                    <div class="relative" x-data="{ open: false }">
                                                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                            </svg>
                                                        </button>
                                                        <div x-show="open" 
                                                             @click.away="open = false" 
                                                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-10">
                                                            <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $applicant->id]) }}" 
                                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                View Profile
                                                            </a>
                                                        </div>
                                                    </div>
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

    <!-- Add JavaScript for auto-updating wage standards -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all wage standard selects
            const wageSelects = document.querySelectorAll('.wage-standard-select');
            
            // Add change event listener to each select
            wageSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const memberId = this.dataset.memberId;
                    const projectId = this.dataset.projectId;
                    const wageStandardId = this.value;
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('wage_standard_id', wageStandardId);
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'PATCH');
                    
                    // Send AJAX request
                    fetch(`/projects/${projectId}/team/${memberId}/wage`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Success:', data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>
</x-app-layout>