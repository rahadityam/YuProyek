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
}