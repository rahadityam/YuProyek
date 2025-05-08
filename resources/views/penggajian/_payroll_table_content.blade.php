{{-- resources/views/penggajian/_payroll_table_content.blade.php --}}
{{-- This partial only contains the task table and pagination --}}

<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                     {{-- Sorting Link Helpers --}}
                     @php
                         $baseUrl = route('projects.payroll.calculate', $project);
                         $currentParams = $request->except('page');
                         $sortLink = function($field, $label) use ($baseUrl, $currentParams, $request) {
                             $queryParams = array_merge($currentParams, [
                                 'sort' => $field,
                                 'direction' => $request->input('sort') === $field && $request->input('direction') === 'asc' ? 'desc' : 'asc',
                             ]);
                             return $baseUrl . '?' . http_build_query($queryParams);
                         };
                         $sortIndicator = fn($field) => $request->input('sort') === $field ? ($request->input('direction') === 'asc' ? '↑' : '↓') : '';
                     @endphp

                    {{-- Table Headers with Sort Links (matching controller's allowedSorts) --}}
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('title')" class="hover:text-indigo-700">
                           Tugas {!! $sortIndicator('title') !!}
                        </a>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="#" @click.prevent="sortBy('assigned_user_name')" class="hover:text-indigo-700">
                            Pekerja {!! $sortIndicator('assigned_user_name') !!}
                        </a>
                    </th>
                     <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                         <a href="#" @click.prevent="sortBy('difficulty_value')" class="hover:text-indigo-700">
                             Kesulitan {!! $sortIndicator('difficulty_value') !!}
                         </a>
                     </th>
                     <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                         <a href="#" @click.prevent="sortBy('priority_value')" class="hover:text-indigo-700">
                             Prioritas {!! $sortIndicator('priority_value') !!}
                         </a>
                     </th>
                     <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                         <a href="#" @click.prevent="sortBy('achievement_percentage')" class="hover:text-indigo-700">
                             Achiev (%) {!! $sortIndicator('achievement_percentage') !!}
                         </a>
                     </th>
                     {{-- Columns NOT sortable by DB query --}}
                     <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Weighted Sum Model Score">
                         Skor
                     </th>
                      <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="Nilai dasar dari standar upah pekerja">
                         Nilai Dasar (Rp)
                     </th>
                     <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="Nilai Akhir = Skor * Nilai Dasar * Persentase Achievement">
                         Nilai Akhir (Rp)
                     </th>
                     {{-- End non-sortable columns --}}
                     <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                         <a href="#" @click.prevent="sortBy('payment_status')" class="hover:text-indigo-700">
                             Status Bayar {!! $sortIndicator('payment_status') !!}
                         </a>
                     </th>
                      <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                         <a href="#" @click.prevent="sortBy('updated_at')" class="hover:text-indigo-700">
                             Tgl Selesai {!! $sortIndicator('updated_at') !!}
                         </a>
                     </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{-- Loop through PAGINATED tasks passed from controller --}}
                @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50">
                         <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $task->title }}</td>
                         {{-- Use alias from join first, fallback to relation if needed --}}
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $task->assigned_user_name ?? $task->assignedUser?->name ?? 'N/A' }}</td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                             {{ $task->difficultyLevel?->name ?? 'N/A' }}
                             ({{ $task->difficulty_value ?? $task->difficultyLevel?->value ?? '-' }}) {{-- Use alias --}}
                         </td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                             {{ $task->priorityLevel?->name ?? 'N/A' }}
                             ({{ $task->priority_value ?? $task->priorityLevel?->value ?? '-' }}) {{-- Use alias --}}
                         </td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $task->achievement_percentage ?? 100 }}%</td>
                         {{-- Call accessors directly here --}}
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold text-center">{{ number_format($task->wsm_score, 2, ',', '.') }}</td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                             Rp {{ number_format($task->base_value, 0, ',', '.') }}
                         </td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800 font-bold text-right">
                              Rp {{ number_format($task->calculated_value, 0, ',', '.') }}
                         </td>
                         <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                              @if($task->payment_id)
                                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                      Dibayar
                                  </span>
                                  {{-- Ensure payment relation is loaded in Controller if you want to use the link --}}
                                   @if($task->relationLoaded('payment') && $task->payment)
                                       <a href="{{ route('projects.payslips.show', [$project, $task->payment]) }}"
                                          class="ml-1 text-xs text-blue-600 hover:text-blue-800 hover:underline"
                                          title="Lihat detail pembayaran: {{ $task->payment->payment_name }} (#{{ $task->payment_id }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /> <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /> </svg>
                                       </a>
                                   @else
                                       {{-- Show only ID if relation not loaded --}}
                                        <span class="text-xs text-gray-400 ml-1">(#{{ $task->payment_id }})</span>
                                   @endif
                              @else
                                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                      Belum Dibayar
                                  </span>
                              @endif
                         </td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                             {{ $task->updated_at ? $task->updated_at->format('d/m/Y H:i') : '-'}}
                         </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                            Tidak ada data task selesai yang sesuai dengan filter yang diterapkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination (Rendered only if tasks has pages) -->
    @if ($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator && $tasks->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{-- Links will be handled by Alpine's click listener --}}
            {{ $tasks->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>