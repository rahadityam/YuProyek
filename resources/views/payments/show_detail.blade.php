<x-app-layout>
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-900">Detail Pembayaran - {{ $project->name }}</h2>
                 <!-- Back Button -->
                 <a href="{{ route('projects.pembayaran', $project) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                     <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                       <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                     </svg>
                     Kembali ke Riwayat Pembayaran
                 </a>
            </div>

            <!-- Payment Details Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Pembayaran</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Detail bukti pembayaran yang diunggah.</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Nama Pembayaran</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payment->payment_name }}</dd>
                        </div>
                         <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Pekerja Penerima</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payment->user->name ?? 'N/A' }}</dd>
                        </div>
                         <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Rekening Tujuan</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payment->bank_account ?? 'N/A' }}</dd>
                        </div>
                        <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Nominal</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">Rp {{ number_format($payment->amount, 0, ',', '.') }}</dd>
                        </div>
                        <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Tanggal Upload</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payment->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                         <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                 @php
                                     $statusClasses = [
                                         'pending' => 'bg-yellow-100 text-yellow-800',
                                         'completed' => 'bg-green-100 text-green-800',
                                         'rejected' => 'bg-red-100 text-red-800',
                                     ];
                                     $statusClass = $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800';
                                 @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </dd>
                        </div>
                        <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Bukti</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @if ($payment->proof_image)
                                    <a href="{{ Storage::url($payment->proof_image) }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-900 hover:underline">Lihat Bukti</a>
                                @else
                                    <span class="text-gray-400">Tidak ada bukti</span>
                                @endif
                            </dd>
                        </div>
                        <div class="py-3 sm:py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payment->notes ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

             <!-- Linked Tasks Section -->
             <h3 class="text-lg font-medium text-gray-800 mb-3 mt-8">Tugas Terkait Pembayaran Ini</h3>
             <div class="bg-white shadow overflow-hidden sm:rounded-md">
                 @if($payment->tasks->count() > 0)
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                 <tr>
                                     <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas</th>
                                     <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kesulitan</th>
                                     <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioritas</th>
                                     <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Achiev. (%)</th>
                                     <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Skor WSM</th>
                                      {{-- <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Task (Rp)</th> --}}
                                 </tr>
                             </thead>
                             <tbody class="bg-white divide-y divide-gray-200">
                                 @foreach($payment->tasks as $task)
                                     <tr>
                                         <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $task->title }}</td>
                                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                             {{ $task->difficultyLevel->name ?? 'N/A' }}
                                             ({{ $task->difficultyLevel->value ?? '-' }})
                                         </td>
                                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                             {{ $task->priorityLevel->name ?? 'N/A' }}
                                             ({{ $task->priorityLevel->value ?? '-' }})
                                         </td>
                                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $task->achievement_percentage }}%</td>
                                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold text-center">{{ $task->wsm_score }}</td>
                                           {{-- <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right"> --}}
                                            {{-- number_format($task->calculated_value, 0, ',', '.') --}} {{-- Uncomment if calculated_value is implemented --}}
                                        {{-- </td> --}}
                                     </tr>
                                 @endforeach
                             </tbody>
                         </table>
                     </div>
                 @else
                     <div class="px-4 py-5 sm:px-6 text-center">
                         <p class="text-gray-500">Tidak ada tugas yang terkait dengan pembayaran ini.</p>
                         {{-- Add button to link tasks manually? Might be complex --}}
                     </div>
                 @endif
             </div>

        </div>
    </x-app-layout>