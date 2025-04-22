<x-app-layout>
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Riwayat Slip Gaji - {{ $project->name }}</h2>
            {{-- Optional: Tombol Aksi Tambahan (misal: Export All) --}}
        </div>

         <!-- Tabs -->
         <div class="border-b border-gray-200 mb-6">
             <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                 <a href="{{ route('projects.payroll.calculate', $project) }}"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                     Perhitungan Gaji
                 </a>
                 <a href="{{ route('projects.payslips.create', $project) }}"
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                     Pembuatan Slip Gaji
                 </a>
                  <a href="{{ route('projects.payslips.history', $project) }}"
                     class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                      Riwayat Slip Gaji
                  </a>
             </nav>
         </div>

        {{-- Filter Form --}}
         <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
             <form method="GET" action="{{ route('projects.payslips.history', $project) }}">
                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                     {{-- Search --}}
                     <div>
                         <label for="search" class="block text-sm font-medium text-gray-700">Cari (Nama/Pekerja/Catatan)</label>
                         <input type="text" name="search" id="search" value="{{ $request->input('search') }}" placeholder="Masukkan kata kunci..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                     </div>
                     {{-- Worker Filter --}}
                     <div>
                         <label for="user_id" class="block text-sm font-medium text-gray-700">Pekerja</label>
                         <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                             <option value="">Semua Pekerja</option>
                             @foreach ($workers as $worker)
                                 <option value="{{ $worker->id }}" @selected($request->input('user_id') == $worker->id)>{{ $worker->name }}</option>
                             @endforeach
                         </select>
                     </div>
                      {{-- Payment Type Filter --}}
                     <div>
                         <label for="payment_type" class="block text-sm font-medium text-gray-700">Tipe Slip</label>
                         <select name="payment_type" id="payment_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                             <option value="">Semua Tipe</option>
                              @foreach ($paymentTypes as $type)
                                 <option value="{{ $type }}" @selected($request->input('payment_type') == $type)>{{ ucfirst($type) }}</option>
                              @endforeach
                         </select>
                     </div>
                     {{-- Date Filter --}}
                     <div class="grid grid-cols-2 gap-2">
                         <div>
                             <label for="date_from" class="block text-sm font-medium text-gray-700">Dari Tanggal Sah</label>
                             <input type="date" name="date_from" id="date_from" value="{{ $request->input('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                         </div>
                         <div>
                             <label for="date_to" class="block text-sm font-medium text-gray-700">Sampai Tanggal Sah</label>
                             <input type="date" name="date_to" id="date_to" value="{{ $request->input('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                         </div>
                     </div>
                 </div>
                  <div class="mt-4 flex justify-end space-x-2">
                       <a href="{{ route('projects.payslips.history', $project) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                           Reset Filter
                       </a>
                       <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                           Terapkan Filter
                       </button>
                  </div>
             </form>
         </div>

        {{-- Approved Payslip Table --}}
         <div class="bg-white shadow overflow-hidden sm:rounded-md">
             <div class="overflow-x-auto">
                 <table class="min-w-full divide-y divide-gray-200">
                     <thead class="bg-gray-50">
                         <tr>
                              {{-- Sorting Links --}}
                              @php
                                  $sortLinkHistory = fn($field, $label) => route('projects.payslips.history', $project) . '?' . http_build_query(array_merge($request->query(), [
                                      'sort' => $field,
                                      'direction' => request('sort') === $field && request('direction') === 'asc' ? 'desc' : 'asc',
                                  ]));
                                  $sortIndicatorHistory = fn($field) => request('sort') === $field ? (request('direction') === 'asc' ? '↑' : '↓') : '';
                              @endphp
                             <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                 <a href="{{ $sortLinkHistory('approved_at', 'Tgl Disetujui') }}">Tgl Disetujui {!! $sortIndicatorHistory('approved_at') !!}</a>
                             </th>
                             <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                 <a href="{{ $sortLinkHistory('user_name', 'Pekerja') }}">Pekerja {!! $sortIndicatorHistory('user_name') !!}</a>
                             </th>
                              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                 <a href="{{ $sortLinkHistory('payment_type', 'Tipe') }}">Tipe {!! $sortIndicatorHistory('payment_type') !!}</a>
                             </th>
                             <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                 <a href="{{ $sortLinkHistory('payment_name', 'Nama Slip') }}">Nama Slip {!! $sortIndicatorHistory('payment_name') !!}</a>
                             </th>
                             <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                 <a href="{{ $sortLinkHistory('amount', 'Nominal') }}">Nominal {!! $sortIndicatorHistory('amount') !!}</a>
                             </th>
                             <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                 <a href="{{ $sortLinkHistory('approver_name', 'Disetujui Oleh') }}">Disetujui Oleh {!! $sortIndicatorHistory('approver_name') !!}</a>
                             </th>
                             <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                         </tr>
                     </thead>
                     <tbody class="bg-white divide-y divide-gray-200">
                         @forelse ($approvedPayslips as $payslip)
                             <tr class="hover:bg-gray-50">
                                 <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payslip->approved_at ? $payslip->approved_at->format('d/m/Y H:i') : '-' }}</td>
                                 <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $payslip->user->name ?? 'N/A' }}</td>
                                 <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $payslip->payment_type }}</td>
                                 <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500" title="{{ $payslip->payment_name }}">{{ \Illuminate\Support\Str::limit($payslip->payment_name, 35) }}</td>
                                 <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rp {{ number_format($payslip->amount, 0, ',', '.') }}</td>
                                 <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payslip->approver->name ?? 'N/A' }}</td>
                                 <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                     <div class="flex justify-center space-x-3">
                                         {{-- Link ke Detail --}}
                                         <a href="{{ route('projects.payslips.show', [$project, $payslip]) }}" class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail Slip Gaji">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /> </svg>
                                         </a>
                                         {{-- Tombol Print/Export (jika diperlukan langsung dari sini) --}}
                                          <a href="{{ route('projects.payslips.show', [$project, $payslip]) }}?print=1" target="_blank" class="text-gray-600 hover:text-gray-900" title="Print Slip Gaji">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /> </svg>
                                          </a>
                                     </div>
                                 </td>
                             </tr>
                         @empty
                             <tr>
                                 <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                                     Tidak ada riwayat slip gaji yang sudah disetujui.
                                 </td>
                             </tr>
                         @endforelse
                     </tbody>
                 </table>
             </div>
             {{-- Pagination --}}
              @if ($approvedPayslips instanceof \Illuminate\Pagination\LengthAwarePaginator && $approvedPayslips->hasPages())
                 <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                     {{ $approvedPayslips->links('vendor.pagination.tailwind') }}
                 </div>
              @endif
         </div>
    </div>
</x-app-layout>