<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\Payment;
use App\Models\PaymentTerm;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PayslipCreateDraft extends Component
{
    use WithPagination;

    public Project $project;
    public $workers;
    public $paymentCalculationType;
    public $paymentTerms; // Collection of available payment terms
    public $unpaidTasksGrouped; // All unpaid tasks grouped by user ID
    public $defaultTerminName = '';

    // Properti untuk form binding (mirip state Alpine sebelumnya)
    public $selectedWorkerId = '';
    public $payslipType = ''; // Akan diinisialisasi di mount
    public $selectedTermId = '';
    public $paymentName = '';
    public $notes = '';
    public $calculatedAmount = 0; // State untuk nominal (terutama yg dihitung otomatis)
    public $manualAmount = ''; // State untuk nominal manual (tipe full/other)
    public $selectedTaskIds = []; // Array of task IDs (string)

    // Properti untuk list draft & sorting/pagination
    public $sortFieldDraft = 'created_at';
    public $sortDirectionDraft = 'desc';
    // public $draftPage = 1; // Pagination dikelola WithPagination

    // Untuk merestore old input jika ada validation error dari Controller
    public $oldInput = [];

    protected $paginationTheme = 'tailwind';

    // Listener untuk me-refresh draft list setelah save/delete (jika pakai event)
    protected $listeners = ['draftListUpdated' => '$refresh'];

    // Aturan validasi Livewire jika ingin validasi realtime (opsional untuk form ini)
    // protected function rules() { ... }

    public function mount(Project $project, Request $request) // Terima Request jika perlu query string awal
    {
        $this->project = $project;
        $this->workers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get(['id', 'name']);
        $this->paymentCalculationType = $project->payment_calculation_type ?? 'task';
        $this->payslipType = $this->paymentCalculationType; // Set default type

        // Load data awal seperti di controller lama
        $this->loadInitialData();

        // Restore state dari old input jika ada (dari redirect back with errors)
        $this->oldInput = session()->getOldInput() ?? [];
        $this->selectedWorkerId = $this->oldInput['user_id'] ?? '';
        $this->payslipType = $this->oldInput['payment_type'] ?? $this->paymentCalculationType;
        $this->selectedTermId = $this->oldInput['payment_term_id'] ?? '';
        $this->paymentName = $this->oldInput['payment_name'] ?? '';
        $this->notes = $this->oldInput['notes'] ?? '';
        $this->selectedTaskIds = isset($this->oldInput['task_ids']) && is_array($this->oldInput['task_ids'])
                                ? array_map('strval', $this->oldInput['task_ids'])
                                : [];
        // Set initial amount dari old input HANYA jika tipe F/O
        if (isset($this->oldInput['amount']) && in_array($this->payslipType, ['full', 'other'])) {
            $this->manualAmount = $this->oldInput['amount'];
            $this->calculatedAmount = floatval($this->oldInput['amount']);
        } else {
             // Hitung amount awal jika tipe T/Tm dan ada old input tasks
             if (!empty($this->selectedTaskIds) && !empty($this->selectedWorkerId)) {
                 $this->calculateAmountFromTasks();
             }
        }
        // Set default name jika perlu
         $this->setDefaultPaymentName();

        // Ambil sorting state dari request awal jika ada
         $this->sortFieldDraft = $request->input('sort_draft', 'created_at');
         $this->sortDirectionDraft = $request->input('direction_draft', 'desc');
    }

    public function loadInitialData()
    {
        // Ambil Payment Terms jika tipe termin
        $this->paymentTerms = collect();
        if ($this->paymentCalculationType === 'termin') {
            $this->paymentTerms = $this->project->paymentTerms()
                                     ->select('id', 'name', 'start_date', 'end_date')
                                     ->orderBy('start_date')
                                     ->get()
                                     ->map(function ($term) {
                                         $term->start_date_formatted = $term->start_date ? $term->start_date->toDateString() : null;
                                         $term->end_date_formatted = $term->end_date ? $term->end_date->toDateString() : null;
                                         return $term;
                                     });
        }

        // Ambil SEMUA task yang belum dibayar
        $unpaidTasksQuery = Task::where('project_id', $this->project->id)
            ->where('status', 'Done')
            ->whereNull('payment_id')
            ->whereNotNull('updated_at')
            ->select('id', 'title', 'assigned_to', 'achievement_percentage', 'difficulty_level_id', 'priority_level_id', 'project_id', 'updated_at')
            ->with([
                'assignedUser:id,name', // Perlu untuk grouping
                'difficultyLevel:id,value',
                'priorityLevel:id,value',
                'projectUserMembership.wageStandard:id,task_price'
                // Project relasi tidak perlu eager load di sini, karena sudah ada $this->project
            ]);

        $unpaidTasks = $unpaidTasksQuery->get()
            ->map(function ($task) {
                 // Set relasi project agar accessor jalan
                 $task->setRelation('project', $this->project);
                 $task->wsm_score = $task->wsm_score;
                 $task->calculated_value = $task->calculated_value;
                 $task->finished_date = $task->updated_at ? Carbon::parse($task->updated_at)->toDateString() : null;
                 // Tidak unset updated_at agar bisa dipakai lagi jika perlu
                 return $task;
            });

        $this->unpaidTasksGrouped = $unpaidTasks->groupBy('assigned_to')->toArray(); // Kirim sebagai array

        // Hitung default termin name
        $this->setDefaultTerminName();
    }

    // Hitung nama default termin
    public function setDefaultTerminName()
    {
        if ($this->paymentCalculationType === 'termin') {
            $nextTerminNumber = 1;
            $lastTermin = Payment::where('project_id', $this->project->id) ->where('payment_type', 'termin') ->where('payment_name', 'like', 'Termin %') ->orderByRaw('CAST(SUBSTRING_INDEX(payment_name, " ", -1) AS UNSIGNED) DESC, created_at DESC') ->first();
            if ($lastTermin && preg_match('/Termin (\d+)/', $lastTermin->payment_name, $matches)) { $nextTerminNumber = intval($matches[1]) + 1; }
            $this->defaultTerminName = "Termin " . $nextTerminNumber;
            // Set paymentName jika belum diisi dan tipe termin
             if (empty($this->paymentName) && $this->payslipType === 'termin') {
                 $this->paymentName = $this->defaultTerminName;
             }
        } else {
             $this->defaultTerminName = '';
        }
    }

     // Set nama default saat tipe slip berubah
    public function setDefaultPaymentName() {
         if ($this->payslipType === 'termin') {
             $this->paymentName = $this->defaultTerminName ?: 'Termin 1'; // Fallback jika belum dihitung
         } else if ($this->payslipType === 'task') {
             $workerName = User::find($this->selectedWorkerId)?->name ?? 'Pekerja';
             $this->paymentName = `Pembayaran Task {$workerName} (`.now()->format('d/m/Y').`)`;
         } else if ($this->payslipType === 'full') {
             $this->paymentName = `Pembayaran Penuh (`.now()->format('d/m/Y').`)`;
         } else if ($this->payslipType === 'other') {
             $this->paymentName = ''; // Kosongkan untuk manual
         }
    }


    // Dipanggil saat worker atau tipe slip berubah
    public function updatedSelectedWorkerId() {
        $this->selectedTaskIds = [];
        $this->selectedTermId = ''; // Reset term juga
        $this->setDefaultPaymentName(); // Update nama default jika perlu
        $this->calculateAmountFromTasks(); // Hitung ulang amount
    }
    public function updatedPayslipType() {
        $this->selectedTaskIds = [];
        $this->selectedTermId = '';
        $this->manualAmount = ''; // Reset manual amount
        $this->calculatedAmount = 0; // Reset calculated amount
        $this->setDefaultPaymentName();
        $this->calculateAmountFromTasks(); // Akan otomatis jadi 0 jika tipe bukan T/Tm
    }
    // Dipanggil saat term berubah
     public function updatedSelectedTermId() {
        $this->selectedTaskIds = [];
        $this->calculateAmountFromTasks(); // Hitung ulang amount berdasarkan term baru
     }

    // Dipanggil saat task dipilih/dibatalkan
     public function updatedSelectedTaskIds() {
        $this->calculateAmountFromTasks();
     }

     // Dipanggil saat amount manual diubah (hanya untuk tipe full/other)
     public function updatedManualAmount() {
         if($this->payslipType === 'full' || $this->payslipType === 'other'){
              $this->calculatedAmount = floatval(preg_replace('/[^0-9.]/', '', $this->manualAmount)) ?: 0;
         }
     }

    // Method untuk menghitung total amount dari task terpilih
    public function calculateAmountFromTasks()
    {
        if (!in_array($this->payslipType, ['task', 'termin']) || empty($this->selectedWorkerId) || empty($this->selectedTaskIds)) {
            $this->calculatedAmount = 0;
            return;
        }

        $tasksForWorker = collect($this->unpaidTasksGrouped[$this->selectedWorkerId] ?? []);
        $selectedIdsSet = array_flip($this->selectedTaskIds); // Lebih cepat untuk cek existensi

        $total = 0;
        $tasksToSum = $tasksForWorker->filter(function ($task) use ($selectedIdsSet) {
            return isset($selectedIdsSet[(string)$task['id']]); // Cek ID task dalam set
        });

        // Filter lagi berdasarkan termin jika tipenya termin
        if ($this->payslipType === 'termin') {
            if (!$this->selectedTermId) {
                $this->calculatedAmount = 0; return; // Belum pilih termin
            }
            $selectedTerm = $this->paymentTerms->firstWhere('id', $this->selectedTermId);
            if (!$selectedTerm || !$selectedTerm->start_date_formatted || !$selectedTerm->end_date_formatted) {
                 $this->calculatedAmount = 0; return; // Termin tidak valid
            }

            try {
                $startDate = Carbon::parse($selectedTerm->start_date_formatted)->startOfDay();
                $endDate = Carbon::parse($selectedTerm->end_date_formatted)->endOfDay();

                $tasksToSum = $tasksToSum->filter(function ($task) use ($startDate, $endDate) {
                    if (!$task['finished_date']) return false;
                    try {
                         $finishedDate = Carbon::parse($task['finished_date']); // Sudah Y-m-d
                         return $finishedDate->betweenIncluded($startDate, $endDate);
                    } catch (\Exception $e) { return false;}
                });
            } catch (\Exception $e) {
                $this->calculatedAmount = 0; return; // Error parsing tanggal
            }
        }

        // Jumlahkan nilai task yang valid dan terpilih
        foreach ($tasksToSum as $task) {
            $total += floatval($task['calculated_value'] ?? 0);
        }
        $this->calculatedAmount = $total;

         // Update manualAmount jika tipe F/O (meskipun seharusnya tidak terjadi di sini)
         if($this->payslipType === 'full' || $this->payslipType === 'other'){
              $this->manualAmount = $this->calculatedAmount;
         }
    }

    // Method untuk mendapatkan task yang akan ditampilkan di list (sudah difilter)
    public function getAvailableTasksForWorkerProperty()
    {
        if (empty($this->selectedWorkerId) || !isset($this->unpaidTasksGrouped[$this->selectedWorkerId])) {
            return collect(); // Kosong jika worker belum dipilih
        }

        $workerTasks = collect($this->unpaidTasksGrouped[$this->selectedWorkerId]);

        if ($this->payslipType === 'termin') {
            if (!$this->selectedTermId) return collect(); // Kosong jika termin belum dipilih

            $selectedTerm = $this->paymentTerms->firstWhere('id', $this->selectedTermId);
            if (!$selectedTerm || !$selectedTerm->start_date_formatted || !$selectedTerm->end_date_formatted) return collect();

            try {
                $startDate = Carbon::parse($selectedTerm->start_date_formatted)->startOfDay();
                $endDate = Carbon::parse($selectedTerm->end_date_formatted)->endOfDay();

                return $workerTasks->filter(function ($task) use ($startDate, $endDate) {
                     if (!$task['finished_date']) return false;
                    try {
                        $finishedDate = Carbon::parse($task['finished_date']);
                        return $finishedDate->betweenIncluded($startDate, $endDate);
                    } catch (\Exception $e) { return false; }
                });
            } catch (\Exception $e) { return collect(); }

        } elseif ($this->payslipType === 'task') {
            return $workerTasks; // Tampilkan semua task worker jika tipe 'task'
        }

        return collect(); // Kosong untuk tipe 'full' atau 'other'
    }

     // Fungsi untuk sorting draft list
     public function sortByDraft($field)
     {
         if ($this->sortFieldDraft === $field) {
             $this->sortDirectionDraft = $this->sortDirectionDraft === 'asc' ? 'desc' : 'asc';
         } else {
             $this->sortDirectionDraft = 'asc';
             $this->sortFieldDraft = $field;
         }
          $this->gotoPage(1, 'draft_page'); // Reset pagination draft
     }


    public function render()
    {
        // Ambil data draft payslips dengan sorting dan pagination
        $draftPayslipsQuery = Payment::where('project_id', $this->project->id)
                                     ->where('status', Payment::STATUS_DRAFT)
                                     ->with('user:id,name'); // Eager load user

        // Apply sorting
        $allowedSortsDraft = ['created_at', 'payment_name', 'amount', 'user_name', 'payment_type'];
        if (in_array($this->sortFieldDraft, $allowedSortsDraft)) {
            if ($this->sortFieldDraft === 'user_name') {
                $draftPayslipsQuery->select('payments.*') // Hindari ambiguitas kolom ID
                      ->join('users', 'payments.user_id', '=', 'users.id')
                      ->orderBy('users.name', $this->sortDirectionDraft);
            } else {
                $draftPayslipsQuery->orderBy($this->sortFieldDraft, $this->sortDirectionDraft);
            }
        } else {
            $draftPayslipsQuery->orderBy('created_at', 'desc'); // Default
        }

        $draftPayslips = $draftPayslipsQuery->paginate(10, ['*'], 'draft_page'); // Nama page 'draft_page'

        return view('livewire.project.payslip-create-draft', [
            'draftPayslips' => $draftPayslips,
            // Data lain sudah ada di properti publik
        ]);
    }
}