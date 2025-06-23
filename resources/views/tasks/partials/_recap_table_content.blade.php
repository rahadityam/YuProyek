<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="recap-table-container">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('title')" class="hover:text-indigo-700">Task <span x-show="filters.sort === 'title'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('difficulty_value')" class="hover:text-indigo-700">Difficulty <span x-show="filters.sort === 'difficulty_value'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('priority_value')" class="hover:text-indigo-700">Priority <span x-show="filters.sort === 'priority_value'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('assigned_user_name')" class="hover:text-indigo-700">Assigned To <span x-show="filters.sort === 'assigned_user_name'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('status')" class="hover:text-indigo-700">Status <span x-show="filters.sort === 'status'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('start_time')" class="hover:text-indigo-700">Start <span x-show="filters.sort === 'start_time'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('end_time')" class="hover:text-indigo-700">End <span x-show="filters.sort === 'end_time'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('achievement_percentage')" class="hover:text-indigo-700">Progress (%) <span x-show="filters.sort === 'achievement_percentage'" x-text="filters.direction === 'asc' ? '↑' : '↓'"></span></a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">WSM Score</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance (Rp)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4"><div class="font-medium text-gray-900 text-sm" title="{{ $task->title }}">{{ Str::limit($task->title, 40) }}</div><div class="text-xs text-gray-500" title="{{ $task->description }}">{{ Str::limit($task->description, 50) }}</div></td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $task->difficultyLevel->name ?? '-' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $task->priorityLevel->name ?? '-' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $task->assignedUser->name ?? 'Unassigned' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $task->status == 'To Do' ? 'bg-red-100 text-red-800' : '' }} {{ $task->status == 'In Progress' ? 'bg-yellow-100 text-yellow-800' : '' }} {{ $task->status == 'Review' ? 'bg-blue-100 text-blue-800' : '' }} {{ $task->status == 'Done' ? 'bg-green-100 text-green-800' : '' }}">{{ $task->status }}</span></td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $task->start_time ? $task->start_time->format('d M Y') : '-' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $task->end_time ? $task->end_time->format('d M Y') : '-' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $task->achievement_percentage }}%</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold text-center">{{ number_format($task->wsm_score, 2) }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800 font-bold text-right">Rp {{ number_format($task->calculated_value, 0, ',', '.') }}</td>
                </tr>
                @empty
                    <tr><td colspan="11" class="text-center py-10 text-gray-500 italic">No tasks found matching your criteria.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginasi disertakan di sini --}}
    @if ($tasks->hasPages())
        <div id="recap-pagination-container" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 no-print">
            {{ $tasks->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>