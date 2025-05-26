<x-app-layout>
    {{-- AlpineJS Data for Approval --}}
     <div x-data="payslipApproval({
         payslipId: {{ $payslip->id }},
         approveUrl: '{{ route('projects.payslips.approve', [$project, $payslip]) }}',
         csrfToken: '{{ csrf_token() }}'
     })"
         class="py-6 px-4 sm:px-6 lg:px-8">

         {{-- Header & Back Button --}}
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Detail Slip Gaji - {{ $payslip->payment_name }}</h2>
             <div class="flex space-x-2">
                 @if($payslip->isApproved())
                    {{-- Tombol Print/Export jika sudah diapprove --}}
                     <button @click="printPayslip()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /> </svg>
                         Print
                     </button>
                    {{-- Tambahkan tombol Export PDF jika diperlukan --}}
                 @endif
                 {{-- Tombol Kembali --}}
                <a href="{{ $payslip->isApproved() ? route('projects.payslips.history', $project) : route('projects.payslips.history', $project) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"> <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /> </svg>
                    Kembali ke {{ $payslip->isApproved() ? 'Riwayat' : 'Draft' }}
                </a>
             </div>
        </div>

        {{-- Alert Message (untuk sukses approve) --}}
         @if(session('success'))
             <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                 <span class="block sm:inline">{{ session('success') }}</span>
             </div>
         @endif
         <template x-if="formErrors.general">
             <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                  <span class="block sm:inline" x-text="formErrors.general[0]"></span>
              </div>
          </template>

         {{-- Slip Gaji Container (Area Cetak) --}}
         <div id="payslip-content" class="bg-white shadow-lg overflow-hidden sm:rounded-lg border border-gray-200 max-w-4xl mx-auto">
            {{-- 1. Header Slip Gaji --}}
            <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $projectName }}</h3>
                        <p class="text-sm text-gray-500">Slip Gaji Karyawan</p>
                    </div>
                    <div class="text-right">
                         {{-- Logo Perusahaan Jika Ada --}}
                         {{-- <img src="/path/to/logo.png" alt="Logo" class="h-10"> --}}
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
            <div class="px-6 py-4 border-b border-gray-200">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                     <div>
                         <p class="text-gray-500 font-medium">Nama Karyawan:</p>
                         <p class="text-gray-800">{{ $payslip->user->name ?? 'N/A' }}</p>
                     </div>
                     <div>
                         <p class="text-gray-500 font-medium">Jabatan/Posisi:</p>
                         {{-- Ambil dari project_users jika ada --}}
                         <p class="text-gray-800">{{ $payslip->user->projects()->where('project_id', $project->id)->first()?->pivot?->position ?: '-' }}</p>
                     </div>
                      <div>
                         <p class="text-gray-500 font-medium">Nama Pembayaran:</p>
                         <p class="text-gray-800">{{ $payslip->payment_name }} (Tipe: {{ ucfirst($payslip->payment_type) }})</p>
                     </div>
                     <div>
                         <p class="text-gray-500 font-medium">Rekening Bank:</p>
                         <p class="text-gray-800">{{ $payslip->bank_account ?? 'N/A' }}</p>
                     </div>
                 </div>
            </div>

            {{-- 3. Rincian Pendapatan (Task/Lainnya) --}}
            <div class="px-6 py-4">
                 <h4 class="text-md font-semibold text-gray-700 mb-3">Rincian Pendapatan</h4>
                 @if(in_array($payslip->payment_type, ['task', 'termin']) && $payslip->tasks->count() > 0)
                    {{-- Tabel Rincian Task --}}
                    <div class="overflow-x-auto border rounded-md mb-4">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Task</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Diff</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Prio</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 uppercase">Achv(%)</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 uppercase">WSM</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Nilai Task (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($payslip->tasks as $task)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-800">{{ $task->title }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $task->difficultyLevel->name ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $task->priorityLevel->name ?? '-' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500 text-center">{{ $task->achievement_percentage }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-600 text-center">{{ number_format($task->wsm_score, 2) }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 text-right font-semibold">{{ number_format($task->calculated_value, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                {{-- Baris Total Task --}}
                                <tr>
                                    <td colspan="5" class="px-3 py-2 text-right font-bold text-gray-800 uppercase">Total Pendapatan Task</td>
                                    <td class="px-3 py-2 text-right font-bold text-gray-800 bg-gray-100">{{ number_format($payslip->tasks->sum('calculated_value'), 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                 @elseif(in_array($payslip->payment_type, ['full', 'other']))
                      {{-- Rincian untuk Full/Other --}}
                      <div class="grid grid-cols-2 gap-4 text-sm border-b pb-2 mb-2">
                           <div class="text-gray-800 font-medium">{{ $payslip->payment_name }}</div>
                           <div class="text-gray-700 text-right font-semibold">Rp {{ number_format($payslip->amount, 0, ',', '.') }}</div>
                      </div>
                 @else
                     <p class="text-sm text-gray-500 italic">Tidak ada rincian task untuk tipe slip ini.</p>
                 @endif

                 {{-- Catatan --}}
                 @if($payslip->notes)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600">Catatan:</p>
                        <p class="text-sm text-gray-500 whitespace-pre-line">{{ $payslip->notes }}</p>
                    </div>
                 @endif
            </div>

                          {{-- 4. Total & Tanda Tangan --}}
                          <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
    {{-- Total Penerimaan --}}
    <div class="text-right mb-8">
        <p class="text-sm font-medium text-gray-500 uppercase">Total Diterima</p>
        <p class="text-2xl font-bold text-indigo-700">Rp {{ number_format($payslip->amount, 0, ',', '.') }}</p>
    </div>

    {{-- Area Tanda Tangan --}}
    <div class="grid grid-cols-1 pt-6 border-t border-dashed border-gray-300">
        <div class="text-center md:text-right">
            <p class="text-sm text-gray-600 mb-2">Disetujui Oleh,</p>
            <div class="h-16 w-full flex items-center justify-center md:justify-end">
                @if($payslip->isApproved())
                    @if($payslip->signature_type == 'digital' && $payslip->signature_url)
                        {{-- Fixed URL handling to prevent double storage/ paths --}}
                        @php
                            $signatureUrl = $payslip->signature_url;
                            // Remove any absolute URLs
                            $signatureUrl = preg_replace('#^https?://[^/]+#', '', $signatureUrl);
                            // Remove any storage/ prefix as asset('storage/') will add it
                            $signatureUrl = str_replace('storage/', '', $signatureUrl);
                            // Make sure we don't have double slashes
                            $signatureUrl = ltrim($signatureUrl, '/');
                        @endphp
                        <img src="{{ asset('storage/'.$signatureUrl) }}" alt="Tanda Tangan Digital" class="max-h-16 object-contain">
                    @elseif($payslip->signature_type == 'scanned' && $payslip->signature_url)
                        {{-- Same fix for scanned signature --}}
                        @php
                            $signatureUrl = $payslip->signature_url;
                            $signatureUrl = preg_replace('#^https?://[^/]+#', '', $signatureUrl);
                            $signatureUrl = str_replace('storage/', '', $signatureUrl);
                            $signatureUrl = ltrim($signatureUrl, '/');
                        @endphp
                        <a href="{{ asset('storage/'.$signatureUrl) }}" target="_blank" class="text-blue-600 hover:underline text-sm">Lihat Tanda Tangan (Scan)</a>
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

         {{-- Approval Form (Hanya tampil jika draft & user adalah PM) --}}
          @if(!$payslip->isApproved() && $project->owner_id === Auth::id())
              <div class="mt-8 max-w-4xl mx-auto bg-indigo-50 shadow sm:rounded-lg border border-indigo-200">
                   <form @submit.prevent="submitApproval" x-ref="approvalForm" enctype="multipart/form-data">
                         <div class="px-4 py-5 sm:p-6">
                              <h3 class="text-lg font-medium text-indigo-900 mb-3">Persetujuan Slip Gaji</h3>
                              <p class="text-sm text-indigo-700 mb-4">Upload tanda tangan Anda untuk menyetujui slip gaji ini.</p>

                              {{-- Pilihan Tipe Signature --}}
                              <div class="mb-4">
                                  <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Tanda Tangan</label>
                                  <select name="signature_type" x-model="signatureType" required class="mt-1 block w-full sm:w-auto py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                       <option value="digital">Digital (Upload Gambar TTD)</option>
                                       <option value="scanned">Scan (Upload PDF/Gambar Slip BertTD)</option>
                                  </select>
                                   <template x-if="formErrors.signature_type"><p class="text-xs text-red-500 mt-1" x-text="formErrors.signature_type[0]"></p></template>
                              </div>

                              {{-- File Upload --}}
                              <div class="mb-4">
                                   <label for="signature_file" class="block text-sm font-medium text-gray-700 mb-1">
                                       Upload File <span x-show="signatureType === 'digital'">(PNG/JPG, max 1MB)</span><span x-show="signatureType === 'scanned'">(PDF/PNG/JPG, max 2MB)</span>
                                   </label>
                                   <input type="file" name="signature_file" id="signature_file" required @change="handleFileSelect($event)"
                                          :accept="signatureType === 'digital' ? '.png,.jpg,.jpeg' : '.pdf,.png,.jpg,.jpeg'"
                                          class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200"/>
                                    <template x-if="formErrors.signature_file"><p class="text-xs text-red-500 mt-1" x-text="formErrors.signature_file[0]"></p></template>
                                    {{-- Preview --}}
                                    <div x-show="previewUrl" class="mt-2">
                                        <template x-if="signatureType === 'digital'">
                                            <img :src="previewUrl" alt="Preview TTD" class="max-h-20 border rounded">
                                        </template>
                                         <template x-if="signatureType === 'scanned'">
                                             <a :href="previewUrl" target="_blank" class="text-sm text-indigo-600 hover:underline">Lihat Pratinjau File</a>
                                        </template>
                                    </div>
                                    {{-- Progress --}}
                                    <div x-show="uploadProgress > 0 && uploadProgress < 100" class="mt-2">
                                        <progress class="w-full" :value="uploadProgress" max="100"></progress>
                                        <span class="text-xs text-indigo-700" x-text="`Uploading: ${uploadProgress}%`"></span>
                                    </div>
                              </div>
                         </div>
                         <div class="px-4 py-3 bg-indigo-100 text-right sm:px-6 border-t border-indigo-200">
                             <button type="submit" :disabled="isSubmitting || !selectedFile"
                                     class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                 <span x-show="!isSubmitting">Setujui Slip Gaji</span>
                                 <span x-show="isSubmitting">Menyetujui...</span>
                             </button>
                         </div>
                   </form>
              </div>
          @endif

     </div>

     @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script>
             function payslipApproval(config) {
                 return {
                     payslipId: config.payslipId,
                     approveUrl: config.approveUrl,
                     csrfToken: config.csrfToken,
                     signatureType: 'digital', // default
                     selectedFile: null,
                     previewUrl: null,
                     isSubmitting: false,
                     formErrors: {},
                     uploadProgress: 0,

                     handleFileSelect(event) {
                         this.formErrors = {}; // Clear previous errors
                         this.selectedFile = event.target.files[0];
                         if (this.selectedFile) {
                             // Simple Validation (Client-side)
                             const maxSize = this.signatureType === 'digital' ? 1024 * 1024 : 2 * 1024 * 1024; // 1MB or 2MB
                             const allowedTypes = this.signatureType === 'digital'
                                 ? ['image/png', 'image/jpeg', 'image/jpg']
                                 : ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg'];

                             if (this.selectedFile.size > maxSize) {
                                 this.formErrors = { signature_file: [`Ukuran file maksimal ${maxSize / (1024 * 1024)}MB.`] };
                                 this.selectedFile = null;
                                 this.previewUrl = null;
                                 event.target.value = ''; // Reset file input
                                 return;
                             }
                             if (!allowedTypes.includes(this.selectedFile.type)) {
                                  this.formErrors = { signature_file: ['Tipe file tidak diizinkan.'] };
                                  this.selectedFile = null;
                                  this.previewUrl = null;
                                  event.target.value = '';
                                  return;
                             }

                             // Generate preview
                             const reader = new FileReader();
                             reader.onload = (e) => { this.previewUrl = e.target.result; }
                             reader.readAsDataURL(this.selectedFile);
                         } else {
                             this.previewUrl = null;
                         }
                     },

                     submitApproval() {
                        if (!this.selectedFile || this.isSubmitting) return;
                        this.isSubmitting = true;
                        this.formErrors = {};
                        this.uploadProgress = 0;

                        const formData = new FormData();
                        formData.append('_method', 'PATCH'); // Method spoofing
                        formData.append('signature_type', this.signatureType);
                        formData.append('signature_file', this.selectedFile);
                        formData.append('_token', this.csrfToken);

                        // Use XMLHttpRequest for progress
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', this.approveUrl, true); // Always POST for FormData with _method
                        xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
                        xhr.setRequestHeader('Accept', 'application/json');

                        xhr.upload.onprogress = (event) => {
                           if (event.lengthComputable) {
                               this.uploadProgress = Math.round((event.loaded / event.total) * 100);
                           }
                       };

                       xhr.onload = () => {
                            this.isSubmitting = false;
                            this.uploadProgress = 0;

                            if (xhr.status >= 200 && xhr.status < 300) {
                                // Sukses
                                window.location.reload(); // Reload halaman untuk lihat hasil approve
                            } else {
                                // Error
                                console.error('Approval failed:', xhr.status, xhr.responseText);
                                try {
                                    const errorData = JSON.parse(xhr.responseText);
                                    if (xhr.status === 422 && errorData.errors) {
                                        this.formErrors = errorData.errors;
                                    } else {
                                         this.formErrors = { general: [errorData.message || `Gagal menyetujui (${xhr.status})`] };
                                    }
                                } catch (e) {
                                     this.formErrors = { general: [`Gagal menyetujui (${xhr.status})`] };
                                }
                            }
                       };

                       xhr.onerror = () => {
                           this.isSubmitting = false;
                           this.uploadProgress = 0;
                           this.formErrors = { general: ['Terjadi kesalahan jaringan.'] };
                       };

                       xhr.send(formData);
                    },

                    printPayslip() {
                        const printContent = document.getElementById('payslip-content');
                        const originalContents = document.body.innerHTML;
                        const printElementHtml = printContent.innerHTML;

                        document.body.innerHTML = `
                             <html>
                             <head>
                                <title>Slip Gaji - ${document.title}</title>
                                <link href="{{ asset('css/app.css') }}" rel="stylesheet"> {{-- Load Tailwind CSS --}}
                                <style>
                                    body { margin: 0; padding: 0; font-family: sans-serif; -webkit-print-color-adjust: exact !important; color-adjust: exact !important;}
                                    @page { size: A4; margin: 15mm; } /* A4 size with margin */
                                    #payslip-content { width: 100%; border: none; box-shadow: none; margin: 0; padding: 0;}
                                     /* Tambahkan style print spesifik jika perlu */
                                     #payslip-content table { font-size: 9pt !important; }
                                     #payslip-content th, #payslip-content td { padding: 4px 6px !important; border: 1px solid #ddd !important;}
                                     #payslip-content .grid { display: grid !important; } /* Ensure grid works */
                                </style>
                             </head>
                             <body>${printElementHtml}</body>
                             </html>`;

                         window.print();

                         // Restore original content after print dialog is closed/cancelled
                         document.body.innerHTML = originalContents;
                         // Re-initialize Alpine if needed, though reload might be simpler
                         window.location.reload();
                     },
                 }
             }

             // Initialize Alpine component specifically for print button if needed outside main data scope
             function payslipPrint() {
                  return {
                      printPayslip() {
                          const printContent = document.getElementById('payslip-content');
                          if(!printContent) return;

                          const options = {
                              margin:       [10, 5, 10, 5], // top, left, bottom, right in mm
                              filename:     'slip_gaji_{{ $payslip->payment_name }}_{{ $payslip->user->name }}.pdf',
                              image:        { type: 'jpeg', quality: 0.98 },
                              html2canvas:  { scale: 2, useCORS: true },
                              jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                          };

                          html2pdf().set(options).from(printContent).save();
                      }
                  }
             }
        </script>
     @endpush
    @stack('scripts')
</x-app-layout>