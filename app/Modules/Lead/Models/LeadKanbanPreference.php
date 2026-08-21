<?php

namespace App\Modules\Lead\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadKanbanPreference extends Model
{
    protected $table =
        'lead_kanban_preferences';

    protected $fillable = [
        'user_id',
        'group_by',
        'column_order',
        'collapsed_columns',
        'hide_empty_columns',
        'selected_filters',
    ];

    protected $casts = [
        'column_order' => 'array',
        'collapsed_columns' => 'array',
        'hide_empty_columns' => 'boolean',
        'selected_filters' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public static function forUser(
        User $user
    ): self {
        return self::firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'group_by' => 'status',
                'column_order' => [
                    'status' => [],
                    'priority' => [],
                ],
                'collapsed_columns' => [],
                'hide_empty_columns' => false,
                'selected_filters' => [],
            ]
        );
    }
}