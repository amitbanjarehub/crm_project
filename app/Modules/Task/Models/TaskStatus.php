<?php

namespace App\Modules\Task\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class TaskStatus extends Model
{
    protected $table = 'task_statuses';

    protected $fillable = [
        'name',
        'slug',
        'system_key',
        'color',
        'is_default',
        'is_active',
        'is_closed',
        'is_manual_selectable',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'is_manual_selectable' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'status',
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

    public function scopeManual(
        Builder $query
    ): Builder {
        return $query->where(
            'is_manual_selectable',
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

    public static function manualOptions(): array
    {
        return self::query()
            ->active()
            ->manual()
            ->ordered()
            ->pluck('name', 'slug')
            ->all();
    }

    public static function defaultSlug(): string
    {
        $slug = self::query()
            ->active()
            ->manual()
            ->where('is_closed', false)
            ->where('is_default', true)
            ->value('slug');

        if ($slug) {
            return $slug;
        }

        $slug = self::query()
            ->active()
            ->manual()
            ->where('is_closed', false)
            ->ordered()
            ->value('slug');

        if (!$slug) {
            throw new RuntimeException(
                'No valid default Task status is configured.'
            );
        }

        return $slug;
    }

    public static function systemSlug(
        string $systemKey
    ): ?string {
        return self::query()
            ->where(
                'system_key',
                $systemKey
            )
            ->value('slug');
    }

    public static function requiredSystemSlug(
        string $systemKey
    ): string {
        $slug = self::systemSlug(
            $systemKey
        );

        if (!$slug) {
            throw new RuntimeException(
                "Required Task status '{$systemKey}' is not configured."
            );
        }

        return $slug;
    }

    public static function closedSlugs(): array
    {
        return self::query()
            ->where('is_closed', true)
            ->pluck('slug')
            ->all();
    }

    public static function openSlugs(): array
    {
        return self::query()
            ->where('is_closed', false)
            ->pluck('slug')
            ->all();
    }
}