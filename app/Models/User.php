<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'gender',
        'birth_date',
        'address',
        'description',
        'profile_photo_path',
        'cv_path',
        'portfolio_path',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
}