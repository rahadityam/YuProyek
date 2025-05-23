<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log; // Untuk logging jika perlu

class PayslipHistory extends Component
{
    use WithPagination;

    public Project $project;
    public $workers; // Untuk dropdown filter
    public $paymentTypes; // Untuk dropdown filter

    // Filter & Sorting State
    public $filterSearch = '';
    public $filterUserId = '';
    public $filterPaymentType = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $sortField = 'approved_at'; // Default sort
    public $sortDirection = 'desc';

    protected $paginationTheme = 'tailwind';

    // Mapping state ke query string URL
    protected $queryString = [
        'filterSearch' => ['except' => '', 'as' => 'search'],
        'filterUserId' => ['except' => '', 'as' => 'user'],
        'filterPaymentType' => ['except' => '', 'as' => 'type'],
        'filterDateFrom' => ['except' => '', 'as' => 'from'],
        'filterDateTo' => ['except' => '', 'as' => 'to'],
        'sortField' => ['except' => 'approved_at', 'as' => 'sort'],
        'sortDirection' => ['except' => 'desc', 'as' => 'direction'],
        'page' => ['except' => 1],
    ];

    public function mount(Project $project)
    {
        // Autorisasi: Pastikan hanya owner yang bisa akses riwayat
        if ($project->owner_id !== Auth::id()) {
            // Redirect atau tampilkan pesan error, contoh redirect:
            return redirect()->route('projects.dashboard', $project)
                   ->with('error', 'Unauthorized action.');
            // atau abort(403);
        }

        $this->project = $project;
        // Ambil data statis untuk filter
        $this->workers = $project->workers()->wherePivot('status', 'accepted')->orderBy('name')->get(['id', 'name']);
        $this->paymentTypes = Payment::where('project_id', $project->id)
                               ->where('status', Payment::STATUS_APPROVED)
                               ->distinct()
                               ->pluck('payment_type');
    }

     // Reset page jika filter berubah
     public function updated($propertyName)
     {
         if (in_array($propertyName, ['filterSearch', 'filterUserId', 'filterPaymentType', 'filterDateFrom', 'filterDateTo'])) {
             $this->resetPage();
         }
     }

    // Method untuk sorting
    public function sortBy($field)
    {
        // Validasi field (opsional tapi bagus)
        $allowedSorts = ['approved_at', 'created_at', 'amount', 'payment_name', 'user_name', 'payment_type', 'approver_name', 'term_name'];
        if (!in_array($field, $allowedSorts)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
            $this->sortField = $field;
        }
        $this->resetPage(); // Kembali ke halaman 1
    }

     // Method untuk reset filter
     public function resetFilters()
     {
         $this->reset(['filterSearch', 'filterUserId', 'filterPaymentType', 'filterDateFrom', 'filterDateTo', 'sortField', 'sortDirection']);
         $this->resetPage();
     }


    public function render()
    {
        // Query utama untuk riwayat payslip
        $query = Payment::where('project_id', $this->project->id)
                        ->where('status', Payment::STATUS_APPROVED) // Hanya yang approved
                        ->with(['user:id,name', 'approver:id,name', 'paymentTerm:id,name']); // Eager load relasi (lebih spesifik)

        // Terapkan Filter
        if (!empty($this->filterSearch)) {
            $searchTerm = $this->filterSearch;
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('payment_name', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('paymentTerm', fn($tq) => $tq->where('name', 'like', "%{$searchTerm}%"));
            });
        }
        if (!empty($this->filterUserId)) {
            $query->where('user_id', $this->filterUserId);
        }
        if (!empty($this->filterPaymentType)) {
            $query->where('payment_type', $this->filterPaymentType);
        }
         if (!empty($this->filterDateFrom)) {
             try {
                 $dateFrom = \Carbon\Carbon::parse($this->filterDateFrom)->startOfDay();
                 $query->whereDate('approved_at', '>=', $dateFrom);
             } catch (\Exception $e) { Log::warning("Invalid date format for filterDateFrom: {$this->filterDateFrom}"); }
         }
         if (!empty($this->filterDateTo)) {
              try {
                 $dateTo = \Carbon\Carbon::parse($this->filterDateTo)->endOfDay();
                 $query->whereDate('approved_at', '<=', $dateTo);
              } catch (\Exception $e) { Log::warning("Invalid date format for filterDateTo: {$this->filterDateTo}"); }
         }

        // Terapkan Sorting
         $allowedSorts = ['approved_at', 'created_at', 'amount', 'payment_name', 'user_name', 'payment_type', 'approver_name', 'term_name'];
         if (in_array($this->sortField, $allowedSorts)) {
            if ($this->sortField === 'user_name') {
                 $query->select('payments.*')->join('users as worker', 'payments.user_id', '=', 'worker.id') ->orderBy('worker.name', $this->sortDirection);
            } elseif ($this->sortField === 'approver_name') {
                $query->select('payments.*')->leftJoin('users as approver', 'payments.approved_by', '=', 'approver.id') ->orderBy('approver.name', $this->sortDirection);
            } elseif ($this->sortField === 'term_name') {
                 $query->select('payments.*')->leftJoin('payment_terms as pt', 'payments.payment_term_id', '=', 'pt.id') ->orderBy('pt.name', $this->sortDirection);
            }
            else {
                 // Sorting kolom langsung di tabel payments
                 $query->orderBy($this->sortField, $this->sortDirection);
            }
         } else {
             $query->orderBy('approved_at', 'desc'); // Default sort
         }

        // Ambil data dengan pagination
        $approvedPayslips = $query->paginate(15); // Default pagination Livewire

        return view('livewire.project.payslip-history', [
            'approvedPayslips' => $approvedPayslips
            // Data filter (workers, paymentTypes) sudah ada di properti publik
        ]);
    }
}