<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bank_account',
        'id_number',
        'phone_number',
        'gender',
        'birth_date',
        'address',
        'description',
        'profile_photo_path',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    // Relasi ke Education
    public function educations()
    {
        return $this->hasMany(UserEducation::class);
    }

    // Relasi ke Documents
    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    // Relasi ke Project (sebagai pemilik proyek)
    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    // Relasi ke Project (sebagai pekerja proyek)
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_users')
                    ->withPivot('status', 'salary', 'position');
    }

    // Relasi ke Task (sebagai pekerja yang di-assign)
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
    
    // Helper untuk mendapatkan CV
    public function getCv()
    {
        return $this->documents()->where('type', 'cv')->latest()->first();
    }
    
    // Helper untuk mendapatkan Portfolio
    public function getPortfolio()
    {
        return $this->documents()->where('type', 'portfolio')->latest()->first();
    }
    
    // Helper untuk mendapatkan Certificates
    public function getCertificates()
    {
        return $this->documents()->where('type', 'certificate')->get();
    }

     /**
         * Komentar yang dibuat oleh user ini.
         */
        public function taskComments()
        {
            return $this->hasMany(TaskComment::class);
        }

        /**
         * Attachment yang diupload oleh user ini.
         */
        public function taskAttachments()
        {
            return $this->hasMany(TaskAttachment::class);
        }

        // app/Models/User.php
public function isProjectOwner(Project $project = null): bool
{
    if ($project) {
        return $this->id === $project->owner_id;
    }
    return false; // Jika hanya cek peran umum
}

public function isWorker(): bool
{
    return $this->role === 'worker';
}

public function isProjectMember(Project $project): bool
{
    // Cek apakah user adalah owner ATAU worker yang diterima di proyek tersebut
    if ($this->id === $project->owner_id) {
        return true;
    }
    return $project->workers()->where('user_id', $this->id)->wherePivot('status', 'accepted')->exists();
}
}