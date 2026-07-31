<?php

namespace App\Modules\Project\Support;

use App\Modules\Project\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectActivityLogger
{
    public static function log(
        Project $project,
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $project->activities()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject
                ? get_class($subject)
                : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}