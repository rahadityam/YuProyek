<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    User
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('action')" class="hover:text-indigo-700">
                        Action <span x-show="filters.sort_by === 'action'" x-text="filters.sort_dir === 'asc' ? '↑' : '↓'"></span>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('created_at')" class="hover:text-indigo-700">
                        Date & Time <span x-show="filters.sort_by === 'created_at'" x-text="filters.sort_dir === 'asc' ? '↑' : '↓'"></span>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($logs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-700 font-medium">{{ substr($log->user->name ?? 'S', 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</div>
                                <div class="text-sm text-gray-500">{{ $log->user->email ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $log->action == 'created' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $log->action == 'updated' || $log->action == 'status_changed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $log->action == 'deleted' || $log->action == 'rejected_application' || $log->action == 'removed_member' ? 'bg-red-100 text-red-800' : '' }}
                            bg-gray-100 text-gray-800">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 break-words">{{ $log->description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">No activity logs found matching your criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>