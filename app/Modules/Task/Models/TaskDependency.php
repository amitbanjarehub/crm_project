<?php

namespace App\Modules\Task\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    protected $table = 'task_dependencies';

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'created_by',
    ];

    /*
     * Jo Task wait kar rahi hai.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(
            Task::class,
            'task_id'
        );
    }

    /*
     * Jis Task ke complete hone ka wait hai.
     */
    public function prerequisiteTask(): BelongsTo
    {
        return $this->belongsTo(
            Task::class,
            'depends_on_task_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}