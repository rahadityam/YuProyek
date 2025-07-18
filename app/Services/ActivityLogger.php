<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an activity.
     *
     * FIX: The optional $model parameter is moved to the end to comply with PHP 8 standards.
     *
     * @param string $action The action performed (created, updated, etc.)
     * @param int $projectId The project ID
     * @param string $description The description of the activity
     * @param Model|null $model The model being acted upon (optional)
     * @param array $properties Additional properties to store (optional)
     * @return ActivityLog
     */
    public static function log(
        string $action,
        int $projectId,
        string $description,
        ?Model $model = null,
        array $properties = []
    ): ActivityLog {
        $user = Auth::user();

        $data = [
            'user_id' => $user->id,
            'project_id' => $projectId,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ];

        // If a model is provided, add the polymorphic relationship
        if ($model) {
            $data['loggable_type'] = get_class($model);
            $data['loggable_id'] = $model->id;
        }

        return ActivityLog::create($data);
    }
}