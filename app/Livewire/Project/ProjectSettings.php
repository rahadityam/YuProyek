<?php

namespace App\Livewire\Project; // Pastikan namespace ini benar

use Livewire\Component;
use App\Models\Project;
use App\Models\Category;
use App\Models\WageStandard;
use App\Models\PaymentTerm;
use App\Models\DifficultyLevel;
use App\Models\PriorityLevel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // Untuk flash message
use Illuminate\Support\Facades\Log;

class ProjectSettings extends Component
{
    public Project $project;

    // Data yang relatif statis (diambil sekali saat mount)
    public $allCategories;
    public $allWageStandards; // Mungkin perlu refresh jika diedit di halaman lain

    // Data yang dinamis dan dikelola Livewire
    public $selectedCategories = [];
    public $paymentTerms = [];
    public $difficultyLevels = [];
    public $priorityLevels = [];
    public $members = [];
    public $memberWageAssignments = []; // Untuk binding select standar gaji

    // State untuk UI
    public string $activeTab = 'project';

    // State untuk Modal Level (jika modal dikelola Livewire)
    // Jika modal dikelola Alpine, ini tidak perlu di sini
    // public $isLevelModalOpen = false;
    // public $levelModalType = 'difficulty';
    // public $currentLevel = ['id' => null, 'name' => '', 'value' => 1, 'color' => '#cccccc'];
    // public $levelFormErrors = [];

    // Aturan validasi Livewire (contoh untuk payment terms inline)
    protected function rules()
    {
        $rules = [];
        foreach ($this->paymentTerms as $index => $term) {
            // Hanya validasi jika tidak ditandai hapus
            if (!($term['markedForDeletion'] ?? false)) {
                 // Periksa apakah $term['id'] ada dan bukan null sebelum menambahkannya ke rule unik
                 $uniqueRuleName = 'required|string|max:255|unique:payment_terms,name';
                 if (isset($term['id']) && $term['id'] !== null) {
                    // Jika ada ID, gunakan ignore
                    $uniqueRuleName .= ',' . $term['id'];
                 }
                  // Tambahkan kondisi where project_id
                 $uniqueRuleName .= ',id,project_id,' . $this->project->id;


                $rules["paymentTerms.{$index}.name"] = $uniqueRuleName;
                $rules["paymentTerms.{$index}.start_date"] = 'required|date';
                $rules["paymentTerms.{$index}.end_date"] = 'required|date|after_or_equal:paymentTerms.'.$index.'.start_date';
            }
        }
        return $rules;
    }

    // Pesan validasi kustom
    protected $validationAttributes = [
        'paymentTerms.*.name' => 'Nama Termin',
        'paymentTerms.*.start_date' => 'Tanggal Mulai Termin',
        'paymentTerms.*.end_date' => 'Tanggal Akhir Termin',
    ];


    public function mount(Project $project)
    {
        $this->project = $project; // Load project

        // Load data awal
        $this->allCategories = Category::orderBy('name')->get();
        $this->selectedCategories = $this->project->categories->pluck('id')->map(fn($id) => (string)$id)->toArray(); // Pastikan string untuk select multiple

        $this->loadDynamicData(); // Panggil method untuk load data dinamis

        // Set tab aktif dari session jika ada
        $this->activeTab = session('active_tab', 'project');

         // Inisialisasi memberWageAssignments berdasarkan data saat ini
        foreach ($this->members as $member) {
             $this->memberWageAssignments[$member->id] = $member->pivot->wage_standard_id ?? '';
        }
    }

