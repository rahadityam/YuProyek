<x-app-layout>
    <div x-data="{
        activeTab: 'members',
        showInviteModal: false,
        inviteEmail: '',
        // invitePosition: '', // Jika ingin input posisi saat invite
        isInviting: false,
        inviteFeedback: { type: '', message: '' },
        clearInviteFeedback() { setTimeout(() => this.inviteFeedback = { type: '', message: '' }, 3000); }
    }" class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">{{ $project->name }} - Team Management</h2>
                        @if($project->owner_id === auth()->id())
                            <button @click="showInviteModal = true"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Invite Worker
                            </button>
                        @endif
                    </div>

                    {{-- Tab Navigation --}}
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'members'"
                                    :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'members', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'members' }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Team Members ({{ $members->count() + 1 }}) {{-- +1 untuk owner --}}
                            </button>
                            <button @click="activeTab = 'pending'"
                                    :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'pending', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'pending' }"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Pending Invitations ({{ $pendingInvitations->count() }})
                            </button>
                        </nav>
                    </div>

                    {{-- Tab Content --}}
                    <div class="mt-6">
                        {{-- Tab: Anggota Tim --}}
                        <div x-show="activeTab === 'members'" x-transition>
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Project Owner</h3>
                                <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                                    {{-- Owner details --}}
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

                            <div>
                                <h3 class="text-lg font-semibold mb-3 border-b pb-2">Team Members</h3>
                                @if($members->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($members as $member)
                                            <div class="flex flex-col md:flex-row md:items-center p-4 bg-gray-50 rounded-lg shadow">
                                                <div class="flex items-center flex-1 mb-3 md:mb-0">
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

                                                <div class="flex items-center space-x-2 md:space-x-4 md:ml-auto">
                                                    @if($project->owner_id === auth()->id())
                                                        <!-- <div class="flex-shrink-0 w-full md:w-48">
                                                            <select id="wage_standard_member_{{ $member->id }}"
                                                                    data-member-id="{{ $member->id }}"
                                                                    data-project-id="{{ $project->id }}"
                                                                    class="wage-standard-select block w-full pl-3 pr-10 py-2 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                                                                <option value="">Pilih standar upah</option>
                                                                @foreach($wageStandards as $wageStandard)
                                                                    <option value="{{ $wageStandard->id }}" {{ $member->pivot->wage_standard_id == $wageStandard->id ? 'selected' : '' }}>
                                                                        {{ Str::limit($wageStandard->job_category, 15) }} - {{ number_format($wageStandard->task_price, 0, ',', '.') }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div> -->
                                                        <div class="relative" x-data="{ open: false }">
                                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-md hover:bg-gray-200">
                                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                                                            </button>
                                                            <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-10">
                                                                <a href="{{ route('projects.user.profile', ['project' => $project->id, 'user' => $member->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Lihat Profil</a>
                                                                <button class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                                                        onclick="if(confirm('Yakin ingin mengeluarkan anggota ini?')) { document.getElementById('remove-member-{{ $member->id }}').submit(); }">
                                                                    Keluarkan
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <form id="remove-member-{{ $member->id }}" action="{{ route('projects.team.remove', ['project' => $project->id, 'user' => $member->id]) }}" method="POST" class="hidden"> @csrf @method('DELETE') </form>
                                                    @else
                                                        @if($member->pivot->wage_standard_id && $wageStandards->find($member->pivot->wage_standard_id))
                                                            <p class="text-xs text-gray-500">{{ Str::limit($wageStandards->find($member->pivot->wage_standard_id)->job_category,15) }} ({{ number_format($wageStandards->find($member->pivot->wage_standard_id)->task_price, 0,',','.') }})</p>
                                                        @else
                                                            <p class="text-xs text-gray-400 italic">Upah belum diatur</p>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 italic">No team members yet.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Tab: Undangan Tertunda --}}
                        <div x-show="activeTab === 'pending'" x-transition>
                            <h3 class="text-lg font-semibold mb-3 border-b pb-2">Pending Invitations</h3>
                            @if($pendingInvitations->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($pendingInvitations as $pending)
                                        <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg shadow border border-yellow-200">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    @if($pending->profile_photo_path)
                                                        <img class="h-10 w-10 rounded-full" src="{{ Storage::url($pending->profile_photo_path) }}" alt="{{ $pending->name }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-gray-600">{{ substr($pending->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="font-medium text-sm text-gray-800">{{ $pending->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $pending->email }}</p>
                                                    <p class="text-xs text-yellow-700 italic">Posisi diajukan: {{ $pending->pivot->position ?? 'Belum ditentukan' }}</p>
                                                </div>
                                            </div>
                                            @if($project->owner_id === auth()->id())
                                            <div class="flex items-center space-x-2">
                                                {{-- Tombol Batalkan Undangan (oleh PM) --}}
                                                <form action="{{ route('projects.invitations.updateStatus', ['project' => $project, 'user' => $pending->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan undangan untuk {{ $pending->name }}?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="action" value="cancel_pm">
                                                    <button type="submit" title="Batalkan Undangan"
                                                            class="p-1.5 text-red-500 hover:text-red-700 rounded-md hover:bg-red-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                {{-- Opsional: Tombol Kirim Ulang Undangan --}}
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">No pending invitations.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invite Worker Modal --}}
        <div x-show="showInviteModal"
             class="fixed inset-0 z-[100] overflow-y-auto"
             aria-labelledby="invite-modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div x-show="showInviteModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showInviteModal = false; inviteEmail = ''; inviteFeedback = {type:'', message:''};" aria-hidden="true"></div>

                {{-- Modal Panel --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
                <div x-show="showInviteModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="
                        isInviting = true;
                        inviteFeedback = {type:'', message:''};
                        fetch('{{ route('projects.team.invite', $project) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ email: inviteEmail /*, position: invitePosition */ })
                        })
                        .then(response => response.json().then(data => ({ok: response.ok, status: response.status, data})))
                        .then(({ok, status, data}) => {
                            if (ok && data.success) {
                                inviteFeedback = {type: 'success', message: data.message};
                                inviteEmail = '';
                                // invitePosition = '';
                                setTimeout(() => { // Beri waktu untuk pesan sukses terlihat
                                    showInviteModal = false;
                                    if (typeof Turbo !== 'undefined') {
                                        Turbo.visit(window.location.href, { action: 'replace' });
                                    } else {
                                        window.location.reload();
                                    }
                                }, 1500);
                            } else {
                                inviteFeedback = {type: 'error', message: data.message || `Error (${status})`};
                                if (data.errors) {
                                    inviteFeedback.message += ': ' + Object.values(data.errors).flat().join(' ');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Invite error:', error);
                            inviteFeedback = {type: 'error', message: 'Network error or unexpected issue.'};
                        })
                        .finally(() => {
                            isInviting = false;
                            if (inviteFeedback.type === 'error') clearInviteFeedback(); // Clear error after 3s
                            // Untuk sukses, modal akan tertutup
                        });
                    ">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="invite-modal-title">
                                        Invite New Worker
                                    </h3>
                                    <div class="mt-4 space-y-3">
                                        <template x-if="inviteFeedback.message">
                                            <div :class="{
                                                'bg-green-100 border-green-400 text-green-700': inviteFeedback.type === 'success',
                                                'bg-red-100 border-red-400 text-red-700': inviteFeedback.type === 'error'
                                            }" class="border px-3 py-2 rounded-md text-xs" x-text="inviteFeedback.message"></div>
                                        </template>
                                        <div>
                                            <label for="invite_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                            <input type="email" name="invite_email" id="invite_email" x-model="inviteEmail" required
                                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                   placeholder="worker@example.com">
                                        </div>
                                        {{--
                                        <div>
                                            <label for="invite_position" class="block text-sm font-medium text-gray-700">Proposed Position (Optional)</label>
                                            <input type="text" name="invite_position" id="invite_position" x-model="invitePosition"
                                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                   placeholder="e.g., Frontend Developer">
                                        </div>
                                        --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="isInviting || !inviteEmail"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-show="!isInviting">Send Invitation</span>
                                <span x-show="isInviting" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Sending...
                                </span>
                            </button>
                            <button @click="showInviteModal = false; inviteEmail = ''; inviteFeedback = {type:'', message:''};" type="button" :disabled="isInviting"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-50">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wage Standard Update Logic
            const wageSelects = document.querySelectorAll('.wage-standard-select');
            wageSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const memberId = this.dataset.memberId;
                    const projectId = this.dataset.projectId;
                    const wageStandardId = this.value;

                    fetch(`/projects/${projectId}/team/${memberId}/wage`, {
                        method: 'PATCH', // or 'POST' with _method: 'PATCH' if PATCH not fully supported
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ wage_standard_id: wageStandardId })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if(data.success) {
                            console.log('Wage standard updated:', data.message);
                            // Bisa tambahkan notifikasi kecil di sini jika mau
                            // Contoh: show temp success message next to select
                            const feedbackEl = document.createElement('span');
                            feedbackEl.textContent = 'Saved!';
                            feedbackEl.className = 'text-xs text-green-600 ml-2';
                            this.parentNode.appendChild(feedbackEl);
                            setTimeout(() => feedbackEl.remove(), 2000);
                        } else {
                             console.error('Failed to update wage:', data.message);
                             alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating wage standard:', error);
                        alert('An error occurred. ' + (error.message || ''));
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>