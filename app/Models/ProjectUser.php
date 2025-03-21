<?php
// app/Models/ProjectUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'status',
        'salary',
        'position',
    ];
}