    // Method untuk memuat ulang data yang mungkin berubah
    public function loadDynamicData()
    {
        $this->project->refresh(); // Refresh data project utama (misal payment_calculation_type)

        $this->allWageStandards = $this->project->wageStandards()->orderBy('job_category')->get();
        $this->members = $this->project->workers()
                            ->wherePivot('status', 'accepted')
                            ->withPivot('wage_standard_id')
                            ->orderBy('name')
                            ->get();

        $this->paymentTerms = $this->project->paymentTerms()->orderBy('start_date')->get()
                                ->map(function ($term) {
                                    return [
                                        'id' => $term->id,
                                        'name' => $term->name,
                                        'start_date' => $term->start_date ? $term->start_date->format('Y-m-d') : '',
                                        'end_date' => $term->end_date ? $term->end_date->format('Y-m-d') : '',
                                        'markedForDeletion' => false, // Default flag
                                    ];
                                })->toArray(); // Ubah ke array agar Livewire bisa handle

        $this->difficultyLevels = $this->project->difficultyLevels()->orderBy('display_order', 'asc')->get()->toArray();
        $this->priorityLevels = $this->project->priorityLevels()->orderBy('display_order', 'asc')->get()->toArray();

         // Re-inisialisasi assignment jika ada member baru/dihapus
        $currentAssignments = $this->memberWageAssignments;
        $this->memberWageAssignments = [];
        foreach ($this->members as $member) {
             $this->memberWageAssignments[$member->id] = $currentAssignments[$member->id] ?? ($member->pivot->wage_standard_id ?? '');
         }
    }

    // Fungsi untuk mengganti tab aktif
    public function switchTab($tabName)
    {
        $this->activeTab = $tabName;
    }

     // --- Payment Terms Methods ---
     public function addTerm()
     {
         // Logika default date sama seperti di Alpine sebelumnya
         $defaultStartDate = $this->project->start_date ? $this->project->start_date->format('Y-m-d') : date('Y-m-d');
         $defaultEndDate = date('Y-m-d', strtotime($defaultStartDate . ' + 7 days'));
         if (count($this->paymentTerms) > 0) {
             $lastTermEndDate = end($this->paymentTerms)['end_date'];
             if ($lastTermEndDate) {
                 try {
                     $nextDay = \Carbon\Carbon::parse($lastTermEndDate)->addDay();
                     $defaultStartDate = $nextDay->format('Y-m-d');
                     $defaultEndDate = $nextDay->addDays(7)->format('Y-m-d');
                 } catch (\Exception $e) { /* Biarkan default awal */ }
             }
         }

         $this->paymentTerms[] = [
             'id' => null, // ID null untuk termin baru
             'name' => 'Termin Baru ' . (count($this->paymentTerms) + 1),
             'start_date' => $defaultStartDate,
             'end_date' => $defaultEndDate,
             'markedForDeletion' => false
         ];
     }

     public function removeTerm($index)
     {
          // Cek apakah index valid
         if (!isset($this->paymentTerms[$index])) return;

         if ($this->paymentTerms[$index]['id']) {
             // Jika sudah ada di DB, tandai untuk dihapus
             $this->paymentTerms[$index]['markedForDeletion'] = true;
             // Tambahkan pesan flash sementara (opsional)
              $this->dispatchBrowserEvent('show-flash', ['message' => "Termin '{$this->paymentTerms[$index]['name']}' akan dihapus saat disimpan.", 'success' => true]);
         } else {
             // Jika belum ada di DB (baru ditambahkan), hapus dari array
             unset($this->paymentTerms[$index]);
             // Re-index array agar tidak ada gap (penting untuk validasi Livewire)
              $this->paymentTerms = array_values($this->paymentTerms);
         }
     }


     // Method untuk trigger refresh data (bisa dipanggil dari event lain)
     public function refreshData()
     {
         $this->loadDynamicData();
     }


    public function render()
    {
        // Load ulang data dinamis setiap render untuk memastikan kekinian
        // $this->loadDynamicData(); // Atau panggil hanya jika perlu

        // Di render() kita tidak perlu query lagi jika data sudah di properti publik
        // dan sudah diload di mount() atau refreshData()

        return view('livewire.project.project-settings');
            // ->layout('layouts.app'); // Biasanya tidak perlu jika route sudah diatur
    }
}