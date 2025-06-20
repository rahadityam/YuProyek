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

    // Skor WSM (Weighted Sum Model)
    public function getWsmScoreAttribute(): float
    {
        $difficultyValue = $this->difficultyLevel->value ?? 0;
        $priorityValue = $this->priorityLevel->value ?? 0;
        
        $achievement = $this->achievement_percentage !== null ? $this->achievement_percentage : 100;

        $project = $this->project;
        if (!$project) {
            return 0;
        }

        $difficultyWeight = $project->difficulty_weight;
        $priorityWeight = $project->priority_weight;  

        $wDifficulty = $difficultyWeight / 100;
        $wPriority = $priorityWeight / 100;

        $rawScore = ($difficultyValue * $wDifficulty) + ($priorityValue * $wPriority);

        $finalScore = $rawScore * ($achievement / 100);

        return round($finalScore, 2);
    }

    // Nilai Dasar (dari Wage Standard yang terhubung ke User di Project ini)
    public function getBaseValueAttribute(): float
    {
        if (!$this->assigned_to || !$this->project_id) {
            return 0.0;
        }

        $projectUser = ProjectUser::where('project_id', $this->project_id)
                                  ->where('user_id', $this->assigned_to)
                                  ->with('wageStandard')
                                  ->first();

        if ($projectUser && $projectUser->wageStandard) {
            return (float) $projectUser->wageStandard->task_price;
        }

        return 0.0;
    }

    // Nilai Akhir Task (WSM * Nilai Dasar)
    public function getCalculatedValueAttribute(): float
    {
        $wsmScore = $this->wsm_score;
        $baseValue = $this->base_value;

        if ($baseValue > 0) {
            return round($wsmScore * $baseValue, 2);
        }

        return 0.0;
    }

    public function getPaymentStatusTextAttribute(): string
{
    if ($this->payment_id) {
        // Selalu coba ambil payment jika payment_id ada
        $payment = $this->relationLoaded('payment') ? $this->payment : $this->payment()->first();
        if ($payment) {
            if ($payment->status === Payment::STATUS_APPROVED) {
                return 'Paid';
            } elseif ($payment->status === Payment::STATUS_DRAFT) {
                return 'Payment Drafted';
            }
        }
    }
    return 'Unpaid';
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

    public function getCanMoveAttribute(): bool
    {
        // Jika task sudah memiliki payment_id (sudah masuk proses pembayaran/dibayar)
        // DAN payment tersebut sudah 'approved', maka tidak bisa dipindah.
        if ($this->payment_id !== null) {
            // Cek status payment terkait. Jika tidak ada relasi payment yang terload, kita load.
            $payment = $this->relationLoaded('payment') ? $this->payment : $this->payment()->first();
            if ($payment && $payment->status === Payment::STATUS_APPROVED) {
                return false; // Tidak bisa dipindah jika sudah dibayar dan disetujui
            }
        }

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