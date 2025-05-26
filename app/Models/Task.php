<?php
// app/Models/Task.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'project_id',
        'assigned_to',
        'start_time',
        'end_time',
        'notes',
        'order',
        'difficulty_level_id',
        'priority_level_id',
        'achievement_percentage',
        'payment_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'achievement_percentage' => 'integer',
    ];

    // --- Relasi ---
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function difficultyLevel()
    {
        return $this->belongsTo(DifficultyLevel::class);
    }

    public function priorityLevel()
    {
        return $this->belongsTo(PriorityLevel::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // Relasi untuk mendapatkan pivot ProjectUser (untuk wage standard)
    // Mungkin perlu jika tidak ingin query manual di accessor
    public function projectUserMembership() {
        // Cek apakah assigned_to dan project_id ada sebelum membuat relasi
        if ($this->assigned_to && $this->project_id) {
            return $this->hasOne(ProjectUser::class, 'user_id', 'assigned_to')
                        ->where('project_id', $this->project_id);
        }
        // Return null relation if keys are missing to avoid errors
        // Note: This approach might be less efficient than direct query in accessor for single task calculation
        // but could be useful if eager loading `projectUserMembership.wageStandard`
         return $this->hasOne(ProjectUser::class, 'user_id', 'assigned_to')->where('project_id', -1); // Dummy condition
    }


    // --- Accessor ---

    // Skor WSM (Weighted Sum Model)
    // Skor WSM (Weighted Sum Model)
public function getWsmScoreAttribute(): float
{
    $difficultyValue = $this->difficultyLevel->value ?? 0;
    $priorityValue = $this->priorityLevel->value ?? 0;
    
    // Get achievement percentage - only default to 100 if NULL, not if 0
    $achievement = $this->achievement_percentage !== null ? $this->achievement_percentage : 100;

    $project = $this->project; // Load relasi project
    if (!$project) {
        return 0; // Handle jika task tidak punya project
    }

    $difficultyWeight = $project->difficulty_weight; // Accessor di Project akan handle default
    $priorityWeight = $project->priority_weight;     // Accessor di Project akan handle default

    // Normalisasi bobot (asumsi 0-100)
    $wDifficulty = $difficultyWeight / 100;
    $wPriority = $priorityWeight / 100;

    // Hitung skor mentah berdasarkan level
    $rawScore = ($difficultyValue * $wDifficulty) + ($priorityValue * $wPriority);

    // Terapkan persentase pencapaian
    $finalScore = $rawScore * ($achievement / 100);

    return round($finalScore, 2); // Bulatkan 2 desimal
}

    // Nilai Dasar (dari Wage Standard yang terhubung ke User di Project ini)
    public function getBaseValueAttribute(): float
    {
        if (!$this->assigned_to || !$this->project_id) {
            return 0.0;
        }

        // Cari data pivot project_users untuk user & project ini
        $projectUser = ProjectUser::where('project_id', $this->project_id)
                                  ->where('user_id', $this->assigned_to)
                                  ->with('wageStandard') // Eager load wage standard
                                  ->first();

        // Jika ada pivot dan wage standard terhubung
        if ($projectUser && $projectUser->wageStandard) {
            return (float) $projectUser->wageStandard->task_price;
        }

        return 0.0; // Fallback
    }

    // Nilai Akhir Task (WSM * Nilai Dasar)
    public function getCalculatedValueAttribute(): float
    {
        // Panggil accessor wsm_score dan base_value
        $wsmScore = $this->wsm_score;
        $baseValue = $this->base_value;

        // Pastikan baseValue valid sebelum mengalikan
        if ($baseValue > 0) {
             // Logika perhitungan: WSM Score * Harga Dasar Task dari Wage Standard
            return round($wsmScore * $baseValue, 2);
        }

        return 0.0; // Jika nilai dasar 0, hasil akhir juga 0
    }

    // Status Pembayaran (Teks)
    public function getPaymentStatusTextAttribute(): string
    {
        return $this->payment_id !== null ? 'Sudah Dibayar' : 'Belum Dibayar';
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'desc'); // Default sort newest
    }

    /**
     * Relasi ke Task Attachments.
     */
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class)->orderBy('created_at', 'desc');
    }

    /**
         * Relasi ke Activity Logs (menggunakan morphMany dari ActivityLog Model).
         * Atau filter dari ActivityLog global.
         */
        // public function activityLogs()
        // {
        //      // Asumsi ActivityLog punya relasi morphTo 'loggable'
        //      // return $this->morphMany(ActivityLog::class, 'loggable')->orderBy('created_at', 'desc');

        //      // Alternatif jika ActivityLog tidak morphMany, filter manual:
        //      return $this->hasMany(ActivityLog::class, 'loggable_id')
        //                 ->where('loggable_type', self::class) // Filter berdasarkan tipe model Task
        //                 ->orderBy('created_at', 'desc');
        // }

         // Relasi untuk Activity Log (History)
         public function activityLogs()
         {
             // Pastikan model ActivityLog Anda memiliki relasi morphTo 'loggable'
             return $this->morphMany(ActivityLog::class, 'loggable')->orderBy('created_at', 'desc');
         }

        // public function getAttachmentCountAttribute(): int
        //  {
        //      // Jika relasi belum diload, load count
        //      if (!$this->relationLoaded('attachments')) {
        //          return $this->attachments()->count();
        //      }
        //      return $this->attachments->count();
        //  }
        // Tambahkan accessor untuk attachment count agar mudah diakses di view
        public function getAttachmentsCountAttribute()
        {
            // Jika relasi sudah di-load (withCount), gunakan itu. Jika tidak, hitung manual.
            if (array_key_exists('attachments_count', $this->attributes)) {
                return $this->attributes['attachments_count'];
            }
            return $this->attachments()->count();
        }

        /**
     * Accessor untuk menentukan apakah user saat ini bisa memindahkan (update status) task ini.
     * Akan otomatis ditambahkan jika model di-serialize ke array/JSON
     * dengan nama atribut 'can_move'.
     */
    public function getCanMoveAttribute(): bool
    {
        // Pastikan ada user yang login
        if (Auth::check()) {
            // Menggunakan policy 'updateStatus' yang sudah kita definisikan
            return Auth::user()->can('updateStatus', $this);
        }
        return false; // Jika tidak ada user login, defaultnya tidak bisa
    }

    protected $appends = [
        'wsm_score',
        'base_value',
        'calculated_value',
        'payment_status_text',
        // 'attachments_count', // Jika Anda menggunakan withCount, ini tidak perlu di appends
        'can_move' // Tambahkan ini!
    ];
}