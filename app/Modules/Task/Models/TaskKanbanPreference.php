<?php

namespace App\Modules\Task\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskKanbanPreference extends Model
{
    protected $table = 'task_kanban_preferences';

    protected $fillable = [
        'user_id',
        'group_by',
        'column_order',
        'hide_empty_columns',
    ];

    protected $casts = [
        'column_order' => 'array',
        'hide_empty_columns' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public static function forUser(
        User $user
    ): self {
        return static::firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'group_by' =>
                    'status',

                'column_order' =>
                    null,

                'hide_empty_columns' =>
                    false,
            ]
        );
    }
}