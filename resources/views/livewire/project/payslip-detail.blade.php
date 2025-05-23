<div> {{-- Root Element --}}
    {{-- Container padding (bisa dihapus jika sudah di layout utama) --}}
    <div class="py-6 px-4 sm:px-6 lg:px-8">

         {{-- Header & Back Button --}}
        <div class="mb-6 flex justify-between items-center no-print"> {{-- no-print agar tidak ikut tercetak --}}
            <h2 class="text-2xl font-semibold text-gray-900">Detail Slip Gaji - {{ $payslip->payment_name }}</h2>
             <div class="flex space-x-2">
                 @if($payslip->isApproved())
                    {{-- Tombol Print (Gunakan Alpine atau JS biasa) --}}
                     <div x-data="payslipPrintUtil()"> {{-- Init Alpine component --}}
                         <button @click="printPayslip('{{ $payslip->payment_name }}', '{{ $payslip->user->name }}')" class="btn-secondary">
                             <svg class="print-icon h-4 w-4 mr-2">...</svg> Print/PDF
                         </button>
                     </div>
                 @endif
                 {{-- Tombol Kembali (Gunakan wire:navigate) --}}
                 {{-- Arahkan ke history jika sudah diapprove, ke create jika masih draft --}}
                <a href="{{ $payslip->isApproved() ? route('projects.payslips.history', $project) : route('projects.payslips.create', $project) }}"
                   wire:navigate
                   class="btn-secondary">
                    <svg class="btn-icon-left">...</svg>
                    Kembali ke {{ $payslip->isApproved() ? 'Riwayat' : 'Draft' }}
                </a>
             </div>
        </div>

        {{-- Alert Message (Untuk sukses approve dari Livewire) --}}
         @if (session()->has('success_message'))
             <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                  class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative no-print" role="alert" x-transition>
                 <span class="block sm:inline">{{ session('success_message') }}</span>
                 <button @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                     <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                 </button>
             </div>
         @endif
         {{-- Tampilkan error general dari Livewire --}}
         @error('general')
              <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative no-print" role="alert">
                  <span class="block sm:inline">{{ $message }}</span>
              </div>
          @enderror


         {{-- Slip Gaji Container (Area Cetak) --}}
         <div id="payslip-content" class="bg-white shadow-lg overflow-hidden sm:rounded-lg border border-gray-200 max-w-4xl mx-auto print:shadow-none print:border-none print:max-w-full print:rounded-none">
            {{-- 1. Header Slip Gaji --}}
            <div class="px-6 py-5 bg-gray-50 border-b border-gray-200 print:bg-white print:border-gray-400">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $projectName }}</h3>
                        <p class="text-sm text-gray-500">Slip Gaji Karyawan</p>
                    </div>
                    <div class="text-right">
                         <p class="text-xs text-gray-500">ID Slip: #{{ $payslip->id }}</p>
                         <p class="text-xs text-gray-500">
                            Status:
                             <span class="font-semibold {{ $payslip->isApproved() ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ ucfirst($payslip->status) }}
                             </span>
                         </p>
                          @if($payslip->isApproved())
                           <p class="text-xs text-gray-500">Tgl Disetujui: {{ $payslip->approved_at->format('d M Y') }}</p>
                          @else
                           <p class="text-xs text-gray-500">Tgl Dibuat: {{ $payslip->created_at->format('d M Y') }}</p>
                          @endif
                    </div>
                </div>
            </div>

            {{-- 2. Informasi Karyawan & Periode --}}
            <div class="px-6 py-4 border-b border-gray-200 print:border-gray-400">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                     <div> <p class="text-gray-500 font-medium">Nama Karyawan:</p> <p class="text-gray-800">{{ $payslip->user->name ?? 'N/A' }}</p> </div>
                     <div> <p class="text-gray-500 font-medium">Jabatan/Posisi:</p> <p class="text-gray-800">{{ $payslip->user->projects()->where('project_id', $project->id)->first()?->pivot?->position ?: '-' }}</p> </div>
                     <div> <p class="text-gray-500 font-medium">Nama Pembayaran:</p> <p class="text-gray-800">{{ $payslip->payment_name }} (Tipe: {{ ucfirst($payslip->payment_type) }})</p> </div>
                     {{-- Tampilkan Info Termin jika ada --}}
                     @if($payslip->paymentTerm)
                     <div> <p class="text-gray-500 font-medium">Periode Termin:</p> <p class="text-gray-800">{{ $payslip->paymentTerm->name }} ({{ $payslip->paymentTerm->start_date->format('d M') }} - {{ $payslip->paymentTerm->end_date->format('d M') }})</p> </div>
                     @endif
                     <div> <p class="text-gray-500 font-medium">Rekening Bank:</p> <p class="text-gray-800">{{ $payslip->bank_account ?? 'N/A' }}</p> </div>
                 </div>
            </div>

            {{-- 3. Rincian Pendapatan (Task/Lainnya) --}}
            <div class="px-6 py-4">
                 <h4 class="text-md font-semibold text-gray-700 mb-3">Rincian Pendapatan</h4>
                 @if(in_array($payslip->payment_type, ['task', 'termin']) && $payslip->tasks->count() > 0)
                    {{-- Tabel Rincian Task --}}
                    <div class="overflow-x-auto border rounded-md mb-4 print:border-gray-400">
                        <table class="min-w-full divide-y divide-gray-200 text-xs print:divide-gray-400">
                            <thead class="bg-gray-50 print:bg-gray-100"> <tr> <th class="th-cell">Task</th> <th class="th-cell">Diff</th> <th class="th-cell">Prio</th> <th class="th-cell text-center">Achv(%)</th> <th class="th-cell text-center">WSM</th> <th class="th-cell text-right">Nilai Task (Rp)</th> </tr> </thead>
                            <tbody class="bg-white divide-y divide-gray-200 print:divide-gray-400">
                                @foreach($payslip->tasks as $task)
                                <tr> <td class="td-cell font-medium text-gray-800">{{ $task->title }}</td> <td class="td-cell text-gray-500">{{ $task->difficultyLevel->name ?? '-' }}</td> <td class="td-cell text-gray-500">{{ $task->priorityLevel->name ?? '-' }}</td> <td class="td-cell text-center text-gray-500">{{ $task->achievement_percentage }}</td> <td class="td-cell text-center text-gray-600">{{ number_format($task->wsm_score, 2) }}</td> <td class="td-cell text-right font-semibold text-gray-700">{{ number_format($task->calculated_value, 0, ',', '.') }}</td> </tr>
                                @endforeach
                                <tr> <td colspan="5" class="px-3 py-2 text-right font-bold text-gray-800 uppercase">Total Pendapatan Task</td> <td class="px-3 py-2 text-right font-bold text-gray-800 bg-gray-100 print:bg-gray-200">{{ number_format($payslip->tasks->sum('calculated_value'), 0, ',', '.') }}</td> </tr>
                            </tbody>
                        </table>
                    </div>
                 @elseif(in_array($payslip->payment_type, ['full', 'other']))
                      <div class="grid grid-cols-2 gap-4 text-sm border-b pb-2 mb-2 print:border-gray-400">
                           <div class="text-gray-800 font-medium">{{ $payslip->payment_name }}</div>
                           <div class="text-gray-700 text-right font-semibold">Rp {{ number_format($payslip->amount, 0, ',', '.') }}</div>
                      </div>
                 @else
                     <p class="text-sm text-gray-500 italic">Tidak ada rincian task.</p>
                 @endif

                 {{-- Catatan --}}
                 @if($payslip->notes)
                    <div class="mt-4"> <p class="text-sm font-medium text-gray-600">Catatan:</p> <p class="text-sm text-gray-500 whitespace-pre-line">{{ $payslip->notes }}</p> </div>
                 @endif
            </div>

            {{-- 4. Total & Tanda Tangan --}}
            <div class="px-6 py-5 bg-gray-50 border-t border-gray-200 print:bg-white print:border-gray-400">
                 {{-- Total Penerimaan --}}
                 <div class="text-right mb-8"> <p class="text-sm font-medium text-gray-500 uppercase">Total Diterima</p> <p class="text-2xl font-bold text-indigo-700">Rp {{ number_format($payslip->amount, 0, ',', '.') }}</p> </div>
                  {{-- Area Tanda Tangan (Hanya PM) --}}
                  <div class="grid grid-cols-1 pt-6 border-t border-dashed border-gray-300 print:border-gray-400">
                       <div class="text-center md:text-right"> {{-- Atau text-center jika mau di tengah --}}
                            <p class="text-sm text-gray-600 mb-2">Disetujui Oleh,</p>
                            <div class="h-16 w-full flex items-center justify-center md:justify-end mb-1">
                                @if($payslip->isApproved())
                                     @if($payslip->signature_type == 'digital' && $payslip->signature_url)
                                         <img src="{{ $payslip->signature_url }}" alt="TTD Digital" class="max-h-16 object-contain print:max-h-12">
                                     @elseif($payslip->signature_type == 'scanned' && $payslip->signature_url)
                                          <a href="{{ $payslip->signature_url }}" target="_blank" class="text-blue-600 hover:underline text-sm no-print">Lihat Tanda Tangan (Scan)</a>
                                           <span class="text-gray-500 text-sm italic print:block hidden">(Tanda Tangan Scan Terlampir)</span>
                                     @else
                                         <span class="text-gray-400 italic text-sm">(Tanda Tangan Digital/Scan)</span>
                                     @endif
                                @else
                                     <span class="text-gray-400 italic text-sm">(Belum Disetujui)</span>
                                @endif
                            </div>
                            <p class="text-sm font-medium text-gray-800 mt-2 border-t border-gray-400 pt-1 inline-block">({{ $payslip->approver->name ?? $ownerName }})</p>
                            <p class="text-xs text-gray-500">Project Manager</p>
                       </div>
                  </div>
             </div>
         </div> {{-- End Slip Gaji Container --}}

         {{-- Approval Form (Hanya tampil jika draft & user adalah PM) --}}
          @if(!$payslip->isApproved() && $project->owner_id === Auth::id())
              <div class="mt-8 max-w-4xl mx-auto bg-indigo-50 shadow sm:rounded-lg border border-indigo-200 no-print">
                  {{-- Form menargetkan method Livewire --}}
                   <form wire:submit.prevent="approvePayslip">
                         <div class="px-4 py-5 sm:p-6">
                              <h3 class="text-lg font-medium text-indigo-900 mb-3">Persetujuan Slip Gaji</h3>
                              <p class="text-sm text-indigo-700 mb-4">Upload tanda tangan Anda untuk menyetujui.</p>

                              {{-- Pilihan Tipe Signature --}}
                              <div class="mb-4">
                                  <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Tanda Tangan</label>
                                  <select wire:model="signatureType" required class="input-field w-full sm:w-auto @error('signatureType') input-error @enderror">
                                       <option value="digital">Digital (Upload Gambar TTD)</option>
                                       <option value="scanned">Scan (Upload PDF/Gambar Slip BertTD)</option>
                                  </select>
                                  @error('signatureType')<p class="input-error-msg">{{ $message }}</p>@enderror
                              </div>

                              {{-- File Upload --}}
                              <div class="mb-4">
                                   <label for="signature_file" class="block text-sm font-medium text-gray-700 mb-1">
                                       Upload File
                                       <span x-show="$wire.signatureType === 'digital'">(PNG/JPG, max 1MB)</span>
                                       <span x-show="$wire.signatureType === 'scanned'">(PDF/PNG/JPG, max 2MB)</span>
                                   </label>
                                   {{-- wire:model untuk file upload --}}
                                   <input type="file" id="signature_file" required wire:model="signatureFile"
                                          accept="{{ $signatureType === 'digital' ? '.png,.jpg,.jpeg' : '.pdf,.png,.jpg,.jpeg' }}"
                                          class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 @error('signatureFile') border-red-500 ring-red-500 @enderror"/>
                                   {{-- Tampilkan error validasi Livewire --}}
                                    @error('signatureFile')<p class="input-error-msg">{{ $message }}</p>@enderror
                                    {{-- Loading indicator untuk upload --}}
                                    <div wire:loading wire:target="signatureFile" class="mt-2 text-sm text-indigo-600">Uploading file...</div>
                                    {{-- Preview sederhana untuk gambar --}}
                                    @if ($signatureFile && str_starts_with($signatureFile->getMimeType(), 'image/'))
                                        <div class="mt-2">
                                            <img src="{{ $signatureFile->temporaryUrl() }}" alt="Preview" class="max-h-20 border rounded">
                                        </div>
                                    @elseif($signatureFile)
                                        <div class="mt-2 text-sm text-gray-600">
                                            File: {{ $signatureFile->getClientOriginalName() }}
                                        </div>
                                    @endif
                                    {{-- Tampilkan error custom dari backend jika ada --}}
                                    @if (!empty($formErrors['signatureFile']))
                                        <p class="input-error-msg">{{ $formErrors['signatureFile'][0] }}</p>
                                    @endif
                              </div>
                         </div>
                         <div class="px-4 py-3 bg-indigo-100 text-right sm:px-6 border-t border-indigo-200">
                             {{-- Tombol submit Livewire --}}
                             <button type="submit" wire:loading.attr="disabled" wire:target="approvePayslip"
                                     class="btn-primary">
                                 <span wire:loading.remove wire:target="approvePayslip">Setujui Slip Gaji</span>
                                 <span wire:loading wire:target="approvePayslip">Menyetujui...</span>
                             </button>
                         </div>
                   </form>
              </div>
          @endif

    </div> {{-- End Padding Utama --}}

    {{-- Javascript untuk Print/PDF (Tetap pakai Alpine/JS biasa) --}}
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script>
             function payslipPrintUtil() {
                  return {
                      printPayslip(paymentName, userName) {
                          const printContent = document.getElementById('payslip-content');
                           if(!printContent) return;
                           // Bersihkan nama file dari karakter tidak valid
                           const safePaymentName = paymentName.replace(/[^a-zA-Z0-9\s-_]/g, '').replace(/\s+/g, '-');
                           const safeUserName = userName.replace(/[^a-zA-Z0-9\s-_]/g, '').replace(/\s+/g, '-');
                           const filename = `SlipGaji-${safePaymentName}-${safeUserName}.pdf`;

                           const opt = {
                              margin:       [10, 8, 10, 8], // top, left, bottom, right (mm)
                              filename:     filename,
                              image:        { type: 'jpeg', quality: 0.98 },
                              html2canvas:  { scale: 2, useCORS: true, logging: false, scrollY: 0 }, // Penting: scrollY 0
                              jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                          };
                           // Pastikan elemen di-clone agar style asli tidak berubah
                           const elementToPrint = printContent.cloneNode(true);
                           // Tambahkan style print inline jika perlu (meskipun @media print lebih baik)
                           // elementToPrint.classList.add('print-styling'); // Contoh class
                           html2pdf().set(opt).from(elementToPrint).save();
                      }
                  }
             }
              // Inisialisasi Alpine jika belum ada di layout utama global
              // document.addEventListener('alpine:init', () => {
              //    Alpine.data('payslipPrintUtil', payslipPrintUtil);
              // });
        </script>
         {{-- Tambahkan CSS utility jika belum ada global --}}
         <style>
            .input-field { @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm; }
            .input-error { @apply border-red-500 ring-red-500; }
            .input-error-msg { @apply mt-1 text-xs text-red-600; }
            .label-text { @apply block text-sm font-medium text-gray-700; }
            .radio-input { @apply focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300; }
            .btn-primary { @apply inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50; }
            .btn-secondary { @apply inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
            .btn-icon-left { @apply -ml-0.5 mr-1.5 h-4 w-4; }
            .th-cell { @apply px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider; }
            .td-cell { @apply px-3 py-2 whitespace-nowrap; }
             /* Style Print */
             @media print {
                 .no-print { display: none !important; }
                 body { -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
                 #payslip-content table { font-size: 9pt !important; }
                 #payslip-content th, #payslip-content td { padding: 4px 6px !important; border: 1px solid #ccc !important;}
                 /* ... style print lainnya ... */
             }
        </style>
     @endpush
</div>