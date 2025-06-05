{{-- resources/views/tasks/partials/task_card.blade.php --}}
@props(['task', 'color' => '#6b7280'])

@php
    $difficultyLevel = $task->difficultyLevel;
    $priorityLevel = $task->priorityLevel;
    $assignedUser = $task->assignedUser;
    $attachmentCount = $task->attachments_count ?? 0;
    $percentage = $task->achievement_percentage ?? 0;
    $wsmScore = $task->wsm_score;

    $percentageColor = 'bg-red-100 text-red-700';
    if ($percentage >= 100) { $percentageColor = 'bg-green-100 text-green-700'; }
    elseif ($percentage >= 75) { $percentageColor = 'bg-green-100 text-green-700'; }
    elseif ($percentage >= 50) { $percentageColor = 'bg-yellow-100 text-yellow-700'; }
    elseif ($percentage >= 25) { $percentageColor = 'bg-orange-100 text-orange-700'; }

    // BARU: Cek status pembayaran
    $isPaid = $task->payment_id !== null && $task->payment_status_text === 'Paid';
    $isPaymentDrafted = $task->payment_id !== null && $task->payment_status_text === 'Payment Drafted';
@endphp

<div x-data="{ showMenu: false }"
     class="task bg-white p-3 rounded-md shadow-sm mb-3 cursor-grab relative transition-shadow duration-200 hover:shadow-md border border-gray-200
            {{ $isPaid ? 'opacity-75 !cursor-not-allowed' : '' }}
            {{ $isPaymentDrafted ? 'border-l-4 border-blue-400' : '' }}" {{-- Indikator border jika draft --}}
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
     data-can-move="{{ $task->can_move ? 'true' : 'false' }}" {{-- Ini sudah ada dan akan otomatis false jika paid & approved --}}
     @click.stop="$dispatch('open-task-modal', { taskId: {{ $task->id }} })">

     {{-- BARU: Overlay jika sudah dibayar --}}
     @if($isPaid)
        <div class="absolute inset-0 bg-gray-100 bg-opacity-30 z-10 flex items-center justify-center">
            {{-- Anda bisa menambahkan ikon atau teks di sini jika mau --}}
        </div>
     @endif

     <div class="flex justify-between items-start mb-2">
        <div class="flex items-center min-w-0">
            <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $percentageColor }} mr-2 flex-shrink-0">
                {{ $percentage }}%
            </span>
            <h4 class="font-semibold text-gray-800 text-sm break-words pr-5 truncate">
                {{ $task->title }}
            </h4>
            {{-- BARU: Indikator Pembayaran --}}
            @if($isPaid)
                <span class="ml-2 text-xs font-semibold text-green-600 bg-green-100 px-1.5 py-0.5 rounded-full flex items-center" title="This task has been paid and approved.">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Paid
                </span>
            @elseif($isPaymentDrafted)
                 <span class="ml-2 text-xs font-semibold text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded-full" title="Payment for this task is in draft.">
                    Drafted
                </span>
            @endif
        </div>

        {{-- Tombol aksi (delete) --}}
        {{-- MODIFIKASI: Sembunyikan jika sudah dibayar (opsional, karena policy juga mencegah) --}}
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
                    @can('delete', $task) {{-- Double check can, meskipun sudah di atas --}}
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
        @elseif ($isPaid && Auth::user()->can('view', $task)) {{-- Jika sudah dibayar, mungkin tampilkan info saja, bukan aksi --}}
            {{-- Bisa tambahkan ikon info atau biarkan kosong --}}
        @endif
    </div>

    {{-- ... sisa card ... --}}
    @if ($task->description)
        <p class="text-gray-600 text-xs mt-1 mb-2 break-words">
            {{ Str::limit($task->description, 70) }}
        </p>
    @endif

    <div class="flex justify-between items-end">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
            @if($attachmentCount > 0)
                <span class="text-gray-500 flex items-center" title="{{ $attachmentCount }} Attachments">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a3 3 0 006 0V7a1 1 0 112 0v4a5 5 0 01-10 0V7a3 3 0 013-3h1z" clip-rule="evenodd" />
                    </svg>
                    {{ $attachmentCount }}
                </span>
            @endif

            @if($difficultyLevel)
                <span class="px-1.5 py-0.5 rounded font-medium"
                      title="Difficulty: {{ $difficultyLevel->name }}"
                      style="color: {{ $difficultyLevel->color }}; background-color: {{ $difficultyLevel->color }}20;">
                    {{ $difficultyLevel->name }}
                </span>
            @endif

            @if($priorityLevel)
                <span class="px-1.5 py-0.5 rounded font-medium"
                      title="Priority: {{ $priorityLevel->name }}"
                      style="color: {{ $priorityLevel->color }}; background-color: {{ $priorityLevel->color }}20;">
                    {{ $priorityLevel->name }}
                </span>
            @endif

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

            <span class="text-gray-500 flex items-center" title="WSM Score">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.82.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.82-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                <span class="font-medium">{{ number_format($wsmScore, 2) }}</span>
            </span>
        </div>

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