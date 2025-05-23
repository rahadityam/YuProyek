<div> {{-- Root Element --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Riwayat Slip Gaji - {{ $project->name }}</h2>
            {{-- Optional: Tombol Aksi Tambahan --}}
        </div>

         <!-- Tabs -->
         <div class="border-b border-gray-200 mb-6 no-print"> {{-- no-print jika perlu --}}
             <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                 <a href="{{ route('projects.payroll.calculate', $project) }}" wire:navigate
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                     Perhitungan Gaji
                 </a>
                 <a href="{{ route('projects.payslips.create', $project) }}" wire:navigate
                    class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                     Pembuatan Slip Gaji
                 </a>
                  <a href="{{ route('projects.payslips.history', $project) }}" wire:navigate
                     class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                      Riwayat Slip Gaji
                  </a>
             </nav>
         </div>

        {{-- Filter Form (Gunakan wire:model, wire:click) --}}
         <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200 no-print">
             {{-- Tidak perlu <form> --}}
                 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                     {{-- Search --}}
                     <div>
                         <label for="filterSearch" class="label-text">Cari (Nama/Pekerja/Catatan/Termin)</label>
                         <input wire:model.debounce.500ms="filterSearch" type="text" id="filterSearch" placeholder="Masukkan kata kunci..." class="input-field w-full">
                     </div>
                     {{-- Worker Filter --}}
                     <div>
                         <label for="filterUserId" class="label-text">Pekerja</label>
                         <select wire:model="filterUserId" id="filterUserId" class="input-field w-full">
                             <option value="">Semua Pekerja</option>
                             @foreach ($workers as $worker)
                                 <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                             @endforeach
                         </select>
                     </div>
                      {{-- Payment Type Filter --}}
                     <div>
                         <label for="filterPaymentType" class="label-text">Tipe Slip</label>
                         <select wire:model="filterPaymentType" id="filterPaymentType" class="input-field w-full">
                             <option value="">Semua Tipe</option>
                              @foreach ($paymentTypes as $type)
                                 <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                              @endforeach
                         </select>
                     </div>
                     {{-- Date Filter --}}
                     <div class="grid grid-cols-2 gap-2">
                         <div>
                             <label for="filterDateFrom" class="label-text">Dari Tgl Sah</label>
                             <input wire:model="filterDateFrom" type="date" id="filterDateFrom" class="input-field w-full">
                         </div>
                         <div>
                             <label for="filterDateTo" class="label-text">Sampai Tgl Sah</label>
                             <input wire:model="filterDateTo" type="date" id="filterDateTo" class="input-field w-full">
                         </div>
                     </div>
                 </div>
                  <div class="mt-4 flex justify-end space-x-2 items-center">
                        {{-- Loading Indicator --}}
                        <div wire:loading wire:target="filterSearch, filterUserId, filterPaymentType, filterDateFrom, filterDateTo, resetFilters"
                             class="text-sm text-gray-500 italic">
                            Applying filters...
                        </div>
                       <button wire:click="resetFilters" type="button" class="btn-secondary">
                           Reset Filter
                       </button>
                       {{-- Tombol Apply tidak perlu --}}
                  </div>
             {{-- </form> --}}
         </div>

        {{-- Approved Payslip Table --}}
         <div class="bg-white shadow overflow-hidden sm:rounded-md">
             <div class="overflow-x-auto">
                  {{-- Loading Indicator Tabel --}}
                  <div wire:loading.flex wire:target="render, sortBy" class="py-6 px-4 items-center justify-center">
                      <svg class="animate-spin h-5 w-5 text-indigo-600 mr-2">...</svg>
                      <span>Loading history...</span>
                  </div>

                 <table class="min-w-full divide-y divide-gray-200" wire:loading.remove wire:target="render, sortBy">
                     <thead class="bg-gray-50">
                         <tr>
                              @php $sortIndicator = fn($field) => $sortField === $field ? ($sortDirection === 'asc' ? '↑' : '↓') : ''; @endphp
                             <th scope="col" class="th-cell"><button wire:click="sortBy('approved_at')" class="sort-button">Tgl Disetujui {!! $sortIndicator('approved_at') !!}</button></th>
                             <th scope="col" class="th-cell"><button wire:click="sortBy('user_name')" class="sort-button">Pekerja {!! $sortIndicator('user_name') !!}</button></th>
                             <th scope="col" class="th-cell"><button wire:click="sortBy('payment_type')" class="sort-button">Tipe {!! $sortIndicator('payment_type') !!}</button></th>
                             <th scope="col" class="th-cell"><button wire:click="sortBy('payment_name')" class="sort-button">Nama Slip {!! $sortIndicator('payment_name') !!}</button></th>
                             <th scope="col" class="th-cell"><button wire:click="sortBy('term_name')" class="sort-button">Termin {!! $sortIndicator('term_name') !!}</button></th> {{-- Kolom Termin --}}
                             <th scope="col" class="th-cell text-right"><button wire:click="sortBy('amount')" class="sort-button">Nominal {!! $sortIndicator('amount') !!}</button></th>
                             <th scope="col" class="th-cell"><button wire:click="sortBy('approver_name')" class="sort-button">Disetujui Oleh {!! $sortIndicator('approver_name') !!}</button></th>
                             <th scope="col" class="th-cell text-center">Aksi</th>
                         </tr>
                     </thead>
                     <tbody class="bg-white divide-y divide-gray-200">
                         @forelse ($approvedPayslips as $payslip)
                             <tr wire:key="payslip-{{ $payslip->id }}">
                                 <td class="td-cell text-gray-500">{{ $payslip->approved_at ? $payslip->approved_at->format('d/m/Y H:i') : '-' }}</td>
                                 <td class="td-cell font-medium text-gray-900">{{ $payslip->user->name ?? 'N/A' }}</td>
                                 <td class="td-cell text-gray-500 capitalize">{{ $payslip->payment_type }}</td>
                                 <td class="td-cell text-gray-500" title="{{ $payslip->payment_name }}">{{ \Illuminate\Support\Str::limit($payslip->payment_name, 30) }}</td>
                                 <td class="td-cell text-gray-500">{{ $payslip->paymentTerm->name ?? '-' }}</td> {{-- Tampilkan nama termin --}}
                                 <td class="td-cell text-right text-gray-500">Rp {{ number_format($payslip->amount, 0, ',', '.') }}</td>
                                 <td class="td-cell text-gray-500">{{ $payslip->approver->name ?? 'N/A' }}</td>
                                 <td class="td-cell text-center">
                                     <div class="flex justify-center space-x-3">
                                         {{-- Link ke detail (bisa Livewire atau Controller) --}}
                                         <a href="{{ route('projects.payslips.show', [$project, $payslip]) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                            <svg class="h-5 w-5">...</svg> {{-- Icon detail --}}
                                         </a>
                                          {{-- Tombol Print bisa langsung dari sini jika diperlukan --}}
                                          {{-- <a href="{{ route('projects.payslips.show', [$project, $payslip]) }}?print=1" target="_blank" class="text-gray-600 hover:text-gray-900" title="Print">
                                            <svg class="h-5 w-5">...</svg> {{-- Icon print --}}
                                          {{-- </a> --}}
                                     </div>
                                 </td>
                             </tr>
                         @empty
                             <tr>
                                 <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                                     Tidak ada riwayat slip gaji yang sesuai filter.
                                 </td>
                             </tr>
                         @endforelse
                     </tbody>
                 </table>
             </div>
             {{-- Pagination Livewire --}}
              @if ($approvedPayslips->hasPages())
                 <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                     {{ $approvedPayslips->links() }}
                 </div>
              @endif
         </div>
    </div>

     {{-- Tambahkan CSS utility jika belum ada global --}}
     @push('styles')
        <style>
            .input-field { @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm; }
            .label-text { @apply block text-sm font-medium text-gray-700; }
            .btn-primary { @apply inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50; }
            .btn-secondary { @apply inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
            .th-cell { @apply px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider; }
            .td-cell { @apply px-4 py-4 whitespace-nowrap text-sm; }
            .sort-button { @apply font-medium text-gray-500 uppercase tracking-wider hover:text-indigo-700 focus:outline-none; }
            .sort-button span { @apply ml-1; }
        </style>
     @endpush
</div>