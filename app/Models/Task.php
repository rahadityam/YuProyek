<?php
// app/Models/Task.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'project_id',
        'assigned_to',
        'difficulty_level',
        'priority_level',
        'start_time',
        'end_time',
        'notes',
        'order', // Added order
    ];

    // Relasi ke Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relasi ke User (pekerja yang di-assign)
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Relasi ke TaskAttachment
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }
}