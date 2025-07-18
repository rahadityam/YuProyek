<?php
// app/Models/ProjectUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // Gunakan Pivot jika ini tabel pivot

class ProjectUser extends Pivot // Atau extends Model jika bukan pivot default Laravel
{
    use HasFactory;
    // Jika bukan pivot default, tambahkan:
    protected $table = 'project_users';
    // public $incrementing = true; // Atau false jika ID bukan auto-increment

    protected $fillable = [
        'project_id',
        'user_id',
        'status',
        'salary', // Mungkin tidak relevan jika pakai WageStandard per task
        'position',
        'wage_standard_id', // Foreign key ke wage_standards
    ];

    // Relasi ke WageStandard yang digunakan user ini di project ini
    public function wageStandard()
    {
        return $this->belongsTo(WageStandard::class, 'wage_standard_id');
    }

    // Relasi balik ke User dan Project (jika diperlukan)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}