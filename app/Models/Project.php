<?php
// app/Models/Project.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'budget',
        'status',
        'owner_id',
        'wip_limits',
        'difficulty_weight',
        'priority_weight',
        'payment_calculation_type', // Pastikan ini ada
    ];
    const STATUS_ACTIVE = 'active';
    const STATUS_BLOCKED = 'blocked';
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date', // <-- Tambahkan ini
        'end_date' => 'date',   // <-- Tambahkan ini
        'budget' => 'decimal:2', // Casting budget jika perlu
        'wip_limits' => 'integer',
        'difficulty_weight' => 'integer',
        'priority_weight' => 'integer',
        // Tambahkan cast lain jika ada (misal: created_at, updated_at sudah otomatis)
    ];

    // Relasi ke User (pemilik proyek)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Relasi ke User (pekerja proyek)
    public function workers()
    {
        // Sertakan pivot wage_standard_id yang baru
        return $this->belongsToMany(User::class, 'project_users')
                    ->withPivot('status', 'salary', 'position', 'wage_standard_id'); // Tambah wage_standard_id
    }


    // Relasi ke Task
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Relasi ke Category (many-to-many)
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'project_categories');
    }

    public function wageStandards()
    {
        return $this->hasMany(WageStandard::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->orderBy('created_at', 'desc');
    }
    public function difficultyLevels()
    {
        return $this->hasMany(DifficultyLevel::class);
    }

    public function priorityLevels()
    {
        return $this->hasMany(PriorityLevel::class);
    }

    // Add relationship to payments made within this project
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // --- BARU: Relasi ke Payment Terms ---
    /**
     * Get the payment terms defined for this project.
     */
    public function paymentTerms()
    {
        return $this->hasMany(PaymentTerm::class)->orderBy('start_date'); // Urutkan berdasarkan tanggal mulai
    }
    // --- END BARU ---

     // Method to get default weights if needed
    public function getDifficultyWeightAttribute($value)
    {
        // Default to 65 if null or 0, but allow 0 if explicitly set
        return ($value === null || $value === 0) ? ($this->attributes['difficulty_weight'] ?? 65) : $value;
    }

    public function getPriorityWeightAttribute($value)
    {
         // Default to 35 if null or 0, but allow 0 if explicitly set
        return ($value === null || $value === 0) ? ($this->attributes['priority_weight'] ?? 35) : $value;
    }

}