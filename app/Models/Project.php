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
        'difficulty_weight', // Add this
        'priority_weight',   // Add this
    ];

    // Relasi ke User (pemilik proyek)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Relasi ke User (pekerja proyek)
    public function workers()
    {
        return $this->belongsToMany(User::class, 'project_users')
                    ->withPivot('status', 'salary', 'position');
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

 // Method to get default weights if needed
public function getDifficultyWeightAttribute($value)
{
    return $value ?? 65; // Return default if null
}

public function getPriorityWeightAttribute($value)
{
    return $value ?? 35; // Return default if null
}

}