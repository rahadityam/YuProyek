<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use Livewire\WithFileUploads; // <-- Tambahkan untuk upload file
use App\Models\Project;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PayslipDetail extends Component
{
    use WithFileUploads; // Aktifkan trait upload file

    public Project $project;
    public Payment $payslip;

    // Properti untuk form approval
    public $signatureType = 'digital'; // Default tipe signature
    public $signatureFile; // Untuk binding file upload Livewire
    public $formErrors = []; // Untuk menampilkan error validasi custom

    public $isApproving = false; // State untuk tombol approval

    // Aturan validasi untuk form approval
    protected function rules()
    {
        return [
            'signatureType' => ['required', Rule::in([Payment::SIGNATURE_DIGITAL, Payment::SIGNATURE_SCANNED])],
            'signatureFile' => [
                'required',
                'file',
                // Validasi MIME berdasarkan tipe signature yang dipilih
                Rule::when($this->signatureType == Payment::SIGNATURE_DIGITAL, ['mimes:png,jpg,jpeg', 'max:1024']), // Max 1MB
                Rule::when($this->signatureType == Payment::SIGNATURE_SCANNED, ['mimes:pdf,png,jpg,jpeg', 'max:2048']), // Max 2MB
            ],
        ];
    }

    // Pesan validasi kustom
    protected $validationAttributes = [
        'signatureType' => 'Tipe Tanda Tangan',
        'signatureFile' => 'File Tanda Tangan',
    ];

    // Method mount untuk inisialisasi dan otorisasi awal
    public function mount(Project $project, Payment $payslip)
    {
        // Pastikan payslip milik project ini
        if ($payslip->project_id !== $project->id) {
            abort(404);
        }
        // Pastikan user boleh melihat (owner atau penerima)
        if ($project->owner_id !== Auth::id() && $payslip->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->project = $project;
        // Eager load relasi yang dibutuhkan view
        $this->payslip = $payslip->load([
            'user:id,name', // Hanya ambil id dan name
            'approver:id,name',
            'paymentTerm:id,name,start_date,end_date', // Ambil detail termin
            'tasks' => function ($query) {
                $query->with(['difficultyLevel:id,name', 'priorityLevel:id,name']) // Eager load task details
                      ->select(['id', 'payment_id', 'title', 'difficulty_level_id', 'priority_level_id', 'achievement_percentage', 'updated_at']); // Pilih kolom task
            }
        ]);
    }

    // Method untuk menghandle perubahan file upload
    public function updatedSignatureFile()
    {
        // Validasi file secara realtime saat diupload
        $this->validateOnly('signatureFile');
        // Reset error spesifik jika validasi berhasil
         $this->resetErrorBag('signatureFile');
         $this->formErrors = []; // Reset error custom juga
    }

    // Method untuk memproses approval
    public function approvePayslip()
    {
        // Otorisasi lagi (jaga-jaga)
        if ($this->project->owner_id !== Auth::id()) {
            $this->addError('general', 'Unauthorized action.');
            return;
        }
        if ($this->payslip->status !== Payment::STATUS_DRAFT) {
             $this->addError('general', 'Slip Gaji tidak valid atau sudah disetujui.');
             return;
        }

        // Validasi input form approval
        $this->validate(); // Gunakan rules() yang sudah didefinisikan

        $this->isApproving = true; // Tampilkan state loading

        DB::beginTransaction();
        try {
            // Simpan file signature menggunakan Livewire
            // Format: payslip_signatures/{projectId}/{fileName}
            $fileName = 'signature_' . $this->payslip->id . '_' . time() . '.' . $this->signatureFile->getClientOriginalExtension();
            $path = $this->signatureFile->storeAs("payslip_signatures/{$this->project->id}", $fileName, 'public');

            if (!$path) {
                 throw new \Exception('Gagal menyimpan file tanda tangan.');
            }

             // Hapus signature lama jika ada (meskipun seharusnya tidak ada untuk draft)
             if ($this->payslip->signature_path && Storage::disk('public')->exists($this->payslip->signature_path)) {
                 Storage::disk('public')->delete($this->payslip->signature_path);
             }

            // Update data slip gaji
            $this->payslip->update([
                'status' => Payment::STATUS_APPROVED,
                'signature_type' => $this->signatureType,
                'signature_path' => $path,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            DB::commit();

            // Refresh data payslip di komponen setelah update
            $this->payslip->refresh();
             $this->reset(['signatureFile', 'signatureType']); // Reset form approval
             $this->formErrors = []; // Bersihkan error

            // Kirim pesan sukses (bisa pakai flash session atau event browser)
            session()->flash('success_message', 'Slip gaji berhasil disetujui.');
            // Atau emit event jika ingin ditangkap Javascript Alpine
            // $this->dispatchBrowserEvent('payslip-approved', ['message' => 'Slip gaji berhasil disetujui.']);

        } catch (\Exception $e) {
            DB::rollBack();
             // Hapus file yang mungkin terupload jika error
             if (isset($path) && Storage::disk('public')->exists($path)) {
                 Storage::disk('public')->delete($path);
             }
            Log::error('Payslip Approval Failed (Livewire) for Payment ' . $this->payslip->id . ': ' . $e->getMessage());
             // Tampilkan pesan error general
             $this->addError('general', 'Gagal menyetujui slip gaji: ' . $e->getMessage());
             // Jika error validasi dari Livewire, tidak perlu addError lagi karena sudah otomatis
        } finally {
            $this->isApproving = false; // Selesai loading
        }
    }


    public function render()
    {
        // Mendapatkan nama proyek dan owner untuk view
        $projectName = $this->project->name;
        $ownerName = $this->project->owner->name;

        return view('livewire.project.payslip-detail', compact('projectName', 'ownerName'));
            // ->layout('layouts.app'); // Layout otomatis
    }
}