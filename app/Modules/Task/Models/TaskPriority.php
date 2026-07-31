<?php

namespace App\Modules\Task\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class TaskPriority extends Model
{
    protected $table = 'task_priorities';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_default',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'priority',
            'slug'
        );
    }

    public function scopeOrdered(
        Builder $query
    ): Builder {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }

    public static function options(
        bool $activeOnly = false
    ): array {
        $query = self::query();

        if ($activeOnly) {
            $query->active();
        }

        return $query
            ->ordered()
            ->pluck('name', 'slug')
            ->all();
    }

    public static function defaultSlug(): string
    {
        $slug = self::query()
            ->active()
            ->where('is_default', true)
            ->value('slug');

        if ($slug) {
            return $slug;
        }

        $slug = self::query()
            ->active()
            ->ordered()
            ->value('slug');

        if (!$slug) {
            throw new RuntimeException(
                'No active Task priority is configured.'
            );
        }

        return $slug;
    }
}