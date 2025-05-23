<div> {{-- Root Element --}}
    {{-- Container padding (bisa dihapus jika sudah di layout) --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        {{-- Header Halaman --}}
        <div class="mb-6">
            {{-- Akses properti project langsung --}}
            <h2 class="text-2xl font-bold text-gray-900">{{ $project->name }} - Team Members</h2>
        </div>

        {{-- Flash Messages for Livewire Actions --}}
        @if (session()->has('success_message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success_message') }}</span>
            </div>
        @endif
        @if (session()->has('error_message'))
             <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                 <span class="block sm:inline">{{ session('error_message') }}</span>
             </div>
        @endif

        {{-- Project Owner Section --}}
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-3 border-b pb-2">Project Owner</h3>
            <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    {{-- Akses properti owner --}}
                    @if($owner->profile_photo_path)
                        <img class="h-12 w-12 rounded-full" src="{{ Storage::url($owner->profile_photo_path) }}" alt="{{ $owner->name }}">
                    @else
                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-gray-600">{{ substr($owner->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    {{-- Gunakan route helper --}}
                    <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $owner->id]) }}" wire:navigate class="font-medium hover:text-blue-600">{{ $owner->name }}</a>
                    <p class="text-sm text-gray-500">{{ $owner->email }}</p>
                </div>
            </div>
        </div>

        {{-- Team Members Section --}}
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-3 border-b pb-2">Team Members ({{ $members->count() }})</h3>
            @if($members->count() > 0)
                <div class="grid grid-cols-1 gap-4">
                    @foreach($members as $member)
                        {{-- Gunakan wire:key untuk performa Livewire --}}
                        <div class="flex flex-col md:flex-row md:items-center p-4 bg-gray-50 rounded-lg" wire:key="member-{{ $member->id }}">
                            <div class="flex items-center flex-1">
                                <div class="flex-shrink-0 mr-4">
                                    {{-- Avatar --}}
                                    @if($member->profile_photo_path)
                                        <img class="h-12 w-12 rounded-full" src="{{ Storage::url($member->profile_photo_path) }}" alt="{{ $member->name }}">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center"> <span class="text-gray-600">{{ substr($member->name, 0, 1) }}</span> </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    {{-- Link profil (bisa pakai wire:navigate jika halaman profil juga Livewire) --}}
                                    <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $member->id]) }}" wire:navigate class="font-medium hover:text-blue-600">{{ $member->name }}</a>
                                    <p class="text-sm text-gray-500">{{ $member->pivot->position ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4 mt-3 md:mt-0">
                                {{-- Tampilkan hanya untuk owner --}}
                                @if($project->owner_id === auth()->id())
                                    <!-- Wage Standard Assignment -->
                                    <div class="flex-shrink-0 w-56"> {{-- Lebarkan sedikit --}}
                                        {{-- wire:model untuk binding, wire:change untuk update --}}
                                        <select id="wage_standard_{{ $member->id }}"
                                                wire:model="memberWageAssignments.{{ $member->id }}"
                                                wire:change="updateMemberWage({{ $member->id }}, $event.target.value)"
                                                class="block w-full pl-3 pr-10 py-1 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                                                {{-- Tampilkan loading saat update spesifik ini --}}
                                                wire:loading.attr="disabled" wire:target="updateMemberWage({{ $member->id }}, $event.target.value)">
                                            <option value="">-- Select Wage --</option>
                                            @foreach($wageStandards as $wageStandard)
                                                <option value="{{ $wageStandard->id }}">
                                                    {{ $wageStandard->job_category }} - ({{ number_format($wageStandard->task_price, 0, ',', '.') }})
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- Tampilkan pesan error validasi inline (jika ada) --}}
                                        {{-- @error('memberWageAssignments.'.$member->id) <span class="text-xs text-red-500">{{ $message }}</span> @enderror --}}
                                         {{-- Indikator loading kecil --}}
                                         <div wire:loading wire:target="updateMemberWage({{ $member->id }}, $event.target.value)" class="text-xs text-gray-500 italic animate-pulse">Saving...</div>
                                    </div>

                                    <!-- Three-dot menu for actions -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600"> <svg class="h-5 w-5">...</svg> </button>
                                        <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-10" style="display: none;">
                                            <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $member->id]) }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"> View Profile </a>
                                            {{-- Tombol Remove tetap pakai form POST biasa --}}
                                            <button class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                                    onclick="if(confirm('Are you sure you want to remove {{ $member->name }}?')) { document.getElementById('remove-member-{{ $member->id }}').submit(); }">
                                                Remove Member
                                            </button>
                                        </div>
                                    </div>
                                    {{-- Form action untuk remove --}}
                                    <form id="remove-member-{{ $member->id }}" action="{{ route('projects.team.remove', ['project' => $project->id, 'user' => $member->id]) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                @else
                                    <!-- Tampilan untuk non-owner -->
                                    <div class="text-right w-56"> {{-- Samakan lebar --}}
                                        @php $assignedWage = $wageStandards->find($member->pivot->wage_standard_id); @endphp
                                        @if($assignedWage)
                                            <p class="text-sm text-gray-600 truncate" title="{{ $assignedWage->job_category }} ({{ number_format($assignedWage->task_price, 0, ',', '.') }})">
                                                {{ $assignedWage->job_category }}
                                            </p>
                                            <p class="text-xs text-gray-500">({{ number_format($assignedWage->task_price, 0, ',', '.') }})</p>
                                        @else
                                            <p class="text-sm text-gray-500 italic">No wage assigned</p>
                                        @endif
                                    </div>
                                    <div class="w-5 h-5"></div> {{-- Placeholder agar align --}}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic mt-4">No active team members yet.</p>
            @endif
        </div>

        {{-- Applicants Section (Hanya untuk Owner) --}}
        @if($project->owner_id === auth()->id())
            <div>
                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Applicants ({{ $applicants->count() }})</h3>
                @if($applicants->count() > 0)
                    <div class="space-y-4">
                        @foreach($applicants as $applicant)
                            <div class="border rounded-lg p-4" wire:key="applicant-{{ $applicant->id }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        {{-- Avatar --}}
                                        <div class="flex-shrink-0">
                                            @if($applicant->profile_photo_path)
                                                <img class="h-12 w-12 rounded-full" src="{{ Storage::url($applicant->profile_photo_path) }}" alt="{{ $applicant->name }}">
                                            @else
                                                <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center"> <span class="text-gray-600">{{ substr($applicant->name, 0, 1) }}</span> </div>
                                            @endif
                                        </div>
                                        <div>
                                            {{-- Link profil (bisa wire:navigate) --}}
                                            <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $applicant->id]) }}" wire:navigate class="font-medium hover:text-blue-600">{{ $applicant->name }}</a>
                                            <p class="text-sm text-gray-500">Applied for: {{ $applicant->pivot->position }}</p>
                                            <p class="text-xs text-gray-400">Applied: {{ $applicant->pivot->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        {{-- Tombol Accept/Reject (Tetap pakai form POST biasa) --}}
                                        <form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $applicant->id]) }}" class="inline">
                                            @csrf @method('PATCH') <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn-success btn-sm"> Accept </button>
                                        </form>
                                        <form method="POST" action="{{ route('projects.application.updateStatus', ['project' => $project->id, 'user' => $applicant->id]) }}" class="inline">
                                            @csrf @method('PATCH') <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn-danger btn-sm"> Reject </button>
                                        </form>
                                        {{-- Menu lihat profil --}}
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600"> <svg class="h-5 w-5">...</svg> </button>
                                            <div x-show="open" @click.away="open = false" x-transition class="..." style="display: none;">
                                                <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $applicant->id]) }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"> View Profile </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic mt-4">No pending applications.</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Helper CSS Classes (tambahkan ke app.css jika belum ada) --}}
    @push('styles')
    <style>
        .btn-sm { @apply px-3 py-1 text-xs font-medium rounded shadow-sm; }
        .btn-success { @apply bg-green-600 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500; }
        .btn-danger { @apply bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500; }
    </style>
    @endpush

</div>