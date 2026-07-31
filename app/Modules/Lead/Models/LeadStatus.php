<?php

namespace App\Modules\Lead\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class LeadStatus extends Model
{
    protected $table = 'lead_statuses';

    protected $fillable = [
        'name',
        'slug',
        'system_key',
        'color',
        'is_default',
        'is_active',
        'is_closed',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(
            Lead::class,
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

    public static function options(
        bool $activeOnly = false
    ): array {
        $query = self::query();

        if ($activeOnly) {
            $query->active();
        }

        return $query
            ->ordered()
            ->pluck(
                'name',
                'slug'
            )
            ->all();
    }

    public static function editableOptions(): array
    {
        return self::query()
            ->active()
            ->where(
                function (Builder $query) {
                    $query
                        ->whereNull(
                            'system_key'
                        )
                        ->orWhere(
                            'system_key',
                            '!=',
                            'converted'
                        );
                }
            )
            ->ordered()
            ->pluck(
                'name',
                'slug'
            )
            ->all();
    }

    public static function defaultSlug(): string
    {
        $slug = self::query()
            ->active()
            ->where(
                'is_default',
                true
            )
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
                'No active Lead status configured.'
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

    public static function closedSlugs(): array
    {
        return self::query()
            ->where(
                'is_closed',
                true
            )
            ->pluck('slug')
            ->all();
    }

    public static function openSlugs(): array
    {
        return self::query()
            ->where(
                'is_closed',
                false
            )
            ->pluck('slug')
            ->all();
    }
}