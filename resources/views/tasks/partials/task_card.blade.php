{{-- resources/views/tasks/partials/task_card.blade.php --}}
@props(['task', 'color' => '#6b7280'])

@php
    // Ambil semua data yang dibutuhkan di awal
    $difficultyLevel = $task->difficultyLevel;
    $priorityLevel = $task->priorityLevel;
    $assignedUser = $task->assignedUser;
    $attachmentCount = $task->attachments_count ?? 0;

    // Data untuk persentase
    $progressPercentage = $task->progress_percentage ?? 0;
    $achievementPercentage = $task->achievement_percentage ?? 0;

    // Data untuk tanggal
    $dueDate = $task->end_time ? \Carbon\Carbon::parse($task->end_time) : null;
    $isOverdue = $dueDate && $dueDate->isPast() && $task->status !== 'Done';
    $dateColor = $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-500';

    // Data untuk status pembayaran
    $isPaid = $task->payment_id !== null && $task->payment_status_text === 'Paid';
    $isPaymentDrafted = $task->payment_id !== null && $task->payment_status_text === 'Payment Drafted';
    
    // Logika untuk menentukan warna Achievement Percentage
    $achievementColorClass = 'bg-red-100 text-red-800'; // Default untuk < 25%
    if ($achievementPercentage >= 100) {
        $achievementColorClass = 'bg-blue-100 text-blue-800';
    } elseif ($achievementPercentage >= 75) {
        $achievementColorClass = 'bg-green-100 text-green-800';
    } elseif ($achievementPercentage >= 50) {
        $achievementColorClass = 'bg-yellow-100 text-yellow-800';
    } elseif ($achievementPercentage >= 25) {
        $achievementColorClass = 'bg-orange-100 text-orange-700';
    }
@endphp

<div x-data="{ showMenu: false }"
     class="task bg-white p-3 rounded-md shadow-sm mb-3 cursor-grab relative transition-shadow duration-200 hover:shadow-md border border-gray-200
            {{ $isPaid ? 'opacity-75 !cursor-not-allowed' : '' }}
            {{ $isPaymentDrafted ? 'border-l-4 border-blue-400' : '' }}"
     data-id="{{ $task->id }}"
     data-status="{{ $task->status }}"
     data-order="{{ $task->order ?? 0 }}"
     data-title-lower="{{ strtolower($task->title) }}"
     data-desc-lower="{{ strtolower(Str::limit($task->description ?? '', 1000)) }}"
     data-assigned-user-id="{{ $task->assigned_to ?? '' }}"
     data-difficulty-id="{{ $task->difficulty_level_id ?? '' }}"
     data-priority-id="{{ $task->priority_level_id ?? '' }}"
     data-start-date="{{ $task->start_time ? date('Y-m-d', strtotime($task->start_time)) : '' }}"
     data-end-date="{{ $task->end_time ? date('Y-m-d', strtotime($task->end_time)) : '' }}"
     data-can-move="{{ $task->can_move ? 'true' : 'false' }}"
     @click.stop="$dispatch('open-task-modal', { taskId: {{ $task->id }} })">

    {{-- Overlay jika sudah dibayar --}}
    @if($isPaid)
        <div class="absolute inset-0 bg-gray-100 bg-opacity-30 z-10"></div>
    @endif

    {{-- Baris Judul dan Menu Aksi --}}
    <div class="flex justify-between items-start mb-1">
        <h4 class="font-semibold text-gray-800 text-sm break-words pr-5 truncate">
            {{ $task->title }}
        </h4>
        @if(Auth::user()->can('delete', $task) && !$isPaid)
            <div class="relative flex-shrink-0">
                <button @click.stop="showMenu = !showMenu" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 -mr-1 -mt-1 rounded hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 10a2 2 0 110-4 2 2 0 010 4zM10 14a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                </button>
                <div x-show="showMenu" @click.away="showMenu = false" @keydown.escape.window="showMenu = false" x-transition
                     class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-30"
                     style="display: none;">
                    <div class="py-1" role="menu" aria-orientation="vertical">
                        @can('delete', $task)
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="block" data-task-id="{{ $task->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click.stop
                                    class="delete-task-btn w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-800"
                                    role="menuitem">
                                Delete Task
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Due Date --}}
    @if($dueDate)
        <div class="flex items-center text-xs {{ $dateColor }} mb-2" title="Due Date: {{ $dueDate->format('d M Y') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span>{{ $dueDate->format('d M Y') }}</span>
            @if($isOverdue)
                <span class="font-semibold ml-1">(Overdue)</span>
            @endif
        </div>
    @endif

    {{-- Baris Bawah: Info & Avatar --}}
    <div class="flex justify-between items-end">
        {{-- Kiri: Tags, Attachment, Persentase --}}
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
            @if($attachmentCount > 0)
                <span class="text-gray-500 flex items-center" title="{{ $attachmentCount }} Attachments">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a3 3 0 006 0V7a1 1 0 112 0v4a5 5 0 01-10 0V7a3 3 0 013-3h1z" clip-rule="evenodd" /></svg>
                    {{ $attachmentCount }}
                </span>
            @endif

            @if($difficultyLevel)
                <span class="px-1.5 py-0.5 rounded font-medium" title="Difficulty: {{ $difficultyLevel->name }}" style="color: {{ $difficultyLevel->color }}; background-color: {{ $difficultyLevel->color }}20;">
                    {{ $difficultyLevel->name }}
                </span>
            @endif

            @if($priorityLevel)
                <span class="px-1.5 py-0.5 rounded font-medium" title="Priority: {{ $priorityLevel->name }}" style="color: {{ $priorityLevel->color }}; background-color: {{ $priorityLevel->color }}20;">
                    {{ $priorityLevel->name }}
                </span>
            @endif
            
            <span class="px-1.5 py-0.5 rounded font-medium bg-gray-100 text-gray-600" title="Progress Pekerjaan">
                <span class="font-semibold">{{ $progressPercentage }}%</span>
            </span>
            
            <span class="px-1.5 py-0.5 rounded font-medium {{ $achievementColorClass }}" title="Validasi/Achievement PM">
                <span class="font-semibold">{{ $achievementPercentage }}%</span>
            </span>
        </div>

        {{-- Kanan: Avatar --}}
        <div class="flex items-center justify-center flex-shrink-0">
            @if($assignedUser)
                <div class="w-6 h-6 bg-indigo-500 text-white flex items-center justify-center rounded-full text-xs font-bold ring-1 ring-white" title="Assigned to: {{ $assignedUser->name }}">
                    {{ strtoupper(substr($assignedUser->name, 0, 1)) }}{{ isset(explode(' ', $assignedUser->name)[1]) ? strtoupper(substr(explode(' ', $assignedUser->name)[1], 0, 1)) : '' }}
                </div>
            @else
                <div class="w-6 h-6 bg-gray-300 text-gray-600 flex items-center justify-center rounded-full text-xs font-bold ring-1 ring-white" title="Unassigned">?</div>
            @endif
        </div>
    </div>
</div>