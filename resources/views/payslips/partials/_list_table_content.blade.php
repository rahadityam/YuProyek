<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @php
                    $currentParams = request()->except('page', '_token'); // Ambil semua parameter kecuali page dan token
                    $sortLink = function($field, $label) use ($project, $currentParams) {
                        $queryParams = array_merge($currentParams, [
                            'sort' => $field,
                            'direction' => (request('sort') === $field && request('direction') === 'asc') ? 'desc' : 'asc',
                        ]);
                        return route('projects.payslips.history', $project) . '?' . http_build_query($queryParams);
                    };
                    $sortIndicator = fn($field) => (request('sort') === $field) ? (request('direction') === 'asc' ? '↑' : '↓') : '';
                @endphp
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('status')" class="hover:text-indigo-700">Status {!! $sortIndicator('status') !!}</a>
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('updated_at')" class="hover:text-indigo-700">Tanggal {!! $sortIndicator('updated_at') !!}</a>
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('user_name')" class="hover:text-indigo-700">Pekerja {!! $sortIndicator('user_name') !!}</a>
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('payment_type')" class="hover:text-indigo-700">Tipe {!! $sortIndicator('payment_type') !!}</a>
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('payment_name')" class="hover:text-indigo-700">Nama Slip {!! $sortIndicator('payment_name') !!}</a>
                </th>
                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="#" @click.prevent="sortBy('amount')" class="hover:text-indigo-700">Nominal {!! $sortIndicator('amount') !!}</a>
                </th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                     <a href="#" @click.prevent="sortBy('approver_name')" class="hover:text-indigo-700">Disetujui Oleh {!! $sortIndicator('approver_name') !!}</a>
                </th>
                <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($payslips as $payslip)
                <tr class="hover:bg-gray-50 {{ $payslip->status == \App\Models\Payment::STATUS_DRAFT ? 'italic text-gray-600 opacity-90' : '' }}">
                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                        @if($payslip->status == \App\Models\Payment::STATUS_DRAFT)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Draft
                            </span>
                        @elseif($payslip->status == \App\Models\Payment::STATUS_APPROVED)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Approved
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($payslip->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                        {{ $payslip->status == \App\Models\Payment::STATUS_DRAFT ? ($payslip->created_at ? $payslip->created_at->format('d/m/Y H:i') : '-') : ($payslip->approved_at ? $payslip->approved_at->format('d/m/Y H:i') : '-') }}
                        <span class="block text-gray-400 text-[10px]">
                            {{ $payslip->status == \App\Models\Payment::STATUS_DRAFT ? '(Dibuat)' : '(Disetujui)' }}
                        </span>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium {{ $payslip->status == \App\Models\Payment::STATUS_DRAFT ? 'text-gray-700' : 'text-gray-900' }}">{{ $payslip->user->name ?? 'N/A' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm capitalize">{{ $payslip->payment_type }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm" title="{{ $payslip->payment_name }}">{{ \Illuminate\Support\Str::limit($payslip->payment_name, 30) }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-right">Rp {{ number_format($payslip->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $payslip->approver->name ?? ($payslip->status == \App\Models\Payment::STATUS_APPROVED ? 'N/A' : '-') }}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex justify-center space-x-3">
                            <a href="{{ route('projects.payslips.show', [$project, $payslip]) }}" class="text-indigo-600 hover:text-indigo-900" title="{{ $payslip->status == \App\Models\Payment::STATUS_DRAFT ? 'Lihat Detail & Setujui' : 'Lihat Detail Slip' }}">
                                <!-- Ikon file signature untuk status draft -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </a>
                            
                            @if($payslip->status == \App\Models\Payment::STATUS_APPROVED)
                            <a href="{{ route('projects.payslips.show', [$project, $payslip]) }}?print=1" target="_blank" class="text-gray-600 hover:text-gray-900" title="Print Slip Gaji">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </a>
                            @endif
                            
                            @if($isProjectOwner && $payslip->status == \App\Models\Payment::STATUS_DRAFT)
                            <form method="POST" action="{{ route('projects.payslips.destroy', [$project, $payslip]) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus draft slip gaji \'{{ e($payslip->payment_name) }}\'? Task terkait (jika ada) akan dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus Draft">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                        Tidak ada data slip gaji yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>