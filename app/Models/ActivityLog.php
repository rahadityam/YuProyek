<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'loggable_type',
        'loggable_id',
        'action',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user who performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project associated with the activity.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the model that was acted upon.
     */
    public function loggable()
    {
        return $this->morphTo();
    }
}