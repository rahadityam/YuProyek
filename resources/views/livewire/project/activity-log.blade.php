<div> {{-- Root element wajib --}}

    {{-- Padding utama (bisa dihapus jika sudah ada di layout) --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">

        {{-- Header Halaman (Opsional) --}}
        {{-- <h1 class="text-2xl font-semibold text-gray-900 mb-6">Activity Log - {{ $project->name }}</h1> --}}

        <!-- Filter Form (Gunakan wire:model dan wire:click) -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium mb-4">Filter Activities</h2>
            {{-- Tidak perlu tag <form> --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- User Filter --}}
                <div>
                    <label for="filterUserId" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    {{-- wire:model terhubung ke properti $filterUserId --}}
                    <select wire:model="filterUserId" id="filterUserId" class="input-field w-full"> {{-- Gunakan class utility jika ada --}}
                        <option value="">All Users</option>
                        {{-- Loop dari properti publik $users --}}
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Filter --}}
                <div>
                    <label for="filterAction" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select wire:model="filterAction" id="filterAction" class="input-field w-full">
                        <option value="">All Actions</option>
                        {{-- Opsi bisa digenerate dinamis jika perlu --}}
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                        <option value="status_changed">Status Changed</option>
                        <option value="commented">Commented</option>
                        <option value="attached">Attached File</option>
                        <option value="detached">Detached File</option>
                        <option value="applied">Applied</option>
                        <option value="accepted_application">Accepted Application</option>
                        <option value="rejected_application">Rejected Application</option>
                        <option value="removed_member">Removed Member</option>
                        {{-- Tambahkan action lain yang relevan --}}
                    </select>
                </div>

                {{-- Date From Filter --}}
                <div>
                    <label for="filterDateFrom" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input wire:model="filterDateFrom" type="date" id="filterDateFrom" class="input-field w-full">
                </div>

                {{-- Date To Filter --}}
                <div>
                    <label for="filterDateTo" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input wire:model="filterDateTo" type="date" id="filterDateTo" class="input-field w-full">
                </div>

                {{-- Tombol Reset & Loading Indicator --}}
                <div class="lg:col-span-4 flex justify-end items-center mt-2">
                     {{-- Loading indicator untuk filter --}}
                     <div wire:loading wire:target="filterUserId, filterAction, filterDateFrom, filterDateTo, resetFilters"
                          class="text-sm text-gray-500 mr-3 italic">
                         Applying filter...
                     </div>
                    {{-- Tombol Reset --}}
                    <button wire:click="resetFilters" type="button" class="btn-secondary"> {{-- Gunakan class utility jika ada --}}
                        Clear Filters
                    </button>
                    {{-- Tombol Apply tidak diperlukan, Livewire otomatis update --}}
                </div>
            </div>
        </div>

        <!-- Activity List -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-medium">Activity Logs</h2>
                 {{-- Loading indicator untuk tabel --}}
                 <div wire:loading wire:target="render" class="text-sm text-gray-500 italic">
                     <svg class="animate-spin h-4 w-4 inline-block mr-1" ... >...</svg>
                     Updating list...
                 </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="th-cell">User</th> {{-- Gunakan class utility --}}
                            <th scope="col" class="th-cell">Action</th>
                            <th scope="col" class="th-cell">Description</th>
                            <th scope="col" class="th-cell">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr wire:key="log-{{ $log->id }}"> {{-- wire:key untuk optimasi Livewire --}}
                                <td class="td-cell"> {{-- Gunakan class utility --}}
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10"> {{-- Avatar --}}
                                             <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center"> <span class="text-indigo-700 font-medium">{{ $log->user ? substr($log->user->name, 0, 1) : 'S' }}</span> </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</div>
                                            <div class="text-sm text-gray-500">{{ $log->user->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-cell">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{-- Logika warna badge --}}
                                        @if(in_array($log->action, ['created', 'accepted_application'])) bg-green-100 text-green-800
                                        @elseif(in_array($log->action, ['updated', 'status_changed', 'commented'])) bg-blue-100 text-blue-800
                                        @elseif(in_array($log->action, ['deleted', 'rejected_application', 'removed_member', 'detached'])) bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td class="td-cell text-sm text-gray-600 break-words">{{ $log->description }}</td>
                                <td class="td-cell whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                                    No activity logs found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Livewire -->
            @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    {{-- </div> --}} {{-- End Padding Utama --}}

     {{-- Tambahkan CSS utility jika belum ada global --}}
     @push('styles')
        <style>
            .input-field { @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm; }
            .input-error { @apply border-red-500 ring-red-500; }
            .input-error-msg { @apply mt-1 text-xs text-red-600; }
            .label-text { @apply block text-sm font-medium text-gray-700 mb-1; }
            .btn-primary { @apply inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50; }
            .btn-secondary { @apply inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
            .th-cell { @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider; }
            .td-cell { @apply px-6 py-4; }
        </style>
     @endpush

</div>