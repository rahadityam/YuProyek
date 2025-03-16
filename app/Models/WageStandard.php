<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WageStandard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'job_category',
        'task_price',
    ];

    /**
     * Get the project that owns the wage standard.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}