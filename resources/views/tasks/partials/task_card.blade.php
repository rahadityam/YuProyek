<div x-data="{ showMenu: false }" 
     class="task bg-white p-4 shadow-md border-l-4 border-{{ $color }} mb-3 cursor-grab relative"
     data-id="{{ $task->id }}" 
     data-status="{{ $task->status }}" 
     data-order="{{ $task->order }}">
    <div class="flex justify-between">
        <h4 class="font-bold text-gray-800 text-lg">{{ $task->title }}</h4>
        
        <!-- Three dot menu -->
        <div class="relative">
            <button @click="showMenu = !showMenu" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="12" cy="5" r="1"></circle>
                    <circle cx="12" cy="19" r="1"></circle>
                </svg>
            </button>
            
            <!-- Dropdown menu -->
            <div x-show="showMenu" @click.away="showMenu = false" 
     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-30">
                <!-- Edit Task Link -->
                <a href="#" 
                   class="edit-task-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                   data-task-id="{{ $task->fresh()->id }}"
                   data-task-data="{{ json_encode([
                       'id' => $task->fresh()->id,
                       'title' => $task->fresh()->title,
                       'description' => $task->fresh()->description,
                       'difficulty_level' => $task->fresh()->difficulty_level,
                       'priority_level' => $task->fresh()->priority_level,
                       'start_time' => $task->fresh()->start_time,
                       'end_time' => $task->fresh()->end_time,
                       'assigned_to' => $task->fresh()->assigned_to,
                       'status' => $task->fresh()->status
                   ]) }}">
                    Edit Task
                </a>
                
                <!-- Delete Task Form (modify in your task_card.blade.php) -->
<form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="block" data-task-id="{{ $task->id }}">
    @csrf
    @method('DELETE')
    <input type="hidden" name="project_id" value="{{ $task->project_id }}">
    <button type="submit" class="delete-task-btn w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
        Delete Task
    </button>
</form>
            </div>
        </div>
    </div>

    <!-- Task description if available -->
    @if ($task->description)
        <p class="text-gray-600 mt-1">{{ Str::limit($task->description, 100) }}</p>
    @endif

    <!-- Additional Indicators -->
    <div class="flex flex-wrap items-center gap-2 mt-2 text-sm">
        @if($task->difficulty_level)
            @php
                $difficultyColors = [
                    '1' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'Very Easy'],
                    '2' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'label' => 'Easy'],
                    '3' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'label' => 'Normal'],
                    '4' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'label' => 'Hard'],
                    '5' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Very Hard'],
                ];
                $diffClass = $difficultyColors[$task->difficulty_level] ?? 
                    ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Level ' . $task->difficulty_level];
            @endphp
            <span class="{{ $diffClass['bg'] }} {{ $diffClass['text'] }} px-2 py-1 rounded text-xs font-semibold">
                {{ $diffClass['label'] }}
            </span>
        @endif
    
        @if($task->priority_level)
            @php
                $priorityColors = [
                    '1' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'Very Low'],
                    '2' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'label' => 'Low'],
                    '3' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'label' => 'Normal'],
                    '4' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'label' => 'High'],
                    '5' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Very High'],
                ];
                $prioClass = $priorityColors[$task->priority_level] ?? 
                    ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Priority ' . $task->priority_level];
            @endphp
            <span class="{{ $prioClass['bg'] }} {{ $prioClass['text'] }} px-2 py-1 rounded text-xs font-semibold">
                {{ $prioClass['label'] }}
            </span>
        @endif
    
        @if($task->end_time)
            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs flex items-center">
                @php
                    $date = \Carbon\Carbon::parse($task->end_time);
                    $day = $date->format('d');
                    $month_map = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 
                        6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 
                        11 => 'Nov', 12 => 'Dec'
                    ];
                    $month = $month_map[$date->month];
                @endphp
                {{ $day }} {{ $month }}
            </span>
        @endif
    </div>

    <!-- User Initials -->
    <div class="absolute bottom-2 right-2 w-8 h-8 bg-blue-500 text-white flex items-center justify-center rounded-full text-xs font-bold">
        {{ substr($task->assignedUser->name, 0, 2) }}
    </div>
</div>