@props(['task', 'color' => '#6b7280']) {{-- Accept $task and $color --}}

@php
    $difficultyLevel = $task->difficultyLevel;
    $priorityLevel = $task->priorityLevel;
    $assignedUser = $task->assignedUser;
    $attachmentCount = $task->attachments_count ?? 0; // Use loaded count
    $percentage = $task->achievement_percentage ?? 0;
    
    // Determine color based on percentage
    $percentageColor = 'bg-red-100 text-red-700'; // Default for low progress
    
    if ($percentage >= 100) {
        $percentageColor = 'bg-green-100 text-green-700'; // Complete
    } elseif ($percentage >= 75) {
        $percentageColor = 'bg-green-100 text-green-700'; // High progress
    } elseif ($percentage >= 50) {
        $percentageColor = 'bg-yellow-100 text-yellow-700'; // Medium progress
    } elseif ($percentage >= 25) {
        $percentageColor = 'bg-orange-100 text-orange-700'; // Low-medium progress
    }
@endphp

<div x-data="{ showMenu: false }"
     class="task bg-white p-3 rounded-md shadow-sm mb-3 cursor-grab relative transition-shadow duration-200 hover:shadow-md border border-gray-200"
     data-id="{{ $task->id }}"
     data-status="{{ $task->status }}"
     data-order="{{ $task->order ?? 0 }}"
     data-assigned-user-id="{{ $task->assigned_to ?? '' }}"
     data-difficulty-id="{{ $task->difficulty_level_id ?? '' }}"
     data-priority-id="{{ $task->priority_level_id ?? '' }}"
     data-start-date="{{ $task->start_time ? date('Y-m-d', strtotime($task->start_time)) : '' }}"
     data-end-date="{{ $task->end_time ? date('Y-m-d', strtotime($task->end_time)) : '' }}"
     @click="$dispatch('open-task-modal', { taskId: {{ $task->id }} })" {{-- Open modal on click --}}
     >
    <div class="flex justify-between items-start mb-2">
        {{-- Task Title with Percentage --}}
        <div class="flex items-center">
            {{-- Percentage Display with Dynamic Color --}}
            <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $percentageColor }} mr-2">
                {{ $percentage }}%
            </span>
            
            {{-- Task Title --}}
            <h4 class="font-semibold text-gray-800 text-sm break-words pr-5">
                {{ $task->title }}
            </h4>
        </div>

        <!-- Three dot menu (stop propagation to prevent opening modal) -->
        <div class="relative flex-shrink-0">
            <button @click.stop="showMenu = !showMenu" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 -mr-1 -mt-1 rounded hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"> 
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 10a2 2 0 110-4 2 2 0 010 4zM10 14a2 2 0 110-4 2 2 0 010 4z" /> 
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div x-show="showMenu"
                 @click.away="showMenu = false"
                 @keydown.escape.window="showMenu = false"
                 x-transition
                 class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-30"
                 style="display: none;">
                <div class="py-1" role="menu" aria-orientation="vertical">
                    {{-- Delete Form --}}
                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="block" data-task-id="{{ $task->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click.stop {{-- Stop propagation --}}
                                class="delete-task-btn w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-800"
                                role="menuitem">
                            Delete Task
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Task description (optional preview) -->
    @if ($task->description)
        <p class="text-gray-600 text-xs mt-1 mb-2 break-words">
            {{ Str::limit($task->description, 70) }}
        </p>
    @endif

    <!-- Indicators & Avatar -->
    <div class="flex justify-between items-end">
        {{-- Left Indicators --}}
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
            {{-- Attachment Indicator --}}
            @if($attachmentCount > 0)
                <span class="text-gray-500 flex items-center" title="{{ $attachmentCount }} Attachments">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a3 3 0 006 0V7a1 1 0 112 0v4a5 5 0 01-10 0V7a3 3 0 013-3h1z" clip-rule="evenodd" />
                    </svg>
                    {{ $attachmentCount }}
                </span>
            @endif

            {{-- Difficulty using color from database - text color with light background --}}
            @if($difficultyLevel)
                <span class="px-1.5 py-0.5 rounded font-medium" 
                      title="Difficulty: {{ $difficultyLevel->name }}"
                      style="color: {{ $difficultyLevel->color }}; background-color: {{ $difficultyLevel->color }}20;">
                    {{ $difficultyLevel->name }}
                </span>
            @endif

            {{-- Priority using color from database - text color with light background --}}
            @if($priorityLevel)
                <span class="px-1.5 py-0.5 rounded font-medium" 
                      title="Priority: {{ $priorityLevel->name }}"
                      style="color: {{ $priorityLevel->color }}; background-color: {{ $priorityLevel->color }}20;">
                    {{ $priorityLevel->name }}
                </span>
            @endif

            {{-- Due Date --}}
            @if($task->end_time)
                @php
                    $dueDate = \Carbon\Carbon::parse($task->end_time);
                    $isOverdue = $dueDate->isPast() && $task->status !== 'Done';
                    $isDueSoon = !$isOverdue && $dueDate->isBetween(now(), now()->addDays(3)) && $task->status !== 'Done';
                    $dateColor = $isOverdue ? 'text-red-600' : ($isDueSoon ? 'text-orange-600' : 'text-gray-500');
                @endphp
                <span class="{{ $dateColor }} flex items-center" title="Due Date: {{ $dueDate->format('d M Y') }}">
                    {{ $dueDate->format('d M') }}
                </span>
            @endif
        </div>

        {{-- Right Avatar --}}
        <div class="flex items-center justify-center flex-shrink-0">
            @if($assignedUser)
                {{-- Simple Initials Avatar --}}
                <div class="w-6 h-6 bg-indigo-500 text-white flex items-center justify-center rounded-full text-xs font-bold ring-1 ring-white" title="Assigned to: {{ $assignedUser->name }}">
                    {{ strtoupper(substr($assignedUser->name, 0, 1)) }}{{ isset(explode(' ', $assignedUser->name)[1]) ? strtoupper(substr(explode(' ', $assignedUser->name)[1], 0, 1)) : '' }}
                </div>
            @else
                <div class="w-6 h-6 bg-gray-300 text-gray-600 flex items-center justify-center rounded-full text-xs font-bold ring-1 ring-white" title="Unassigned">?</div>
            @endif
        </div>
    </div>
</div>