<?php

namespace App\Livewire\Project; // Pastikan namespace benar

use Livewire\Component;
use Livewire\WithPagination; // Gunakan trait pagination Livewire
use App\Models\Project;
use App\Models\ActivityLog as ActivityLogModel; // Alias agar tidak bentrok nama kelas
use App\Models\User;
use Illuminate\Support\Facades\Log; // Untuk logging error jika perlu

class ActivityLog extends Component
{
    use WithPagination; // Aktifkan pagination Livewire

    public Project $project;
    public $users; // Untuk dropdown filter

    // Filter properties dengan default value
    public $filterUserId = '';
    public $filterAction = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    // Tentukan tema pagination jika berbeda dari default
    protected $paginationTheme = 'tailwind';

    // Agar pagination bekerja dengan query string Livewire
    // dan filter tersimpan di URL
    protected $queryString = [
        'filterUserId' => ['except' => '', 'as' => 'user'], // 'as' untuk nama parameter URL
        'filterAction' => ['except' => '', 'as' => 'action'],
        'filterDateFrom' => ['except' => '', 'as' => 'from'],
        'filterDateTo' => ['except' => '', 'as' => 'to'],
        'page' => ['except' => 1] // Nama parameter pagination default
    ];

    // Method ini dipanggil saat komponen pertama kali di-mount
    public function mount(Project $project)
    {
        $this->project = $project;
        // Ambil data user sekali saja saat mount (owner + workers)
        $workerIds = $this->project->workers()->pluck('users.id')->toArray();
        $userIds = array_unique(array_merge([$this->project->owner_id], $workerIds));
        $this->users = User::whereIn('id', $userIds)->orderBy('name')->select('id', 'name')->get();
    }

    // Method yang dipanggil saat properti filter diubah (otomatis oleh Livewire)
    // Digunakan untuk mereset pagination ke halaman 1 saat filter berubah
    public function updatingFilterUserId() { $this->resetPage(); }
    public function updatingFilterAction() { $this->resetPage(); }
    public function updatingFilterDateFrom() { $this->resetPage(); }
    public function updatingFilterDateTo() { $this->resetPage(); }

    // Method untuk mereset semua filter
    public function resetFilters()
    {
        // Reset properti ke nilai defaultnya
        $this->reset(['filterUserId', 'filterAction', 'filterDateFrom', 'filterDateTo']);
        // Reset pagination juga
        $this->resetPage();
    }

    public function render()
    {
        // Logika query dipindahkan ke sini
        $query = ActivityLogModel::where('project_id', $this->project->id)
                     ->with('user:id,name'); // Eager load user (hanya ID dan Nama)

        // Terapkan filter berdasarkan properti publik
        if (!empty($this->filterUserId)) {
            $query->where('user_id', $this->filterUserId);
        }
        if (!empty($this->filterAction)) {
            $query->where('action', $this->filterAction);
        }
        if (!empty($this->filterDateFrom)) {
            // Validasi tanggal sederhana sebelum query
            try {
                $dateFrom = \Carbon\Carbon::parse($this->filterDateFrom)->startOfDay();
                $query->where('created_at', '>=', $dateFrom);
            } catch (\Exception $e) {
                 Log::warning("Invalid date format for filterDateFrom: {$this->filterDateFrom}");
                 // Mungkin reset filter atau abaikan saja
                 // $this->filterDateFrom = ''; // Contoh: reset jika tidak valid
            }
        }
        if (!empty($this->filterDateTo)) {
             try {
                $dateTo = \Carbon\Carbon::parse($this->filterDateTo)->endOfDay();
                $query->where('created_at', '<=', $dateTo);
             } catch (\Exception $e) {
                 Log::warning("Invalid date format for filterDateTo: {$this->filterDateTo}");
                 // $this->filterDateTo = '';
             }
        }

        // Ambil data log dengan pagination
        $logs = $query->orderBy('created_at', 'desc')
                     ->paginate(20); // Jumlah item per halaman

        // Kirim logs ke view
        return view('livewire.project.activity-log', [
            'logs' => $logs,
            // users dan project sudah property publik, otomatis tersedia di view
        ]);
    }
}